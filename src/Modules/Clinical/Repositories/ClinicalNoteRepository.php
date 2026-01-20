<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class ClinicalNoteRepository
{
    public function __construct(private Database $db)
    {
    }

    public function findByAppointment(int $appointmentId): ?array
    {
        return $this->db->selectOne(
            'SELECT * FROM clinical_notes WHERE appointment_id = ?',
            [$appointmentId]
        );
    }

    public function upsert(int $appointmentId, array $data): int
    {
        $existing = $this->findByAppointment($appointmentId);
        if ($existing) {
            $this->db->update('clinical_notes', $data, 'appointment_id = ?', [$appointmentId]);
            return (int) $existing['id'];
        }

        return $this->db->insert('clinical_notes', $data);
    }
}
