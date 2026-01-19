<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Appointment Detail Controller
 * =========================================================================
 */

namespace App\Modules\Agenda\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class AppointmentDetailController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function show(Request $request): Response
    {
        $appointmentId = (int) $request->param('id');
        $locationId = (int) session('current_location_id', 0);

        $appointment = $this->db->selectOne(
            "SELECT a.*, p.first_name, p.last_name, t.name AS appointment_type_name,
                    u.first_name AS professional_first_name, u.last_name AS professional_last_name
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             JOIN appointment_types t ON t.id = a.appointment_type_id
             JOIN users u ON u.id = a.professional_id
             WHERE a.id = ?" . ($locationId > 0 ? " AND a.location_id = ?" : ""),
            $locationId > 0 ? [$appointmentId, $locationId] : [$appointmentId]
        );

        if (!$appointment) {
            return Response::notFound('Cita no encontrada');
        }

        $audit = $this->db->select(
            "SELECT action, old_values, new_values, created_at
             FROM audit_logs
             WHERE entity_type = 'appointment' AND entity_id = ?
             ORDER BY created_at DESC",
            [$appointmentId]
        );

        return Response::view('agenda.show', [
            'title' => 'Detalle de Cita',
            'appointment' => $appointment,
            'audit' => $audit,
        ]);
    }
}
