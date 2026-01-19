<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Patient Export Controller
 * =========================================================================
 */

namespace App\Modules\Patients\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class PatientExportController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function export(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);

        $sql = "SELECT id, first_name, last_name, id_type, id_number, email, phone, created_at
                FROM patients";
        $params = [];

        if ($organizationId > 0) {
            $sql .= " WHERE organization_id = ?";
            $params[] = $organizationId;
        }

        $sql .= " ORDER BY created_at DESC";

        $rows = $this->db->select($sql, $params);

        $filename = 'patients_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['ID','Nombre','Apellido','Tipo ID','Numero ID','Email','Telefono','Creado']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['id_type'],
                $row['id_number'],
                $row['email'],
                $row['phone'],
                $row['created_at'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return (new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]));
    }
}
