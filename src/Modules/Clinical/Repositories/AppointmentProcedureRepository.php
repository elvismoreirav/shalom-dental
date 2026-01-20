<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class AppointmentProcedureRepository
{
    public function __construct(private Database $db)
    {
    }

    public function listByAppointment(int $appointmentId): array
    {
        return $this->db->select(
            'SELECT ap.*, at.name as service_name
             FROM appointment_procedures ap
             LEFT JOIN appointment_types at ON at.id = ap.appointment_type_id
             WHERE ap.appointment_id = ?
             ORDER BY ap.created_at DESC',
            [$appointmentId]
        );
    }

    public function insert(array $data): int
    {
        return $this->db->insert('appointment_procedures', $data);
    }

    public function delete(int $procedureId): void
    {
        $this->db->delete('appointment_procedures', 'id = ?', [$procedureId]);
    }
}
