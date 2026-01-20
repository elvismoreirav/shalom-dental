<?php

namespace App\Modules\Clinical\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Modules\Clinical\Repositories\TreatmentPlanItemRepository;
use App\Modules\Clinical\Repositories\TreatmentPlanRepository;

class TreatmentPlanController
{
    private TreatmentPlanRepository $plans;
    private TreatmentPlanItemRepository $items;
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->plans = new TreatmentPlanRepository($this->db);
        $this->items = new TreatmentPlanItemRepository($this->db);
    }

    public function index(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $plans = $this->plans->listByPatient($patientId);

        return Response::view('clinical.treatment-plans.index', [
            'title' => 'Planes de tratamiento',
            'patientId' => $patientId,
            'plans' => $plans,
        ]);
    }

    public function create(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $appointmentTypes = $this->fetchAppointmentTypes();

        return Response::view('clinical.treatment-plans.create', [
            'title' => 'Nuevo plan',
            'patientId' => $patientId,
            'appointmentTypes' => $appointmentTypes,
            'plan' => [],
            'items' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $planId = $this->plans->create([
            'organization_id' => $organizationId,
            'patient_id' => $patientId,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'draft'),
            'priority' => $request->input('priority', 'normal'),
            'estimated_completion_date' => $request->input('estimated_completion_date'),
            'notes' => $request->input('notes'),
            'patient_observations' => $request->input('patient_observations'),
            'created_by_user_id' => $userId ?: 1,
        ]);

        $items = $request->input('items', []);
        $this->items->replaceItems($planId, $items);
        $this->syncTotals($planId);

        session()->setFlash('success', 'Plan de tratamiento creado.');
        return Response::redirect("/patients/{$patientId}/treatment-plans/{$planId}");
    }

    public function show(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $planId = (int) $request->param('planId');
        $plan = $this->plans->find($planId);

        if (!$plan) {
            return Response::notFound('Plan no encontrado');
        }

        $items = $this->items->listByPlan($planId);

        return Response::view('clinical.treatment-plans.show', [
            'title' => 'Detalle plan',
            'patientId' => $patientId,
            'plan' => $plan,
            'items' => $items,
        ]);
    }

    public function edit(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $planId = (int) $request->param('planId');
        $plan = $this->plans->find($planId);

        if (!$plan) {
            return Response::notFound('Plan no encontrado');
        }

        $appointmentTypes = $this->fetchAppointmentTypes();
        $items = $this->items->listByPlan($planId);

        return Response::view('clinical.treatment-plans.edit', [
            'title' => 'Editar plan',
            'patientId' => $patientId,
            'plan' => $plan,
            'items' => $items,
            'appointmentTypes' => $appointmentTypes,
        ]);
    }

    public function update(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $planId = (int) $request->param('planId');

        $this->plans->update($planId, [
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'draft'),
            'priority' => $request->input('priority', 'normal'),
            'estimated_completion_date' => $request->input('estimated_completion_date'),
            'notes' => $request->input('notes'),
            'patient_observations' => $request->input('patient_observations'),
        ]);

        $items = $request->input('items', []);
        $this->items->replaceItems($planId, $items);
        $this->syncTotals($planId);

        session()->setFlash('success', 'Plan actualizado.');
        return Response::redirect("/patients/{$patientId}/treatment-plans/{$planId}");
    }

    public function delete(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $planId = (int) $request->param('planId');
        $this->plans->delete($planId);

        session()->setFlash('success', 'Plan eliminado.');
        return Response::redirect("/patients/{$patientId}/treatment-plans");
    }

    private function fetchAppointmentTypes(): array
    {
        $organizationId = (int) session('organization_id', 0);
        return $this->db->select(
            'SELECT id, name, price_default FROM appointment_types' . ($organizationId ? ' WHERE organization_id = ?' : '') . ' ORDER BY name',
            $organizationId ? [$organizationId] : []
        );
    }

    private function syncTotals(int $planId): void
    {
        $items = $this->items->listByPlan($planId);
        $totalItems = count($items);
        $completedItems = 0;
        $totalEstimated = 0.0;
        $totalInvoiced = 0.0;

        foreach ($items as $item) {
            if (($item['status'] ?? '') === 'completed') {
                $completedItems++;
            }
            $totalEstimated += (float) ($item['estimated_price'] ?? 0);
            if (!empty($item['is_invoiced'])) {
                $totalInvoiced += (float) ($item['final_price'] ?? 0);
            }
        }

        $this->plans->update($planId, [
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'total_estimated' => $totalEstimated,
            'total_invoiced' => $totalInvoiced,
        ]);
    }
}
