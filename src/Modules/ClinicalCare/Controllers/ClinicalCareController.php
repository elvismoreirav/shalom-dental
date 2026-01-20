<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Clinical Care Controller
 * =========================================================================
 * Main controller for clinical attendance - Professional dental care view
 */

namespace App\Modules\ClinicalCare\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ClinicalCareController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Main clinical care view - Attend a patient appointment
     * Shows tabs: Summary, History, Odontogram, Treatment Plan, Invoice
     */
    public function attend(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);
        $currentLocationId = (int) session('current_location_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        // Get appointment with related data
        $appointment = $this->db->selectOne("
            SELECT
                a.*,
                p.id as patient_id,
                p.first_name as patient_first_name,
                p.last_name as patient_last_name,
                p.id_type as patient_id_type,
                p.id_number as patient_id_number,
                p.phone as patient_phone,
                p.email as patient_email,
                p.birth_date as patient_birth_date,
                p.gender as patient_gender,
                p.blood_type as patient_blood_type,
                p.allergies as patient_allergies,
                p.current_medications as patient_medications,
                p.medical_conditions as patient_conditions,
                at.name as appointment_type_name,
                at.code as appointment_type_code,
                at.color_hex as appointment_color,
                at.price_default as service_price,
                at.tax_percentage as service_tax,
                u.first_name as professional_first_name,
                u.last_name as professional_last_name,
                u.professional_title,
                l.name as location_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN appointment_types at ON a.appointment_type_id = at.id
            JOIN users u ON a.professional_id = u.id
            JOIN locations l ON a.location_id = l.id
            WHERE a.id = ? AND a.organization_id = ?
        ", [$appointmentId, $organizationId]);

        if (!$appointment) {
            session()->setFlash('error', 'Cita no encontrada.');
            return Response::redirect('/agenda');
        }

        // Get patient's medical alerts (allergies, conditions)
        $alerts = [];
        if (!empty($appointment['patient_allergies'])) {
            $alerts[] = ['type' => 'allergy', 'message' => 'Alergias: ' . $appointment['patient_allergies']];
        }
        if (!empty($appointment['patient_conditions'])) {
            $alerts[] = ['type' => 'condition', 'message' => 'Condiciones: ' . $appointment['patient_conditions']];
        }
        if (!empty($appointment['patient_medications'])) {
            $alerts[] = ['type' => 'medication', 'message' => 'Medicamentos: ' . $appointment['patient_medications']];
        }

        // Get odontogram summary
        $odontogramSummary = $this->db->select("
            SELECT tooth_number, tooth_status, surfaces
            FROM patient_odontogram
            WHERE patient_id = ?
            ORDER BY tooth_number
        ", [$appointment['patient_id']]);

        // Get active treatment plans
        $treatmentPlans = $this->db->select("
            SELECT id, name, status, total_items, completed_items,
                   ROUND((completed_items / NULLIF(total_items, 0)) * 100, 0) as progress
            FROM treatment_plans
            WHERE patient_id = ? AND status IN ('accepted', 'in_progress')
            ORDER BY created_at DESC
            LIMIT 5
        ", [$appointment['patient_id']]);

        // Get previous clinical notes (last 5)
        $previousNotes = $this->db->select("
            SELECT cn.id, cn.chief_complaint, cn.assessment, cn.created_at,
                   CONCAT(u.professional_title, ' ', u.first_name, ' ', u.last_name) as professional
            FROM clinical_notes cn
            JOIN users u ON cn.professional_id = u.id
            WHERE cn.patient_id = ?
            ORDER BY cn.created_at DESC
            LIMIT 5
        ", [$appointment['patient_id']]);

        // Get existing clinical note for this appointment
        $clinicalNote = $this->db->selectOne("
            SELECT * FROM clinical_notes WHERE appointment_id = ?
        ", [$appointmentId]);

        // Get procedures added to this appointment
        $procedures = $this->db->select("
            SELECT ap.*, at.name as service_name, at.code as service_code
            FROM appointment_procedures ap
            JOIN appointment_types at ON ap.appointment_type_id = at.id
            WHERE ap.appointment_id = ?
            ORDER BY ap.created_at
        ", [$appointmentId]);

        // Get available services for adding procedures
        $services = $this->db->select("
            SELECT at.id, at.code, at.name, at.price_default, at.tax_percentage,
                   at.applies_to_teeth, at.max_teeth_per_session,
                   dsc.name as category_name, dsc.color_hex as category_color
            FROM appointment_types at
            LEFT JOIN dental_service_categories dsc ON at.category_id = dsc.id
            WHERE at.organization_id = ? AND at.is_active = 1
            ORDER BY dsc.sort_order, at.sort_order, at.name
        ", [$organizationId]);

        // Get service categories
        $serviceCategories = $this->db->select("
            SELECT id, code, name, color_hex, icon
            FROM dental_service_categories
            WHERE organization_id = ? AND is_active = 1
            ORDER BY sort_order
        ", [$organizationId]);

        // Calculate totals for procedures
        $procedureTotals = [
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'pending_invoice' => 0
        ];
        foreach ($procedures as $proc) {
            $procedureTotals['subtotal'] += (float) $proc['subtotal'];
            $procedureTotals['tax'] += (float) $proc['tax_amount'];
            $procedureTotals['total'] += (float) $proc['total'];
            if (!$proc['is_invoiced']) {
                $procedureTotals['pending_invoice'] += (float) $proc['total'];
            }
        }

        // Get active tab from query string
        $activeTab = $request->query('tab', 'summary');

        return Response::view('clinical.attend', [
            'title' => 'Atención Clínica',
            'appointment' => $appointment,
            'alerts' => $alerts,
            'odontogramSummary' => $odontogramSummary,
            'treatmentPlans' => $treatmentPlans,
            'previousNotes' => $previousNotes,
            'clinicalNote' => $clinicalNote,
            'procedures' => $procedures,
            'services' => $services,
            'serviceCategories' => $serviceCategories,
            'procedureTotals' => $procedureTotals,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Start appointment - Change status to in_progress
     */
    public function startAppointment(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);

        $appointment = $this->db->selectOne(
            "SELECT id, status FROM appointments WHERE id = ? AND organization_id = ?",
            [$appointmentId, $organizationId]
        );

        if (!$appointment) {
            return Response::json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        if (!in_array($appointment['status'], ['scheduled', 'confirmed', 'checked_in'])) {
            return Response::json(['success' => false, 'message' => 'La cita no puede ser iniciada en su estado actual'], 400);
        }

        $this->db->update('appointments', [
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$appointmentId]);

        return Response::json(['success' => true, 'message' => 'Atención iniciada']);
    }

    /**
     * Complete appointment - Change status to completed
     */
    public function completeAppointment(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $organizationId = (int) session('organization_id', 0);

        $appointment = $this->db->selectOne(
            "SELECT id, status FROM appointments WHERE id = ? AND organization_id = ?",
            [$appointmentId, $organizationId]
        );

        if (!$appointment) {
            return Response::json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        if ($appointment['status'] !== 'in_progress') {
            return Response::json(['success' => false, 'message' => 'La cita debe estar en progreso para completarla'], 400);
        }

        $this->db->update('appointments', [
            'status' => 'completed',
            'finished_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$appointmentId]);

        return Response::json(['success' => true, 'message' => 'Atención completada']);
    }

    /**
     * Patient clinical history summary
     */
    public function patientHistory(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $organizationId = (int) session('organization_id', 0);

        $patient = $this->db->selectOne("
            SELECT p.*,
                   (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id AND status = 'completed') as total_appointments,
                   (SELECT MAX(scheduled_date) FROM appointments WHERE patient_id = p.id AND status = 'completed') as last_visit
            FROM patients p
            WHERE p.id = ? AND p.organization_id = ?
        ", [$patientId, $organizationId]);

        if (!$patient) {
            return Response::notFound('Paciente no encontrado');
        }

        // Get clinical record
        $clinicalRecord = $this->db->selectOne(
            "SELECT * FROM patient_clinical_records WHERE patient_id = ?",
            [$patientId]
        );

        // Get all clinical notes
        $clinicalNotes = $this->db->select("
            SELECT cn.*,
                   CONCAT(u.professional_title, ' ', u.first_name, ' ', u.last_name) as professional_name,
                   a.scheduled_date
            FROM clinical_notes cn
            JOIN users u ON cn.professional_id = u.id
            LEFT JOIN appointments a ON cn.appointment_id = a.id
            WHERE cn.patient_id = ?
            ORDER BY cn.created_at DESC
        ", [$patientId]);

        // Get treatment plans
        $treatmentPlans = $this->db->select("
            SELECT tp.*,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM treatment_plans tp
            JOIN users u ON tp.created_by_user_id = u.id
            WHERE tp.patient_id = ?
            ORDER BY tp.created_at DESC
        ", [$patientId]);

        // Get odontogram
        $odontogram = $this->db->select(
            "SELECT * FROM patient_odontogram WHERE patient_id = ? ORDER BY tooth_number",
            [$patientId]
        );

        // Get odontogram history
        $odontogramHistory = $this->db->select("
            SELECT oh.*,
                   CONCAT(u.first_name, ' ', u.last_name) as changed_by_name,
                   a.scheduled_date
            FROM odontogram_history oh
            JOIN users u ON oh.changed_by_user_id = u.id
            LEFT JOIN appointments a ON oh.appointment_id = a.id
            WHERE oh.patient_id = ?
            ORDER BY oh.changed_at DESC
            LIMIT 50
        ", [$patientId]);

        return Response::view('clinical.history', [
            'title' => 'Historial Clínico - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'clinicalRecord' => $clinicalRecord,
            'clinicalNotes' => $clinicalNotes,
            'treatmentPlans' => $treatmentPlans,
            'odontogram' => $odontogram,
            'odontogramHistory' => $odontogramHistory,
        ]);
    }
}
