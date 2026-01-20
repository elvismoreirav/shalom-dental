<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Clinical Note Controller
 * =========================================================================
 * Controller for SOAP clinical notes management
 */

namespace App\Modules\ClinicalCare\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ClinicalNoteController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Save or update clinical note for an appointment
     */
    public function save(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        // Verify appointment
        $appointment = $this->db->selectOne(
            "SELECT id, patient_id, professional_id FROM appointments WHERE id = ? AND organization_id = ?",
            [$appointmentId, $organizationId]
        );

        if (!$appointment) {
            return Response::json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        $data = [
            'appointment_id' => $appointmentId,
            'patient_id' => $appointment['patient_id'],
            'professional_id' => $appointment['professional_id'],
            'chief_complaint' => trim((string) $request->input('chief_complaint')),
            'subjective' => trim((string) $request->input('subjective')),
            'objective' => trim((string) $request->input('objective')),
            'assessment' => trim((string) $request->input('assessment')),
            'plan' => trim((string) $request->input('plan')),
            'vital_signs' => $request->input('vital_signs') ? json_encode($request->input('vital_signs')) : null,
        ];

        // Check if note already exists
        $existingNote = $this->db->selectOne(
            "SELECT id, status FROM clinical_notes WHERE appointment_id = ?",
            [$appointmentId]
        );

        if ($existingNote) {
            if ($existingNote['status'] === 'signed') {
                return Response::json(['success' => false, 'message' => 'La nota ya está firmada y no puede ser modificada'], 400);
            }
            $this->db->update('clinical_notes', $data, 'id = ?', [$existingNote['id']]);
            $noteId = $existingNote['id'];
        } else {
            $data['status'] = 'draft';
            $noteId = $this->db->insert('clinical_notes', $data);
        }

        return Response::json([
            'success' => true,
            'message' => 'Nota clínica guardada',
            'data' => ['id' => $noteId]
        ]);
    }

    /**
     * Sign clinical note
     */
    public function sign(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $note = $this->db->selectOne("
            SELECT cn.* FROM clinical_notes cn
            JOIN appointments a ON cn.appointment_id = a.id
            WHERE cn.appointment_id = ? AND a.organization_id = ?
        ", [$appointmentId, $organizationId]);

        if (!$note) {
            return Response::json(['success' => false, 'message' => 'Nota clínica no encontrada'], 404);
        }

        if ($note['status'] === 'signed') {
            return Response::json(['success' => false, 'message' => 'La nota ya está firmada'], 400);
        }

        // Validate required fields before signing
        if (empty($note['subjective']) && empty($note['objective']) && empty($note['assessment'])) {
            return Response::json(['success' => false, 'message' => 'Debe completar al menos un campo SOAP antes de firmar'], 400);
        }

        $this->db->update('clinical_notes', [
            'status' => 'signed',
            'signed_at' => date('Y-m-d H:i:s'),
            'signed_by_user_id' => $userId,
        ], 'id = ?', [$note['id']]);

        return Response::json([
            'success' => true,
            'message' => 'Nota clínica firmada correctamente'
        ]);
    }

    /**
     * Amend a signed clinical note
     */
    public function amend(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $note = $this->db->selectOne("
            SELECT cn.* FROM clinical_notes cn
            JOIN appointments a ON cn.appointment_id = a.id
            WHERE cn.appointment_id = ? AND a.organization_id = ?
        ", [$appointmentId, $organizationId]);

        if (!$note) {
            return Response::json(['success' => false, 'message' => 'Nota clínica no encontrada'], 404);
        }

        if ($note['status'] !== 'signed') {
            return Response::json(['success' => false, 'message' => 'Solo se pueden enmendar notas firmadas'], 400);
        }

        $amendmentNotes = trim((string) $request->input('amendment_notes'));
        if (empty($amendmentNotes)) {
            return Response::json(['success' => false, 'message' => 'Debe proporcionar la enmienda'], 400);
        }

        $this->db->update('clinical_notes', [
            'status' => 'amended',
            'amendment_notes' => $amendmentNotes,
            'amended_at' => date('Y-m-d H:i:s'),
            'amended_by_user_id' => $userId,
        ], 'id = ?', [$note['id']]);

        return Response::json([
            'success' => true,
            'message' => 'Enmienda agregada correctamente'
        ]);
    }

    /**
     * Get clinical note for an appointment (API)
     */
    public function show(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);

        $note = $this->db->selectOne("
            SELECT cn.*,
                   CONCAT(u.professional_title, ' ', u.first_name, ' ', u.last_name) as signed_by_name,
                   CONCAT(u2.professional_title, ' ', u2.first_name, ' ', u2.last_name) as amended_by_name
            FROM clinical_notes cn
            LEFT JOIN users u ON cn.signed_by_user_id = u.id
            LEFT JOIN users u2 ON cn.amended_by_user_id = u2.id
            JOIN appointments a ON cn.appointment_id = a.id
            WHERE cn.appointment_id = ? AND a.organization_id = ?
        ", [$appointmentId, $organizationId]);

        if (!$note) {
            return Response::json([
                'success' => true,
                'data' => null,
                'message' => 'No existe nota clínica para esta cita'
            ]);
        }

        $note['vital_signs'] = json_decode($note['vital_signs'] ?? '{}', true);

        return Response::json([
            'success' => true,
            'data' => $note
        ]);
    }

    /**
     * Get all clinical notes for a patient
     */
    public function patientNotes(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $organizationId = (int) session('organization_id', 0);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Verify patient
        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        }

        $total = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM clinical_notes WHERE patient_id = ?",
            [$patientId]
        )['total'];

        $notes = $this->db->select("
            SELECT cn.*,
                   CONCAT(u.professional_title, ' ', u.first_name, ' ', u.last_name) as professional_name,
                   a.scheduled_date,
                   at.name as appointment_type
            FROM clinical_notes cn
            JOIN users u ON cn.professional_id = u.id
            LEFT JOIN appointments a ON cn.appointment_id = a.id
            LEFT JOIN appointment_types at ON a.appointment_type_id = at.id
            WHERE cn.patient_id = ?
            ORDER BY cn.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", [$patientId]);

        return Response::json([
            'success' => true,
            'data' => $notes,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
}
