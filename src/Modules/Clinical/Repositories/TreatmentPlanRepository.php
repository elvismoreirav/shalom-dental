<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class TreatmentPlanRepository
{
    public function __construct(private Database $db)
    {
    }

    public function listByPatient(int $patientId): array
    {
        return $this->db->select(
            'SELECT * FROM treatment_plans WHERE patient_id = ? ORDER BY created_at DESC',
            [$patientId]
        );
    }

    public function find(int $planId): ?array
    {
        return $this->db->selectOne('SELECT * FROM treatment_plans WHERE id = ?', [$planId]);
    }

    public function create(array $data): int
    {
        return $this->db->insert('treatment_plans', $data);
    }

    public function update(int $planId, array $data): void
    {
        $this->db->update('treatment_plans', $data, 'id = ?', [$planId]);
    }

    public function delete(int $planId): void
    {
        $this->db->delete('treatment_plans', 'id = ?', [$planId]);
    }
}
