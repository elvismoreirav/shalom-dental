<?php

namespace App\Modules\Clinical\Services;

use App\Core\Database;
use Exception;

class InvoiceGeneratorService
{
    public function __construct(private Database $db)
    {
    }

    public function generateFromAppointment(int $appointmentId, int $organizationId, int $locationId, int $userId): int
    {
        $appointment = $this->db->selectOne(
            'SELECT a.*, p.first_name, p.last_name, p.id_number, p.id_type, p.email, p.phone, p.address
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             WHERE a.id = ?',
            [$appointmentId]
        );
        if (!$appointment) {
            throw new Exception('Cita no encontrada');
        }

        $emissionPoint = $this->db->selectOne(
            'SELECT ep.id, ep.code, l.sri_establishment_code
             FROM emission_points ep
             JOIN locations l ON l.id = ep.location_id
             WHERE ep.location_id = ?
             ORDER BY ep.code ASC LIMIT 1',
            [$locationId]
        );
        if (!$emissionPoint) {
            throw new Exception('No hay punto de emisión configurado');
        }

        $seqRow = $this->db->selectOne(
            "SELECT current_sequential FROM invoice_sequentials WHERE emission_point_id = ? AND document_type = '01'",
            [$emissionPoint['id']]
        );
        $sequential = (int) (($seqRow['current_sequential'] ?? 0) + 1);

        $procedures = $this->db->select(
            'SELECT * FROM appointment_procedures WHERE appointment_id = ? AND is_invoiced = FALSE',
            [$appointmentId]
        );
        if (empty($procedures)) {
            throw new Exception('No hay procedimientos pendientes de facturar');
        }

        [$totals, $items] = $this->calculateTotalsFromProcedures($procedures);

        $invoiceId = $this->db->insert('invoices', [
            'organization_id' => $organizationId,
            'location_id' => $locationId,
            'emission_point_id' => (int) $emissionPoint['id'],
            'patient_id' => (int) $appointment['patient_id'],
            'appointment_id' => $appointmentId,
            'document_type' => '01',
            'establishment_code' => $emissionPoint['sri_establishment_code'],
            'emission_point_code' => $emissionPoint['code'],
            'sequential' => $sequential,
            'issue_date' => date('Y-m-d'),
            'issue_time' => date('H:i:s'),
            'buyer_id_type' => $appointment['id_type'] ?? '05',
            'buyer_id_number' => $appointment['id_number'] ?? '',
            'buyer_name' => trim(($appointment['last_name'] ?? '') . ' ' . ($appointment['first_name'] ?? '')),
            'buyer_address' => $appointment['address'] ?? null,
            'buyer_email' => $appointment['email'] ?? null,
            'buyer_phone' => $appointment['phone'] ?? null,
            'subtotal_no_tax' => $totals['subtotal_no_tax'],
            'subtotal_0' => $totals['subtotal_0'],
            'subtotal_12' => $totals['subtotal_12'],
            'subtotal_15' => $totals['subtotal_15'],
            'total_discount' => $totals['total_discount'],
            'subtotal' => $totals['subtotal_no_tax'],
            'total_tax' => $totals['total_tax'],
            'total_ice' => $totals['total_ice'],
            'tip' => $totals['tip'],
            'total' => $totals['total'],
            'status' => 'draft',
            'created_by_user_id' => $userId ?: 1,
        ]);

        $this->db->update('invoice_sequentials', [
            'current_sequential' => $sequential,
            'last_used_at' => date('Y-m-d H:i:s'),
        ], 'emission_point_id = ? AND document_type = ?', [$emissionPoint['id'], '01']);

        $sequence = 1;
        foreach ($items as $item) {
            $invoiceItemId = $this->db->insert('invoice_items', [
                'invoice_id' => $invoiceId,
                'sequence' => $sequence++,
                'main_code' => $item['main_code'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'subtotal' => $item['subtotal'],
                'tax_code' => '2',
                'tax_percentage' => $item['tax_percentage'],
                'tax_rate_code' => $item['tax_rate_code'],
                'tax_amount' => $item['tax_amount'],
                'total' => $item['total'],
                'appointment_type_id' => $item['appointment_type_id'],
            ]);

            $this->db->update('appointment_procedures', [
                'is_invoiced' => 1,
                'invoice_id' => $invoiceId,
                'invoice_item_id' => $invoiceItemId,
            ], 'id = ?', [$item['procedure_id']]);
        }

        return $invoiceId;
    }

    private function calculateTotalsFromProcedures(array $procedures): array
    {
        $subtotalNoTax = 0.0;
        $subtotal0 = 0.0;
        $subtotal12 = 0.0;
        $subtotal15 = 0.0;
        $totalDiscount = 0.0;
        $totalTax = 0.0;
        $items = [];

        foreach ($procedures as $procedure) {
            $subtotal = (float) $procedure['subtotal'];
            $discount = (float) ($procedure['discount_amount'] ?? 0);
            $taxPct = (float) ($procedure['tax_percentage'] ?? 15);
            $taxAmount = (float) $procedure['tax_amount'];
            $total = (float) $procedure['total'];

            $subtotalNoTax += $subtotal;
            $totalDiscount += $discount;
            $totalTax += $taxAmount;
            if ($taxPct === 0.0) $subtotal0 += $subtotal;
            if ($taxPct === 12.0) $subtotal12 += $subtotal;
            if ($taxPct === 15.0) $subtotal15 += $subtotal;

            $items[] = [
                'procedure_id' => (int) $procedure['id'],
                'appointment_type_id' => (int) $procedure['appointment_type_id'],
                'main_code' => 'SERV',
                'description' => $procedure['description'] ?? 'Procedimiento',
                'quantity' => (float) ($procedure['quantity'] ?? 1),
                'unit_price' => (float) $procedure['unit_price'],
                'discount_amount' => $discount,
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPct,
                'tax_rate_code' => $taxPct === 0.0 ? '0' : ($taxPct === 12.0 ? '2' : '4'),
                'tax_amount' => $taxAmount,
                'total' => $total,
            ];
        }

        $totalWithTax = $subtotalNoTax + $totalTax;
        $total = $totalWithTax;

        $totals = [
            'subtotal_no_tax' => $subtotalNoTax,
            'subtotal_0' => $subtotal0,
            'subtotal_12' => $subtotal12,
            'subtotal_15' => $subtotal15,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_ice' => 0,
            'tip' => 0,
            'total' => $total,
        ];

        return [$totals, $items];
    }
}
