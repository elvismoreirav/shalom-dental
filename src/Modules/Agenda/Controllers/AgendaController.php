<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Agenda Controller
 * =========================================================================
 */

namespace App\Modules\Agenda\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class AgendaController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $locationId = (int) session('current_location_id', 0);
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $date = trim((string) $request->query('date', ''));

        $sort = $request->query('sort', 'time');
        $dir = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $sqlBase = "FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                JOIN appointment_types t ON t.id = a.appointment_type_id
                WHERE a.scheduled_date = ?";
        $params = [];

        if ($date !== '') {
            $params[] = $date;
        } else {
            $params[] = date('Y-m-d');
        }

        if ($locationId > 0) {
            $sqlBase .= " AND a.location_id = ?";
            $params[] = $locationId;
        }

        if ($query !== '') {
            $sqlBase .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR t.name LIKE ?)";
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like);
        }

        if ($status !== '') {
            $sqlBase .= " AND a.status = ?";
            $params[] = $status;
        }

        $count = $this->db->selectOne("SELECT COUNT(*) as total " . $sqlBase, $params);
        $total = (int) ($count['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT a.id, a.scheduled_date, a.scheduled_start_time, a.scheduled_end_time, a.status,
                       p.first_name, p.last_name,
                       t.name AS appointment_type_name
                " . $sqlBase;

        $orderBy = match ($sort) {
            'patient' => "p.last_name {$dir}, p.first_name {$dir}",
            'type' => "t.name {$dir}",
            'status' => "a.status {$dir}",
            default => "a.scheduled_start_time {$dir}",
        };

        $sql .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";

        $appointments = $this->db->select($sql, $params);

        return Response::view('agenda.index', [
            'title' => 'Agenda',
            'appointments' => $appointments,
            'query' => $query,
            'status' => $status,
            'date' => $date,
            'sort' => $sort,
            'dir' => strtolower($dir) === 'desc' ? 'desc' : 'asc',
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }
}
