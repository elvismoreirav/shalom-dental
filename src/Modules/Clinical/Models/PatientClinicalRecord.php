<?php

namespace App\Modules\Clinical\Models;

class PatientClinicalRecord
{
    public function __construct(
        public ?int $id,
        public int $patientId,
        public ?array $medicalHistory,
        public ?string $surgicalHistory,
        public ?string $familyHistory,
        public ?array $habits,
        public ?string $dentalHistory,
        public ?string $lastDentalVisit,
        public ?string $oralHygieneFrequency,
        public ?array $extraoralExam,
        public ?array $intraoralExam,
        public ?string $occlusionType,
        public ?string $occlusionNotes,
        public ?string $generalDiagnosis
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'] ?? null,
            (int) ($row['patient_id'] ?? 0),
            self::decodeJson($row['medical_history'] ?? null),
            $row['surgical_history'] ?? null,
            $row['family_history'] ?? null,
            self::decodeJson($row['habits'] ?? null),
            $row['dental_history'] ?? null,
            $row['last_dental_visit'] ?? null,
            $row['oral_hygiene_frequency'] ?? null,
            self::decodeJson($row['extraoral_exam'] ?? null),
            self::decodeJson($row['intraoral_exam'] ?? null),
            $row['occlusion_type'] ?? null,
            $row['occlusion_notes'] ?? null,
            $row['general_diagnosis'] ?? null
        );
    }

    private static function decodeJson(mixed $value): ?array
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }
}
