<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Odontogram Controller
 * =========================================================================
 * Controller for interactive odontogram management
 */

namespace App\Modules\ClinicalCare\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class OdontogramController
{
    private Database $db;

    // FDI tooth notation - permanent teeth
    private const PERMANENT_TEETH = [
        // Upper right (1st quadrant)
        '18', '17', '16', '15', '14', '13', '12', '11',
        // Upper left (2nd quadrant)
        '21', '22', '23', '24', '25', '26', '27', '28',
        // Lower left (3rd quadrant)
        '38', '37', '36', '35', '34', '33', '32', '31',
        // Lower right (4th quadrant)
        '41', '42', '43', '44', '45', '46', '47', '48'
    ];

    // FDI tooth notation - deciduous teeth
    private const DECIDUOUS_TEETH = [
        // Upper right (5th quadrant)
        '55', '54', '53', '52', '51',
        // Upper left (6th quadrant)
        '61', '62', '63', '64', '65',
        // Lower left (7th quadrant)
        '75', '74', '73', '72', '71',
        // Lower right (8th quadrant)
        '81', '82', '83', '84', '85'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get patient odontogram data (API)
     */
    public function show(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $organizationId = (int) session('organization_id', 0);

        // Verify patient belongs to organization
        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        }

        // Get odontogram data
        $teeth = $this->db->select(
            "SELECT * FROM patient_odontogram WHERE patient_id = ?",
            [$patientId]
        );

        // Index by tooth number
        $teethData = [];
        foreach ($teeth as $tooth) {
            $teethData[$tooth['tooth_number']] = [
                'id' => $tooth['id'],
                'status' => $tooth['tooth_status'],
                'surfaces' => json_decode($tooth['surfaces'] ?? '{}', true),
                'mobility' => $tooth['mobility'],
                'periodontal_status' => $tooth['periodontal_status'],
                'pocket_depth' => json_decode($tooth['pocket_depth'] ?? '{}', true),
                'notes' => $tooth['notes'],
            ];
        }

        return Response::json([
            'success' => true,
            'data' => [
                'patient_id' => $patientId,
                'permanent_teeth' => self::PERMANENT_TEETH,
                'deciduous_teeth' => self::DECIDUOUS_TEETH,
                'teeth' => $teethData
            ]
        ]);
    }

    /**
     * Update a single tooth
     */
    public function updateTooth(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $toothNumber = $request->param('toothNumber');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);
        $appointmentId = $request->input('appointment_id') ? (int) $request->input('appointment_id') : null;

