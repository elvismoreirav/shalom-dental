<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class PatientClinicalRecordRepository
{
    public function __construct(private Database $db)
    {
    }

    public function findByPatientId(int $patientId): ?array
    {
        return $this->db->selectOne(
            'SELECT * FROM patient_clinical_records WHERE patient_id = ?',
            [$patientId]
        );
    }

    public function upsert(int $patientId, array $data, int $userId): int
    {
        $payload = [
            'patient_id' => $patientId,
            'medical_history' => $this->normalizeJson($data['medical_history'] ?? null),
            'surgical_history' => $this->normalizeText($data['surgical_history'] ?? null),
            'family_history' => $this->normalizeText($data['family_history'] ?? null),
            'habits' => $this->normalizeJson($data['habits'] ?? null),
            'dental_history' => $this->normalizeText($data['dental_history'] ?? null),
            'last_dental_visit' => $this->normalizeDate($data['last_dental_visit'] ?? null),
            'oral_hygiene_frequency' => $this->normalizeText($data['oral_hygiene_frequency'] ?? null),
            'extraoral_exam' => $this->normalizeJson($data['extraoral_exam'] ?? null),
            'intraoral_exam' => $this->normalizeJson($data['intraoral_exam'] ?? null),
            'occlusion_type' => $this->normalizeText($data['occlusion_type'] ?? null),
            'occlusion_notes' => $this->normalizeText($data['occlusion_notes'] ?? null),
            'general_diagnosis' => $this->normalizeText($data['general_diagnosis'] ?? null),
            'updated_by_user_id' => $userId ?: null,
        ];

        $existing = $this->findByPatientId($patientId);
        if ($existing) {
            $this->db->update('patient_clinical_records', $payload, 'patient_id = ?', [$patientId]);
            return (int) ($existing['id'] ?? 0);
        }

        $payload['created_by_user_id'] = $userId ?: null;
        return $this->db->insert('patient_clinical_records', $payload);
    }

    private function normalizeJson(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
            return json_encode(['notes' => $value], JSON_UNESCAPED_UNICODE);
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return null;
    }

    private function normalizeText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }
}
