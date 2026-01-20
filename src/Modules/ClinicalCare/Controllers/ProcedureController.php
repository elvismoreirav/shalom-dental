<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Procedure Controller
 * =========================================================================
 * Controller for managing procedures performed during appointments
 */

namespace App\Modules\ClinicalCare\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ProcedureController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Add a procedure to an appointment
     */
    public function store(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        // Verify appointment
        $appointment = $this->db->selectOne(
            "SELECT id, patient_id, status FROM appointments WHERE id = ? AND organization_id = ?",
            [$appointmentId, $organizationId]
        );

        if (!$appointment) {
            return Response::json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        // Get service type
        $serviceId = (int) $request->input('appointment_type_id');
        $service = $this->db->selectOne(
            "SELECT id, name, code, price_default, tax_percentage, applies_to_teeth FROM appointment_types WHERE id = ? AND organization_id = ?",
            [$serviceId, $organizationId]
        );

        if (!$service) {
            return Response::json(['success' => false, 'message' => 'Servicio no encontrado'], 404);
        }

        // Calculate pricing
        $quantity = max(1, (int) $request->input('quantity', 1));
        $unitPrice = (float) ($request->input('unit_price') ?? $service['price_default'] ?? 0);
        $discountAmount = (float) ($request->input('discount_amount') ?? 0);
        $taxPercentage = (float) ($service['tax_percentage'] ?? 15);

        $subtotal = ($unitPrice * $quantity) - $discountAmount;
        $taxAmount = $subtotal * ($taxPercentage / 100);
        $total = $subtotal + $taxAmount;

        // Determine tax code based on percentage
        $taxCode = '4'; // Default 15% IVA
        if ($taxPercentage == 0) {
            $taxCode = '0';
        } elseif ($taxPercentage == 12) {
            $taxCode = '2';
        }

        $data = [
            'appointment_id' => $appointmentId,
            'appointment_type_id' => $serviceId,
            'treatment_plan_item_id' => $request->input('treatment_plan_item_id') ? (int) $request->input('treatment_plan_item_id') : null,
            'tooth_number' => $request->input('tooth_number'),
            'surfaces' => $request->input('surfaces'),
            'description' => trim((string) $request->input('description')),
            'notes' => trim((string) $request->input('notes')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'tax_code' => $taxCode,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'created_by_user_id' => $userId,
        ];

        $procedureId = $this->db->insert('appointment_procedures', $data);

        // If linked to treatment plan item, update its status
        if ($data['treatment_plan_item_id']) {
            $this->db->update('treatment_plan_items', [
                'status' => 'completed',
                'completed_appointment_id' => $appointmentId,
                'completed_at' => date('Y-m-d H:i:s'),
                'final_price' => $total,
            ], 'id = ?', [$data['treatment_plan_item_id']]);

            // Update treatment plan progress
            $this->updateTreatmentPlanProgress($data['treatment_plan_item_id']);
        }

        // Get the created procedure with service info
        $procedure = $this->db->selectOne("
            SELECT ap.*, at.name as service_name, at.code as service_code
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            WHERE ap.id = ?
        ", [$procedureId]);

        return Response::json([
            'success' => true,
            'message' => 'Procedimiento agregado',
            'data' => $procedure
        ]);
    }

    /**
     * Update a procedure
     */
    public function update(Request $request): Response
    {
        $procedureId = (int) $request->param('procedureId');
        $organizationId = (int) session('organization_id', 0);

        $procedure = $this->db->selectOne("
            SELECT ap.* FROM appointment_procedures ap
            JOIN appointments a ON ap.appointment_id = a.id
            WHERE ap.id = ? AND a.organization_id = ?
        ", [$procedureId, $organizationId]);

        if (!$procedure) {
            return Response::json(['success' => false, 'message' => 'Procedimiento no encontrado'], 404);
        }

        if ($procedure['is_invoiced']) {
            return Response::json(['success' => false, 'message' => 'No se puede modificar un procedimiento ya facturado'], 400);
        }

        // Get service for tax calculation
        $serviceId = (int) ($request->input('appointment_type_id') ?? $procedure['appointment_type_id']);
        $service = $this->db->selectOne(
            "SELECT tax_percentage FROM appointment_types WHERE id = ?",
            [$serviceId]
        );

        $quantity = max(1, (int) ($request->input('quantity') ?? $procedure['quantity']));
        $unitPrice = (float) ($request->input('unit_price') ?? $procedure['unit_price']);
        $discountAmount = (float) ($request->input('discount_amount') ?? $procedure['discount_amount']);
        $taxPercentage = (float) ($service['tax_percentage'] ?? 15);

        $subtotal = ($unitPrice * $quantity) - $discountAmount;
        $taxAmount = $subtotal * ($taxPercentage / 100);
        $total = $subtotal + $taxAmount;

        $taxCode = '4';
        if ($taxPercentage == 0) {
            $taxCode = '0';
        } elseif ($taxPercentage == 12) {
            $taxCode = '2';
        }

        $data = [
            'appointment_type_id' => $serviceId,
            'tooth_number' => $request->input('tooth_number') ?? $procedure['tooth_number'],
            'surfaces' => $request->input('surfaces') ?? $procedure['surfaces'],
            'description' => trim((string) ($request->input('description') ?? $procedure['description'])),
            'notes' => trim((string) ($request->input('notes') ?? $procedure['notes'])),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'tax_code' => $taxCode,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];

        $this->db->update('appointment_procedures', $data, 'id = ?', [$procedureId]);

        $updated = $this->db->selectOne("
            SELECT ap.*, at.name as service_name, at.code as service_code
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            WHERE ap.id = ?
        ", [$procedureId]);

        return Response::json([
            'success' => true,
            'message' => 'Procedimiento actualizado',
            'data' => $updated
        ]);
    }

    /**
     * Delete a procedure
     */
    public function delete(Request $request): Response
    {
        $procedureId = (int) $request->param('procedureId');
        $organizationId = (int) session('organization_id', 0);

        $procedure = $this->db->selectOne("
            SELECT ap.* FROM appointment_procedures ap
            JOIN appointments a ON ap.appointment_id = a.id
            WHERE ap.id = ? AND a.organization_id = ?
        ", [$procedureId, $organizationId]);

        if (!$procedure) {
            return Response::json(['success' => false, 'message' => 'Procedimiento no encontrado'], 404);
        }

        if ($procedure['is_invoiced']) {
            return Response::json(['success' => false, 'message' => 'No se puede eliminar un procedimiento ya facturado'], 400);
        }

        // If linked to treatment plan, revert the item status
        if ($procedure['treatment_plan_item_id']) {
            $this->db->update('treatment_plan_items', [
                'status' => 'pending',
                'completed_appointment_id' => null,
                'completed_at' => null,
                'final_price' => null,
            ], 'id = ?', [$procedure['treatment_plan_item_id']]);

            $this->updateTreatmentPlanProgress($procedure['treatment_plan_item_id']);
        }

        $this->db->delete('appointment_procedures', 'id = ?', [$procedureId]);

        return Response::json([
            'success' => true,
            'message' => 'Procedimiento eliminado'
        ]);
    }

    /**
     * Get all procedures for an appointment
     */
    public function index(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);

        $procedures = $this->db->select("
            SELECT ap.*, at.name as service_name, at.code as service_code,
                   dsc.name as category_name
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            LEFT JOIN dental_service_categories dsc ON at.category_id = dsc.id
            JOIN appointments a ON ap.appointment_id = a.id
            WHERE ap.appointment_id = ? AND a.organization_id = ?
            ORDER BY ap.created_at
        ", [$appointmentId, $organizationId]);

        // Calculate totals
        $totals = [
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
            'pending_invoice' => 0
        ];

        foreach ($procedures as $proc) {
            $totals['subtotal'] += (float) $proc['subtotal'];
            $totals['discount'] += (float) $proc['discount_amount'];
            $totals['tax'] += (float) $proc['tax_amount'];
            $totals['total'] += (float) $proc['total'];
            if (!$proc['is_invoiced']) {
                $totals['pending_invoice'] += (float) $proc['total'];
            }
        }

        return Response::json([
            'success' => true,
            'data' => [
                'procedures' => $procedures,
                'totals' => $totals
            ]
        ]);
    }

    /**
     * Get pending procedures for invoicing
     */
    public function pendingInvoice(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);

        $procedures = $this->db->select("
            SELECT ap.*, at.name as service_name, at.code as service_code
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            JOIN appointments a ON ap.appointment_id = a.id
            WHERE ap.appointment_id = ? AND a.organization_id = ? AND ap.is_invoiced = 0
            ORDER BY ap.created_at
        ", [$appointmentId, $organizationId]);

        return Response::json([
            'success' => true,
            'data' => $procedures
        ]);
    }

    /**
     * Helper: Update treatment plan progress
     */
    private function updateTreatmentPlanProgress(int $itemId): void
    {
        $item = $this->db->selectOne(
            "SELECT treatment_plan_id FROM treatment_plan_items WHERE id = ?",
            [$itemId]
        );

        if (!$item) return;

        $planId = $item['treatment_plan_id'];

        $stats = $this->db->selectOne("
            SELECT
                COUNT(*) as total_items,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_items,
                SUM(estimated_price) as total_estimated,
                SUM(CASE WHEN is_invoiced = 1 THEN final_price ELSE 0 END) as total_invoiced
            FROM treatment_plan_items
            WHERE treatment_plan_id = ?
        ", [$planId]);

        $status = 'in_progress';
        if ($stats['completed_items'] == 0) {
            $status = 'accepted';
        } elseif ($stats['completed_items'] == $stats['total_items']) {
            $status = 'completed';
        }

        $this->db->update('treatment_plans', [
            'total_items' => $stats['total_items'],
            'completed_items' => $stats['completed_items'],
            'total_estimated' => $stats['total_estimated'] ?? 0,
            'total_invoiced' => $stats['total_invoiced'] ?? 0,
            'status' => $status,
            'completed_at' => $status === 'completed' ? date('Y-m-d H:i:s') : null,
        ], 'id = ?', [$planId]);
    }
}
