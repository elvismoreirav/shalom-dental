<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Invoice Generator Service
 * =========================================================================
 * Service for generating invoices from clinical procedures
 */

namespace App\Modules\ClinicalCare\Services;

use App\Core\Database;

class InvoiceGeneratorService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate invoice from appointment procedures
     *
     * @param int $appointmentId
     * @param array $procedureIds - IDs of procedures to invoice (empty = all pending)
     * @param int $emissionPointId
     * @param int $userId
     * @return array ['success' => bool, 'invoice_id' => int|null, 'message' => string]
     */
    public function generateFromAppointment(int $appointmentId, array $procedureIds, int $emissionPointId, int $userId): array
    {
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);

        // Get appointment with patient data
        $appointment = $this->db->selectOne("
            SELECT a.*, p.id_type, p.id_number, p.first_name, p.last_name,
                   p.email, p.phone, p.address
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = ? AND a.organization_id = ?
        ", [$appointmentId, $organizationId]);

        if (!$appointment) {
            return ['success' => false, 'message' => 'Cita no encontrada', 'invoice_id' => null];
        }

        // Get procedures to invoice
        $whereIds = '';
        $params = [$appointmentId];

        if (!empty($procedureIds)) {
            $placeholders = implode(',', array_fill(0, count($procedureIds), '?'));
            $whereIds = " AND ap.id IN ({$placeholders})";
            $params = array_merge($params, $procedureIds);
        }

        $procedures = $this->db->select("
            SELECT ap.*, at.code as service_code, at.name as service_name
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            WHERE ap.appointment_id = ? AND ap.is_invoiced = 0 {$whereIds}
            ORDER BY ap.created_at
        ", $params);

        if (empty($procedures)) {
            return ['success' => false, 'message' => 'No hay procedimientos pendientes de facturar', 'invoice_id' => null];
        }

        // Get emission point and location data
        $emissionPoint = $this->db->selectOne("
            SELECT ep.*, l.sri_establishment_code
            FROM emission_points ep
            JOIN locations l ON ep.location_id = l.id
            WHERE ep.id = ? AND l.id = ?
        ", [$emissionPointId, $locationId]);

        if (!$emissionPoint) {
            return ['success' => false, 'message' => 'Punto de emisión no válido', 'invoice_id' => null];
        }

        // Get next sequential
        $sequential = $this->getNextSequential($emissionPointId, '01');

        // Calculate totals
        $totals = $this->calculateTotals($procedures);

        // Map patient ID type to SRI code
        $buyerIdType = $this->mapIdTypeToSri($appointment['id_type']);

        // Begin transaction
        try {
            // Create invoice
            $invoiceData = [
                'organization_id' => $organizationId,
                'location_id' => $locationId,
                'emission_point_id' => $emissionPointId,
                'patient_id' => $appointment['patient_id'],
                'appointment_id' => $appointmentId,
                'document_type' => '01',
                'establishment_code' => $emissionPoint['sri_establishment_code'],
                'emission_point_code' => $emissionPoint['code'],
                'sequential' => $sequential,
                'issue_date' => date('Y-m-d'),
                'issue_time' => date('H:i:s'),
                'buyer_id_type' => $buyerIdType,
                'buyer_id_number' => $appointment['id_number'],
                'buyer_name' => $appointment['first_name'] . ' ' . $appointment['last_name'],
                'buyer_address' => $appointment['address'],
                'buyer_email' => $appointment['email'],
                'buyer_phone' => $appointment['phone'],
                'subtotal_no_tax' => $totals['subtotal_0'],
                'subtotal_0' => $totals['subtotal_0'],
                'subtotal_12' => $totals['subtotal_12'],
                'subtotal_15' => $totals['subtotal_15'],
                'subtotal_not_subject' => 0,
                'subtotal_exempt' => 0,
                'total_discount' => $totals['discount'],
                'subtotal' => $totals['subtotal'],
                'total_tax' => $totals['tax'],
                'tip' => 0,
                'total' => $totals['total'],
                'status' => 'draft',
                'created_by_user_id' => $userId,
            ];

            $invoiceId = $this->db->insert('invoices', $invoiceData);

            // Create invoice items
            $sequence = 0;
            foreach ($procedures as $proc) {
                $sequence++;

                $itemData = [
                    'invoice_id' => $invoiceId,
                    'sequence' => $sequence,
                    'main_code' => $proc['service_code'],
                    'aux_code' => null,
                    'description' => $this->buildItemDescription($proc),
                    'quantity' => $proc['quantity'],
                    'unit_price' => $proc['unit_price'],
                    'discount_amount' => $proc['discount_amount'],
                    'subtotal' => $proc['subtotal'],
                    'tax_code' => $proc['tax_code'],
                    'tax_percentage' => $proc['tax_percentage'],
                    'tax_rate_code' => $this->mapTaxRateCode($proc['tax_percentage']),
                    'tax_amount' => $proc['tax_amount'],
                    'total' => $proc['total'],
                    'appointment_type_id' => $proc['appointment_type_id'],
                ];

                $itemId = $this->db->insert('invoice_items', $itemData);

                // Mark procedure as invoiced
                $this->db->update('appointment_procedures', [
                    'is_invoiced' => 1,
                    'invoice_id' => $invoiceId,
                    'invoice_item_id' => $itemId,
                ], 'id = ?', [$proc['id']]);

                // Update treatment plan item if linked
                if ($proc['treatment_plan_item_id']) {
                    $this->db->update('treatment_plan_items', [
                        'is_invoiced' => 1,
                        'invoice_item_id' => $itemId,
                    ], 'id = ?', [$proc['treatment_plan_item_id']]);
                }
            }

            return [
                'success' => true,
                'message' => 'Factura generada correctamente',
                'invoice_id' => $invoiceId,
                'invoice_number' => $emissionPoint['sri_establishment_code'] . '-' .
                                   $emissionPoint['code'] . '-' .
                                   str_pad($sequential, 9, '0', STR_PAD_LEFT)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar factura: ' . $e->getMessage(),
                'invoice_id' => null
            ];
        }
    }

    /**
     * Preview invoice without saving
     */
    public function previewFromAppointment(int $appointmentId, array $procedureIds = []): array
    {
        $organizationId = (int) session('organization_id', 0);

        // Get appointment with patient data
        $appointment = $this->db->selectOne("
            SELECT a.*, p.id_type, p.id_number, p.first_name, p.last_name,
                   p.email, p.phone, p.address
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = ? AND a.organization_id = ?
        ", [$appointmentId, $organizationId]);

        if (!$appointment) {
            return ['success' => false, 'message' => 'Cita no encontrada'];
        }

        // Get procedures
        $whereIds = '';
        $params = [$appointmentId];

        if (!empty($procedureIds)) {
            $placeholders = implode(',', array_fill(0, count($procedureIds), '?'));
            $whereIds = " AND ap.id IN ({$placeholders})";
            $params = array_merge($params, $procedureIds);
        }

        $procedures = $this->db->select("
            SELECT ap.*, at.code as service_code, at.name as service_name
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            WHERE ap.appointment_id = ? AND ap.is_invoiced = 0 {$whereIds}
            ORDER BY ap.created_at
        ", $params);

        if (empty($procedures)) {
            return ['success' => false, 'message' => 'No hay procedimientos pendientes'];
        }

        $totals = $this->calculateTotals($procedures);

        $items = [];
        foreach ($procedures as $proc) {
            $items[] = [
                'id' => $proc['id'],
                'code' => $proc['service_code'],
                'description' => $this->buildItemDescription($proc),
                'quantity' => $proc['quantity'],
                'unit_price' => $proc['unit_price'],
                'discount' => $proc['discount_amount'],
                'subtotal' => $proc['subtotal'],
                'tax_percentage' => $proc['tax_percentage'],
                'tax_amount' => $proc['tax_amount'],
                'total' => $proc['total'],
            ];
        }

        return [
            'success' => true,
            'data' => [
                'buyer' => [
                    'id_type' => $appointment['id_type'],
                    'id_number' => $appointment['id_number'],
                    'name' => $appointment['first_name'] . ' ' . $appointment['last_name'],
                    'email' => $appointment['email'],
                    'phone' => $appointment['phone'],
                    'address' => $appointment['address'],
                ],
                'items' => $items,
                'totals' => $totals,
            ]
        ];
    }

    /**
     * Calculate totals from procedures
     */
    private function calculateTotals(array $procedures): array
    {
        $subtotal_0 = 0;
        $subtotal_12 = 0;
        $subtotal_15 = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($procedures as $proc) {
            $totalDiscount += (float) $proc['discount_amount'];
            $totalTax += (float) $proc['tax_amount'];

            $taxPct = (float) $proc['tax_percentage'];
            if ($taxPct == 0) {
                $subtotal_0 += (float) $proc['subtotal'];
            } elseif ($taxPct == 12) {
                $subtotal_12 += (float) $proc['subtotal'];
            } elseif ($taxPct == 15) {
                $subtotal_15 += (float) $proc['subtotal'];
            } else {
                $subtotal_15 += (float) $proc['subtotal']; // Default to 15%
            }
        }

        $subtotal = $subtotal_0 + $subtotal_12 + $subtotal_15;
        $total = $subtotal + $totalTax;

        return [
            'subtotal_0' => $subtotal_0,
            'subtotal_12' => $subtotal_12,
            'subtotal_15' => $subtotal_15,
            'discount' => $totalDiscount,
            'subtotal' => $subtotal,
            'tax' => $totalTax,
            'total' => $total,
        ];
    }

    /**
     * Get next sequential number
     */
    private function getNextSequential(int $emissionPointId, string $documentType): int
    {
        $current = $this->db->selectOne(
            "SELECT current_sequential FROM invoice_sequentials WHERE emission_point_id = ? AND document_type = ? FOR UPDATE",
            [$emissionPointId, $documentType]
        );

        if ($current) {
            $next = (int) $current['current_sequential'] + 1;
            $this->db->update('invoice_sequentials', [
                'current_sequential' => $next,
                'last_used_at' => date('Y-m-d H:i:s')
            ], 'emission_point_id = ? AND document_type = ?', [$emissionPointId, $documentType]);
        } else {
            $next = 1;
            $this->db->insert('invoice_sequentials', [
                'emission_point_id' => $emissionPointId,
                'document_type' => $documentType,
                'current_sequential' => $next,
                'last_used_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $next;
    }

    /**
     * Map patient ID type to SRI code
     */
    private function mapIdTypeToSri(string $idType): string
    {
        return match ($idType) {
            'cedula' => '05',
            'ruc' => '04',
            'pasaporte' => '06',
            default => '07', // Consumidor final
        };
    }

    /**
     * Map tax percentage to SRI rate code
     */
    private function mapTaxRateCode(float $percentage): string
    {
        return match (true) {
            $percentage == 0 => '0',
            $percentage == 12 => '2',
            $percentage == 15 => '4',
            default => '4',
        };
    }

    /**
     * Build item description including tooth info
     */
    private function buildItemDescription(array $procedure): string
    {
        $desc = $procedure['service_name'];

        if (!empty($procedure['tooth_number'])) {
            $desc .= ' - Pieza ' . $procedure['tooth_number'];
        }

        if (!empty($procedure['surfaces'])) {
            $desc .= ' (' . $procedure['surfaces'] . ')';
        }

        if (!empty($procedure['description'])) {
            $desc .= '. ' . $procedure['description'];
        }

        return $desc;
    }
}
