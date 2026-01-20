<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Treatment Plan Controller
 * =========================================================================
 * Controller for managing patient treatment plans
 */

namespace App\Modules\ClinicalCare\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class TreatmentPlanController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List treatment plans for a patient
     */
    public function index(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $organizationId = (int) session('organization_id', 0);

        // Verify patient
        $patient = $this->db->selectOne(
            "SELECT id, first_name, last_name FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::notFound('Paciente no encontrado');
        }

        $plans = $this->db->select("
            SELECT tp.*,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                   ROUND((tp.completed_items / NULLIF(tp.total_items, 0)) * 100, 0) as progress
            FROM treatment_plans tp
            JOIN users u ON tp.created_by_user_id = u.id
            WHERE tp.patient_id = ?
            ORDER BY
                CASE tp.status
                    WHEN 'in_progress' THEN 1
                    WHEN 'accepted' THEN 2
                    WHEN 'proposed' THEN 3
                    WHEN 'draft' THEN 4
                    ELSE 5
                END,
                tp.created_at DESC
        ", [$patientId]);

        return Response::view('clinical.treatment-plans.index', [
            'title' => 'Planes de Tratamiento - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'plans' => $plans,
        ]);
    }

    /**
     * Show treatment plan detail
     */
    public function show(Request $request): Response
    {
        $planId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $plan = $this->db->selectOne("
            SELECT tp.*,
                   p.id as patient_id,
                   p.first_name as patient_first_name,
                   p.last_name as patient_last_name,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM treatment_plans tp
            JOIN patients p ON tp.patient_id = p.id
            JOIN users u ON tp.created_by_user_id = u.id
            WHERE tp.id = ? AND tp.organization_id = ?
        ", [$planId, $organizationId]);

        if (!$plan) {
            return Response::notFound('Plan de tratamiento no encontrado');
        }

        $items = $this->db->select("
            SELECT tpi.*,
                   at.name as service_name,
                   at.code as service_code,
                   dsc.name as category_name,
                   dsc.color_hex as category_color,
                   a.scheduled_date,
                   a.scheduled_start_time
            FROM treatment_plan_items tpi
            JOIN appointment_types at ON tpi.appointment_type_id = at.id
            LEFT JOIN dental_service_categories dsc ON at.category_id = dsc.id
            LEFT JOIN appointments a ON tpi.scheduled_appointment_id = a.id
            WHERE tpi.treatment_plan_id = ?
            ORDER BY tpi.phase, tpi.sequence_order
        ", [$planId]);

        // Group items by phase
        $itemsByPhase = [];
        foreach ($items as $item) {
            $phase = $item['phase'] ?: 'General';
            if (!isset($itemsByPhase[$phase])) {
                $itemsByPhase[$phase] = [];
            }
            $itemsByPhase[$phase][] = $item;
        }

        return Response::view('clinical.treatment-plans.show', [
            'title' => 'Plan: ' . $plan['name'],
            'plan' => $plan,
            'items' => $items,
            'itemsByPhase' => $itemsByPhase,
        ]);
    }

    /**
     * Create treatment plan form
     */
    public function create(Request $request): Response
    {
        $patientId = (int) $request->query('patient_id', 0);
        $organizationId = (int) session('organization_id', 0);

        $patient = null;
        if ($patientId > 0) {
            $patient = $this->db->selectOne(
                "SELECT id, first_name, last_name FROM patients WHERE id = ? AND organization_id = ?",
                [$patientId, $organizationId]
            );
        }

        $hasAppliesToTeeth = $this->hasAppointmentTypeColumn('applies_to_teeth');
        $hasCategoryId = $this->hasAppointmentTypeColumn('category_id');
        $hasServiceSort = $this->hasAppointmentTypeColumn('sort_order');
        $appliesField = $hasAppliesToTeeth ? 'at.applies_to_teeth' : '0 as applies_to_teeth';

        $categorySelect = $hasCategoryId
            ? 'dsc.id as category_id, dsc.name as category_name, dsc.color_hex'
            : 'NULL as category_id, NULL as category_name, NULL as category_color';
        $categoryJoin = $hasCategoryId
            ? 'LEFT JOIN dental_service_categories dsc ON at.category_id = dsc.id'
            : '';
        $orderBy = $hasCategoryId
            ? ('dsc.sort_order, ' . ($hasServiceSort ? 'at.sort_order, ' : '') . 'at.name')
            : (($hasServiceSort ? 'at.sort_order, ' : '') . 'at.name');

        // Get services grouped by category
        $services = $this->db->select("
            SELECT at.id, at.code, at.name, at.price_default, {$appliesField},
                   {$categorySelect}
            FROM appointment_types at
            {$categoryJoin}
            WHERE at.organization_id = ? AND at.is_active = 1
            ORDER BY {$orderBy}
        ", [$organizationId]);

        $categories = $this->db->select(
            "SELECT id, code, name, color_hex FROM dental_service_categories WHERE organization_id = ? AND is_active = 1 ORDER BY sort_order",
            [$organizationId]
        );

        return Response::view('clinical.treatment-plans.create', [
            'title' => 'Nuevo Plan de Tratamiento',
            'patient' => $patient,
            'services' => $services,
            'categories' => $categories,
        ]);
    }

    private function hasAppointmentTypeColumn(string $column): bool
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointment_types' AND COLUMN_NAME = ?",
            [$column]
        );

        return ((int) ($result['c'] ?? 0)) > 0;
    }

    /**
     * Store new treatment plan
     */
    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $patientId = (int) $request->input('patient_id');

        // Verify patient
        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            session()->setFlash('error', 'Paciente no encontrado.');
            return Response::redirect('/clinical/treatment-plans/create');
        }

        $data = [
            'organization_id' => $organizationId,
            'patient_id' => $patientId,
            'code' => $request->input('code') ?: null,
            'name' => trim((string) $request->input('name')),
            'description' => trim((string) $request->input('description')),
            'status' => $request->input('status', 'draft'),
            'priority' => $request->input('priority', 'normal'),
            'estimated_completion_date' => $request->input('estimated_completion_date') ?: null,
            'notes' => trim((string) $request->input('notes')),
            'created_by_user_id' => $userId,
        ];

        // Validation
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre del plan es requerido.';
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Complete los campos requeridos.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/clinical/treatment-plans/create?patient_id=' . $patientId);
        }

        // Generate code if not provided
        if (empty($data['code'])) {
            $count = $this->db->selectOne(
                "SELECT COUNT(*) as c FROM treatment_plans WHERE patient_id = ?",
                [$patientId]
            )['c'];
            $data['code'] = 'PT-' . str_pad($patientId, 4, '0', STR_PAD_LEFT) . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $planId = $this->db->insert('treatment_plans', $data);

        // Add items if provided
        $items = $request->input('items', []);
        $totalEstimated = 0;
        $sequence = 0;

        foreach ($items as $item) {
            if (empty($item['appointment_type_id'])) continue;

            $price = (float) ($item['estimated_price'] ?? 0);
            $totalEstimated += $price;
            $sequence++;

            $this->db->insert('treatment_plan_items', [
                'treatment_plan_id' => $planId,
                'appointment_type_id' => (int) $item['appointment_type_id'],
                'sequence_order' => $sequence,
                'phase' => $item['phase'] ?? null,
                'tooth_number' => $item['tooth_number'] ?? null,
                'surfaces' => $item['surfaces'] ?? null,
                'description' => $item['description'] ?? null,
                'estimated_price' => $price,
                'status' => 'pending',
            ]);
        }

        // Update totals
        $this->db->update('treatment_plans', [
            'total_items' => $sequence,
            'total_estimated' => $totalEstimated,
        ], 'id = ?', [$planId]);

        session()->setFlash('success', 'Plan de tratamiento creado correctamente.');
        return Response::redirect('/clinical/treatment-plans/' . $planId);
    }

    /**
     * Update treatment plan status
     */
    public function updateStatus(Request $request): Response
    {
        $planId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);
        $newStatus = $request->input('status');

        $plan = $this->db->selectOne(
            "SELECT id, status FROM treatment_plans WHERE id = ? AND organization_id = ?",
            [$planId, $organizationId]
        );

        if (!$plan) {
            return Response::json(['success' => false, 'message' => 'Plan no encontrado'], 404);
        }

        $validTransitions = [
            'draft' => ['proposed', 'cancelled'],
            'proposed' => ['accepted', 'draft', 'cancelled'],
            'accepted' => ['in_progress', 'on_hold', 'cancelled'],
            'in_progress' => ['completed', 'on_hold', 'cancelled'],
            'on_hold' => ['in_progress', 'cancelled'],
        ];

        if (!isset($validTransitions[$plan['status']]) ||
            !in_array($newStatus, $validTransitions[$plan['status']])) {
            return Response::json(['success' => false, 'message' => 'Transición de estado no válida'], 400);
        }

        $updateData = ['status' => $newStatus];

        // Set timestamps based on status
        switch ($newStatus) {
            case 'proposed':
                $updateData['proposed_at'] = date('Y-m-d H:i:s');
                break;
            case 'accepted':
                $updateData['accepted_at'] = date('Y-m-d H:i:s');
                break;
            case 'in_progress':
                if (!$plan['started_at']) {
                    $updateData['started_at'] = date('Y-m-d H:i:s');
                }
                break;
            case 'completed':
                $updateData['completed_at'] = date('Y-m-d H:i:s');
                break;
        }

        $this->db->update('treatment_plans', $updateData, 'id = ?', [$planId]);

        return Response::json([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    /**
     * Add item to existing plan
     */
    public function addItem(Request $request): Response
    {
        $planId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $plan = $this->db->selectOne(
            "SELECT id, status FROM treatment_plans WHERE id = ? AND organization_id = ?",
            [$planId, $organizationId]
        );

        if (!$plan) {
            return Response::json(['success' => false, 'message' => 'Plan no encontrado'], 404);
        }

        if (in_array($plan['status'], ['completed', 'cancelled'])) {
            return Response::json(['success' => false, 'message' => 'No se pueden agregar items a un plan ' . $plan['status']], 400);
        }

        $serviceId = (int) $request->input('appointment_type_id');
        $service = $this->db->selectOne(
            "SELECT id, name, price_default FROM appointment_types WHERE id = ? AND organization_id = ?",
            [$serviceId, $organizationId]
        );

        if (!$service) {
            return Response::json(['success' => false, 'message' => 'Servicio no encontrado'], 404);
        }

        // Get next sequence
        $maxSeq = $this->db->selectOne(
            "SELECT MAX(sequence_order) as seq FROM treatment_plan_items WHERE treatment_plan_id = ?",
            [$planId]
        )['seq'] ?? 0;

        $price = (float) ($request->input('estimated_price') ?? $service['price_default'] ?? 0);

        $itemId = $this->db->insert('treatment_plan_items', [
            'treatment_plan_id' => $planId,
            'appointment_type_id' => $serviceId,
            'sequence_order' => $maxSeq + 1,
            'phase' => $request->input('phase'),
            'tooth_number' => $request->input('tooth_number'),
            'surfaces' => $request->input('surfaces'),
            'description' => $request->input('description'),
            'estimated_price' => $price,
            'status' => 'pending',
        ]);

        // Update plan totals
        $this->updatePlanTotals($planId);

        return Response::json([
            'success' => true,
            'message' => 'Item agregado al plan',
            'data' => ['id' => $itemId]
        ]);
    }

    /**
     * Remove item from plan
     */
    public function removeItem(Request $request): Response
    {
        $planId = (int) $request->param('id');
        $itemId = (int) $request->param('itemId');
        $organizationId = (int) session('organization_id', 0);

        $item = $this->db->selectOne("
            SELECT tpi.* FROM treatment_plan_items tpi
            JOIN treatment_plans tp ON tpi.treatment_plan_id = tp.id
            WHERE tpi.id = ? AND tpi.treatment_plan_id = ? AND tp.organization_id = ?
        ", [$itemId, $planId, $organizationId]);

        if (!$item) {
            return Response::json(['success' => false, 'message' => 'Item no encontrado'], 404);
        }

        if ($item['status'] === 'completed') {
            return Response::json(['success' => false, 'message' => 'No se puede eliminar un item completado'], 400);
        }

        $this->db->delete('treatment_plan_items', 'id = ?', [$itemId]);
        $this->updatePlanTotals($planId);

        return Response::json([
            'success' => true,
            'message' => 'Item eliminado del plan'
        ]);
    }

    /**
     * Get plan items (API)
     */
    public function getItems(Request $request): Response
    {
        $planId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $plan = $this->db->selectOne(
            "SELECT id FROM treatment_plans WHERE id = ? AND organization_id = ?",
            [$planId, $organizationId]
        );

        if (!$plan) {
            return Response::json(['success' => false, 'message' => 'Plan no encontrado'], 404);
        }

        $items = $this->db->select("
            SELECT tpi.*,
                   at.name as service_name,
                   at.code as service_code
            FROM treatment_plan_items tpi
            JOIN appointment_types at ON tpi.appointment_type_id = at.id
            WHERE tpi.treatment_plan_id = ?
            ORDER BY tpi.phase, tpi.sequence_order
        ", [$planId]);

        return Response::json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Helper: Update plan totals
     */
    private function updatePlanTotals(int $planId): void
    {
        $stats = $this->db->selectOne("
            SELECT
                COUNT(*) as total_items,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_items,
                SUM(estimated_price) as total_estimated,
                SUM(CASE WHEN is_invoiced = 1 THEN final_price ELSE 0 END) as total_invoiced
            FROM treatment_plan_items
            WHERE treatment_plan_id = ?
        ", [$planId]);

        $this->db->update('treatment_plans', [
            'total_items' => $stats['total_items'] ?? 0,
            'completed_items' => $stats['completed_items'] ?? 0,
            'total_estimated' => $stats['total_estimated'] ?? 0,
            'total_invoiced' => $stats['total_invoiced'] ?? 0,
        ], 'id = ?', [$planId]);
    }
}