        // Verify patient
        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        }

        // Validate tooth number
        if (!in_array($toothNumber, array_merge(self::PERMANENT_TEETH, self::DECIDUOUS_TEETH))) {
            return Response::json(['success' => false, 'message' => 'Número de pieza inválido'], 400);
        }

        // Get current tooth data for history
        $currentTooth = $this->db->selectOne(
            "SELECT * FROM patient_odontogram WHERE patient_id = ? AND tooth_number = ?",
            [$patientId, $toothNumber]
        );

        $data = [
            'patient_id' => $patientId,
            'tooth_number' => $toothNumber,
            'tooth_type' => in_array($toothNumber, self::DECIDUOUS_TEETH) ? 'deciduous' : 'permanent',
            'tooth_status' => $request->input('status', 'healthy'),
            'surfaces' => $request->input('surfaces') ? json_encode($request->input('surfaces')) : null,
            'mobility' => $request->input('mobility', '0'),
            'periodontal_status' => $request->input('periodontal_status', 'healthy'),
            'pocket_depth' => $request->input('pocket_depth') ? json_encode($request->input('pocket_depth')) : null,
            'gingival_recession' => $request->input('gingival_recession') ? json_encode($request->input('gingival_recession')) : null,
            'notes' => $request->input('notes'),
            'updated_by_user_id' => $userId,
        ];

        if ($currentTooth) {
            // Update existing
            $this->db->update('patient_odontogram', $data, 'id = ?', [$currentTooth['id']]);

            // Record history if status changed
            if ($currentTooth['tooth_status'] !== $data['tooth_status'] ||
                $currentTooth['surfaces'] !== $data['surfaces']) {
                $this->db->insert('odontogram_history', [
                    'patient_id' => $patientId,
                    'tooth_number' => $toothNumber,
                    'appointment_id' => $appointmentId,
                    'previous_status' => $currentTooth['tooth_status'],
                    'new_status' => $data['tooth_status'],
                    'previous_surfaces' => $currentTooth['surfaces'],
                    'new_surfaces' => $data['surfaces'],
                    'procedure_description' => $request->input('procedure_description'),
                    'changed_by_user_id' => $userId,
                ]);
            }
        } else {
            // Insert new
            $this->db->insert('patient_odontogram', $data);

            // Record history for new tooth
            $this->db->insert('odontogram_history', [
                'patient_id' => $patientId,
                'tooth_number' => $toothNumber,
                'appointment_id' => $appointmentId,
                'previous_status' => null,
                'new_status' => $data['tooth_status'],
                'previous_surfaces' => null,
                'new_surfaces' => $data['surfaces'],
                'procedure_description' => $request->input('procedure_description'),
                'changed_by_user_id' => $userId,
            ]);
        }

        return Response::json([
            'success' => true,
            'message' => 'Pieza dental actualizada'
        ]);
    }

    /**
     * Get tooth history
     */
    public function toothHistory(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $toothNumber = $request->param('toothNumber');
        $organizationId = (int) session('organization_id', 0);

        // Verify patient
        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        }

        $history = $this->db->select("
            SELECT oh.*,
                   CONCAT(u.first_name, ' ', u.last_name) as changed_by_name,
                   a.scheduled_date as appointment_date
            FROM odontogram_history oh
            JOIN users u ON oh.changed_by_user_id = u.id
            LEFT JOIN appointments a ON oh.appointment_id = a.id
            WHERE oh.patient_id = ? AND oh.tooth_number = ?
            ORDER BY oh.changed_at DESC
        ", [$patientId, $toothNumber]);

        return Response::json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Bulk update teeth (for multiple selections)
     */
    public function bulkUpdate(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);
        $teeth = $request->input('teeth', []);
        $status = $request->input('status');
        $appointmentId = $request->input('appointment_id') ? (int) $request->input('appointment_id') : null;

        // Verify patient
        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        }

        if (empty($teeth) || !$status) {
            return Response::json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }

        $updated = 0;
        foreach ($teeth as $toothNumber) {
            if (!in_array($toothNumber, array_merge(self::PERMANENT_TEETH, self::DECIDUOUS_TEETH))) {
                continue;
            }

            $currentTooth = $this->db->selectOne(
                "SELECT * FROM patient_odontogram WHERE patient_id = ? AND tooth_number = ?",
                [$patientId, $toothNumber]
            );

            $data = [
                'patient_id' => $patientId,
                'tooth_number' => $toothNumber,
                'tooth_type' => in_array($toothNumber, self::DECIDUOUS_TEETH) ? 'deciduous' : 'permanent',
                'tooth_status' => $status,
                'updated_by_user_id' => $userId,
            ];

            if ($currentTooth) {
                $this->db->update('patient_odontogram', ['tooth_status' => $status, 'updated_by_user_id' => $userId], 'id = ?', [$currentTooth['id']]);

                if ($currentTooth['tooth_status'] !== $status) {
                    $this->db->insert('odontogram_history', [
                        'patient_id' => $patientId,
                        'tooth_number' => $toothNumber,
                        'appointment_id' => $appointmentId,
                        'previous_status' => $currentTooth['tooth_status'],
                        'new_status' => $status,
                        'procedure_description' => $request->input('procedure_description'),
                        'changed_by_user_id' => $userId,
                    ]);
                }
            } else {
                $this->db->insert('patient_odontogram', $data);
                $this->db->insert('odontogram_history', [
                    'patient_id' => $patientId,
                    'tooth_number' => $toothNumber,
                    'appointment_id' => $appointmentId,
                    'previous_status' => null,
                    'new_status' => $status,
                    'procedure_description' => $request->input('procedure_description'),
                    'changed_by_user_id' => $userId,
                ]);
            }
            $updated++;
        }

        return Response::json([
            'success' => true,
            'message' => "{$updated} piezas actualizadas"
        ]);
    }
}
