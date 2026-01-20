<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class TreatmentPlanItemRepository
{
    public function __construct(private Database $db)
    {
    }

    public function listByPlan(int $planId): array
    {
        return $this->db->select(
            'SELECT tpi.*, at.name as service_name
             FROM treatment_plan_items tpi
             LEFT JOIN appointment_types at ON at.id = tpi.appointment_type_id
             WHERE tpi.treatment_plan_id = ?
             ORDER BY tpi.sequence_order ASC, tpi.id ASC',
            [$planId]
        );
    }

    public function replaceItems(int $planId, array $items): void
    {
        $this->db->delete('treatment_plan_items', 'treatment_plan_id = ?', [$planId]);
        $sequence = 1;
        foreach ($items as $item) {
            $appointmentTypeId = (int) ($item['appointment_type_id'] ?? 0);
            if ($appointmentTypeId <= 0) {
                continue;
            }
            $this->db->insert('treatment_plan_items', [
                'treatment_plan_id' => $planId,
                'appointment_type_id' => $appointmentTypeId,
                'sequence_order' => (int) ($item['sequence_order'] ?? $sequence++),
                'phase' => $this->normalizeText($item['phase'] ?? null),
                'tooth_number' => $this->normalizeText($item['tooth_number'] ?? null),
                'surfaces' => $this->normalizeText($item['surfaces'] ?? null),
                'description' => $this->normalizeText($item['description'] ?? null),
                'status' => $item['status'] ?? 'pending',
                'estimated_price' => (float) ($item['estimated_price'] ?? 0),
                'final_price' => (float) ($item['final_price'] ?? 0) ?: null,
                'scheduled_date' => $this->normalizeText($item['scheduled_date'] ?? null),
                'notes' => $this->normalizeText($item['notes'] ?? null),
            ]);
        }
    }

    private function normalizeText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }
}
