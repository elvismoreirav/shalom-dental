<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Appointment Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Agenda\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class AppointmentController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(Request $request): Response
    {
        $locationId = (int) session('current_location_id', 0);
        $organizationId = (int) session('organization_id', 0);

        $patients = $this->db->select(
            "SELECT id, first_name, last_name FROM patients" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY last_name, first_name LIMIT 100",
            $organizationId > 0 ? [$organizationId] : []
        );

        $appointmentTypes = $this->db->select(
            "SELECT id, name FROM appointment_types" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY name",
            $organizationId > 0 ? [$organizationId] : []
        );

        $professionals = $this->db->select(
            "SELECT id, first_name, last_name FROM users WHERE is_active = 1 AND is_professional = 1" .
            ($organizationId > 0 ? " AND organization_id = ?" : "") .
            " ORDER BY last_name, first_name",
            $organizationId > 0 ? [$organizationId] : []
        );

        return Response::view('agenda.create', [
            'title' => 'Nueva Cita',
            'patients' => $patients,
            'appointmentTypes' => $appointmentTypes,
            'professionals' => $professionals,
            'locationId' => $locationId,
        ]);
    }

    public function edit(Request $request): Response
    {
        $appointmentId = (int) $request->param('id');
        $locationId = (int) session('current_location_id', 0);
        $organizationId = (int) session('organization_id', 0);

        $appointment = $this->db->selectOne(
            "SELECT * FROM appointments WHERE id = ?" .
            ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$appointmentId, $locationId] : [$appointmentId]
        );

        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        $patients = $this->db->select(
            "SELECT id, first_name, last_name FROM patients" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY last_name, first_name LIMIT 100",
            $organizationId > 0 ? [$organizationId] : []
        );

        $appointmentTypes = $this->db->select(
            "SELECT id, name FROM appointment_types" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY name",
            $organizationId > 0 ? [$organizationId] : []
        );

        $professionals = $this->db->select(
            "SELECT id, first_name, last_name FROM users WHERE is_active = 1 AND is_professional = 1" .
            ($organizationId > 0 ? " AND organization_id = ?" : "") .
            " ORDER BY last_name, first_name",
            $organizationId > 0 ? [$organizationId] : []
        );

        return Response::view('agenda.edit', [
            'title' => 'Editar Cita',
            'appointment' => $appointment,
            'patients' => $patients,
            'appointmentTypes' => $appointmentTypes,
            'professionals' => $professionals,
            'locationId' => $locationId,
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $data = [
            'organization_id' => $organizationId,
            'location_id' => $locationId,
            'patient_id' => (int) $request->input('patient_id'),
            'professional_id' => (int) $request->input('professional_id'),
            'appointment_type_id' => (int) $request->input('appointment_type_id'),
            'scheduled_date' => $request->input('scheduled_date'),
            'scheduled_start_time' => $request->input('scheduled_start_time'),
            'scheduled_end_time' => $request->input('scheduled_end_time'),
            'duration_minutes' => (int) $request->input('duration_minutes', 30),
            'status' => $request->input('status', 'scheduled'),
            'notes' => trim((string) $request->input('notes')) ?: null,
            'created_by_user_id' => $userId ?: 1,
        ];

        $errors = [];
        if ($data['organization_id'] <= 0) {
            $errors['organization_id'] = 'Organizacion invalida.';
        }
        if ($data['location_id'] <= 0) {
            $errors['location_id'] = 'Sede invalida.';
        }
        if ($data['patient_id'] <= 0) {
            $errors['patient_id'] = 'Paciente es requerido.';
        }
        if ($data['professional_id'] <= 0) {
            $errors['professional_id'] = 'Profesional es requerido.';
        }
        if ($data['appointment_type_id'] <= 0) {
            $errors['appointment_type_id'] = 'Tipo de cita es requerido.';
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Complete los campos requeridos.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/agenda/create');
        }

        if (empty($data['scheduled_date'])) {
            $errors['scheduled_date'] = 'Fecha es requerida.';
        }
        if (empty($data['scheduled_start_time'])) {
            $errors['scheduled_start_time'] = 'Hora inicio es requerida.';
        }
        if (empty($data['scheduled_end_time'])) {
            $errors['scheduled_end_time'] = 'Hora fin es requerida.';
        }
        if (!empty($data['scheduled_start_time']) && !empty($data['scheduled_end_time'])) {
            if (strtotime($data['scheduled_end_time']) <= strtotime($data['scheduled_start_time'])) {
                $errors['scheduled_end_time'] = 'Hora fin debe ser mayor a hora inicio.';
            }
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Fecha y hora son requeridas.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/agenda/create');
        }

        if ($this->hasConflict(
            $data['professional_id'],
            $data['scheduled_date'],
            $data['scheduled_start_time'],
            $data['scheduled_end_time'],
            null
        )) {
            session()->setFlash('error', 'Conflicto con otra cita en ese horario.');
            session()->setFlash('errors', ['scheduled_start_time' => 'Horario ocupado.']);
            return Response::redirect('/agenda/create');
        }

        $appointmentId = $this->db->insert('appointments', $data);
        session()->setFlash('success', 'Cita creada correctamente.');

        return Response::redirect('/agenda');
    }

    public function update(Request $request): Response
    {
        $appointmentId = (int) $request->param('id');
        $locationId = (int) session('current_location_id', 0);

        $appointment = $this->db->selectOne(
            "SELECT id FROM appointments WHERE id = ?" .
            ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$appointmentId, $locationId] : [$appointmentId]
        );

        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        $data = [
            'patient_id' => (int) $request->input('patient_id'),
            'professional_id' => (int) $request->input('professional_id'),
            'appointment_type_id' => (int) $request->input('appointment_type_id'),
            'scheduled_date' => $request->input('scheduled_date'),
            'scheduled_start_time' => $request->input('scheduled_start_time'),
            'scheduled_end_time' => $request->input('scheduled_end_time'),
            'duration_minutes' => (int) $request->input('duration_minutes', 30),
            'status' => $request->input('status', 'scheduled'),
            'notes' => trim((string) $request->input('notes')) ?: null,
        ];

        $errors = [];
        if ($data['patient_id'] <= 0) {
            $errors['patient_id'] = 'Paciente es requerido.';
        }
        if ($data['professional_id'] <= 0) {
            $errors['professional_id'] = 'Profesional es requerido.';
        }
        if ($data['appointment_type_id'] <= 0) {
            $errors['appointment_type_id'] = 'Tipo de cita es requerido.';
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Complete los campos requeridos.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/agenda/' . $appointmentId . '/edit');
        }

        if (empty($data['scheduled_date'])) {
            $errors['scheduled_date'] = 'Fecha es requerida.';
        }
        if (empty($data['scheduled_start_time'])) {
            $errors['scheduled_start_time'] = 'Hora inicio es requerida.';
        }
        if (empty($data['scheduled_end_time'])) {
            $errors['scheduled_end_time'] = 'Hora fin es requerida.';
        }
        if (!empty($data['scheduled_start_time']) && !empty($data['scheduled_end_time'])) {
            if (strtotime($data['scheduled_end_time']) <= strtotime($data['scheduled_start_time'])) {
                $errors['scheduled_end_time'] = 'Hora fin debe ser mayor a hora inicio.';
            }
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Fecha y hora son requeridas.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/agenda/' . $appointmentId . '/edit');
        }

        if ($this->hasConflict(
            $data['professional_id'],
            $data['scheduled_date'],
            $data['scheduled_start_time'],
            $data['scheduled_end_time'],
            $appointmentId
        )) {
            session()->setFlash('error', 'Conflicto con otra cita en ese horario.');
            session()->setFlash('errors', ['scheduled_start_time' => 'Horario ocupado.']);
            return Response::redirect('/agenda/' . $appointmentId . '/edit');
        }

        $this->db->update('appointments', $data, 'id = ?', [$appointmentId]);
        session()->setFlash('success', 'Cita actualizada correctamente.');

        return Response::redirect('/agenda');
    }

    public function cancel(Request $request): Response
    {
        $appointmentId = (int) $request->param('id');
        $appointment = $this->loadAppointmentForAction($appointmentId, 'cancel');
        if ($appointment instanceof Response) {
            return $appointment;
        }

        $reason = trim((string) $request->input('reason')) ?: null;
        $userId = (int) (user()['id'] ?? 0);

        $this->db->update('appointments', [
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancellation_source' => 'clinic',
            'cancelled_by_user_id' => $userId ?: null,
            'cancelled_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$appointmentId]);

        session()->setFlash('success', 'Cita cancelada.');
        return Response::redirect('/agenda/' . $appointmentId);
    }

    public function noShow(Request $request): Response
    {
        $appointmentId = (int) $request->param('id');
        $appointment = $this->loadAppointmentForAction($appointmentId, 'no_show');
        if ($appointment instanceof Response) {
            return $appointment;
        }

        $reason = trim((string) $request->input('reason')) ?: null;
        $this->db->update('appointments', [
            'status' => 'no_show',
            'no_show_reason' => $reason,
        ], 'id = ?', [$appointmentId]);

        session()->setFlash('success', 'Cita marcada como no-show.');
        return Response::redirect('/agenda/' . $appointmentId);
    }

    private function loadAppointmentForAction(int $appointmentId, string $action): array|Response
    {
        $locationId = (int) session('current_location_id', 0);
        $appointment = $this->db->selectOne(
            "SELECT id, created_by_user_id FROM appointments WHERE id = ?" . ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$appointmentId, $locationId] : [$appointmentId]
        );

        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        if ($action === 'cancel') {
            $canAll = can('agenda.appointments.cancel_all');
            $canOwn = can('agenda.appointments.cancel_own');
            $userId = (int) (user()['id'] ?? 0);
            if (!$canAll && (!$canOwn || (int) $appointment['created_by_user_id'] !== $userId)) {
                return Response::forbidden('Sin permisos');
            }
        }

        return $appointment;
    }

    private function hasConflict(int $professionalId, string $date, string $start, string $end, ?int $excludeId): bool
    {
        $stmt = $this->db->query(
            "CALL sp_check_appointment_conflict(?, ?, ?, ?, ?, @has_conflict, @conflict_info)",
            [$professionalId, $date, $start, $end, $excludeId]
        );

        $stmt->closeCursor();
        $result = $this->db->selectOne("SELECT @has_conflict AS has_conflict");
        return !empty($result['has_conflict']);
    }
}
