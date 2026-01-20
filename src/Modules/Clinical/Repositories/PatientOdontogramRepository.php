<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class PatientOdontogramRepository
{
    public function __construct(private Database $db)
    {
    }

    public function getByPatientId(int $patientId): array
    {
        return $this->db->select(
            'SELECT * FROM patient_odontogram WHERE patient_id = ?',
            [$patientId]
        );
    }

    public function getHistoryByPatientId(int $patientId, int $limit = 50): array
    {
        return $this->db->select(
            'SELECT h.*, u.first_name, u.last_name
             FROM odontogram_history h
             LEFT JOIN users u ON u.id = h.changed_by_user_id
             WHERE h.patient_id = ?
             ORDER BY h.changed_at DESC
             LIMIT ' . $limit,
            [$patientId]
        );
    }

    public function upsertTooth(int $patientId, string $toothNumber, array $data, int $userId): array
    {
        $existing = $this->db->selectOne(
            'SELECT * FROM patient_odontogram WHERE patient_id = ? AND tooth_number = ?',
            [$patientId, $toothNumber]
        );

        $payload = [
            'patient_id' => $patientId,
            'tooth_number' => $toothNumber,
            'tooth_type' => $data['tooth_type'] ?? 'permanent',
            'tooth_status' => $data['tooth_status'] ?? 'healthy',
            'surfaces' => $data['surfaces'] ?? null,
            'mobility' => $data['mobility'] ?? '0',
            'periodontal_status' => $data['periodontal_status'] ?? 'healthy',
            'pocket_depth' => $data['pocket_depth'] ?? null,
            'gingival_recession' => $data['gingival_recession'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_by_user_id' => $userId ?: null,
        ];

        if ($existing) {
            $this->db->update('patient_odontogram', $payload, 'id = ?', [$existing['id']]);
            $updated = $this->db->selectOne('SELECT * FROM patient_odontogram WHERE id = ?', [$existing['id']]);
            return ['previous' => $existing, 'current' => $updated];
        }

        $id = $this->db->insert('patient_odontogram', $payload);
        $created = $this->db->selectOne('SELECT * FROM patient_odontogram WHERE id = ?', [$id]);
        return ['previous' => null, 'current' => $created];
    }

    public function addHistory(array $data): void
    {
        $this->db->insert('odontogram_history', $data);
    }
}
