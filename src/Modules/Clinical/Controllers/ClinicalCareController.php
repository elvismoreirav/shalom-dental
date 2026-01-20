<?php

namespace App\Modules\Clinical\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Modules\Clinical\Repositories\AppointmentProcedureRepository;
use App\Modules\Clinical\Repositories\ClinicalNoteRepository;
use App\Modules\Clinical\Repositories\PatientOdontogramRepository;
use App\Modules\Clinical\Services\InvoiceGeneratorService;

class ClinicalCareController
{
    private Database $db;
    private ClinicalNoteRepository $notes;
    private AppointmentProcedureRepository $procedures;
    private PatientOdontogramRepository $odontogram;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->notes = new ClinicalNoteRepository($this->db);
        $this->procedures = new AppointmentProcedureRepository($this->db);
        $this->odontogram = new PatientOdontogramRepository($this->db);
    }

    public function show(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $tab = (string) $request->query('tab', 'soap');

        $appointment = $this->db->selectOne(
            'SELECT a.*, p.first_name, p.last_name, p.id as patient_id,
                    at.name as appointment_type_name
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             LEFT JOIN appointment_types at ON at.id = a.appointment_type_id
             WHERE a.id = ?',
            [$appointmentId]
        );

        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        $note = $this->notes->findByAppointment($appointmentId) ?? [];
        $procedures = $this->procedures->listByAppointment($appointmentId);
        $appointmentTypes = $this->db->select('SELECT id, name, price_default, tax_percentage FROM appointment_types ORDER BY name');
        $odontogramRows = $this->odontogram->getByPatientId((int) $appointment['patient_id']);
        $odontogram = [];
        foreach ($odontogramRows as $row) {
            $odontogram[$row['tooth_number']] = $row;
        }
        $odontogramHistory = $this->odontogram->getHistoryByPatientId((int) $appointment['patient_id'], 20);

        return Response::view('clinical.attend', [
            'title' => 'Atención Clínica',
            'appointment' => $appointment,
            'tab' => 'soap'
        ]);
    }

    public function saveNote(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $appointment = $this->db->selectOne('SELECT patient_id, professional_id FROM appointments WHERE id = ?', [$appointmentId]);
        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        $userId = (int) (user()['id'] ?? 0);
        $data = [
            'appointment_id' => $appointmentId,
            'patient_id' => (int) $appointment['patient_id'],
            'professional_id' => (int) ($appointment['professional_id'] ?? $userId),
            'subjective' => $request->input('subjective'),
            'objective' => $request->input('objective'),
            'assessment' => $request->input('assessment'),
            'plan' => $request->input('plan'),
            'chief_complaint' => $request->input('chief_complaint'),
            'status' => $request->input('status', 'draft'),
        ];

        $this->notes->upsert($appointmentId, $data);
        session()->setFlash('success', 'Nota clínica actualizada.');
        return Response::redirect("/clinical/attend/{$appointmentId}?tab=soap");
    }

    public function addProcedure(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $appointment = $this->db->selectOne('SELECT patient_id FROM appointments WHERE id = ?', [$appointmentId]);
        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        $appointmentTypeId = (int) $request->input('appointment_type_id');
        if ($appointmentTypeId <= 0) {
            session()->setFlash('error', 'Seleccione un procedimiento.');
            return Response::redirect("/clinical/attend/{$appointmentId}?tab=procedures");
        }

        $quantity = (int) $request->input('quantity', 1);
        $unitPrice = (float) $request->input('unit_price', 0);
        $discount = (float) $request->input('discount_amount', 0);
        $subtotal = max(0, ($unitPrice * $quantity) - $discount);
        $taxPct = (float) $request->input('tax_percentage', 15);
        $taxAmount = $subtotal * ($taxPct / 100);
        $total = $subtotal + $taxAmount;

        $this->procedures->insert([
            'appointment_id' => $appointmentId,
            'appointment_type_id' => $appointmentTypeId,
            'tooth_number' => $request->input('tooth_number'),
            'surfaces' => $request->input('surfaces'),
            'description' => $request->input('description'),
            'notes' => $request->input('notes'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discount,
            'subtotal' => $subtotal,
            'tax_percentage' => $taxPct,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'created_by_user_id' => (int) (user()['id'] ?? 1),
        ]);

        session()->setFlash('success', 'Procedimiento agregado.');
        return Response::redirect("/clinical/attend/{$appointmentId}?tab=procedures");
    }

    public function deleteProcedure(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $procedureId = (int) $request->param('procedureId');
        $this->procedures->delete($procedureId);

        session()->setFlash('success', 'Procedimiento eliminado.');
        return Response::redirect("/clinical/attend/{$appointmentId}?tab=procedures");
    }

    public function generateInvoice(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        try {
            $invoiceId = (new InvoiceGeneratorService($this->db))
                ->generateFromAppointment($appointmentId, $organizationId, $locationId, $userId);
            session()->setFlash('success', 'Factura generada.');
            return Response::redirect("/billing/invoices/{$invoiceId}");
        } catch (\Exception $e) {
            session()->setFlash('error', $e->getMessage());
            return Response::redirect("/clinical/attend/{$appointmentId}?tab=billing");
        }
    }
}
