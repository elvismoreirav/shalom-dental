<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Patient Controller
 * =========================================================================
 */

namespace App\Modules\Patients\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class PatientController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $query = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $sort = $request->query('sort', 'created');
        $dir = strtolower((string) $request->query('dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($organizationId > 0) {
            $where[] = "organization_id = ?";
            $params[] = $organizationId;
        }

        if ($query !== '') {
            $where[] = "(first_name LIKE ? OR last_name LIKE ? OR id_number LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $count = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM patients" . $whereSql,
            $params
        );

        $total = (int) ($count['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $orderBy = match ($sort) {
            'name' => "last_name {$dir}, first_name {$dir}",
            'email' => "email {$dir}",
            'created' => "created_at {$dir}",
            default => "created_at {$dir}",
        };

        $sql = "SELECT id, first_name, last_name, email, phone, created_at
                FROM patients" . $whereSql .
                " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";

        $patients = $this->db->select($sql, $params);

        return Response::view('patients.index', [
            'title' => 'Pacientes',
            'patients' => $patients,
            'query' => $query,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
            'dir' => strtolower($dir) === 'desc' ? 'desc' : 'asc',
        ]);
    }

    public function show(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $sql = "SELECT * FROM patients WHERE id = ?";
        $params = [$patientId];

        if ($organizationId > 0) {
            $sql .= " AND organization_id = ?";
            $params[] = $organizationId;
        }

        $patient = $this->db->selectOne($sql, $params);

        if (!$patient) {
            return Response::notFound('Paciente no encontrado');
        }

        $files = [];
        $canViewAll = can('patients.files.view_all');
        $canViewOwn = can('patients.files.view_own');
        $userId = (int) (user()['id'] ?? 0);

        if ($canViewAll || $canViewOwn) {
            $sql = "SELECT id, original_name, file_path, created_at, category, uploaded_by_user_id
                    FROM patient_files
                    WHERE patient_id = ? AND is_deleted = 0";
            $params = [$patientId];

            if (!$canViewAll && $canViewOwn) {
                $sql .= " AND uploaded_by_user_id = ?";
                $params[] = $userId;
            }

            $sql .= " ORDER BY created_at DESC";
            $files = $this->db->select($sql, $params);
        }

        return Response::view('patients.show', [
            'title' => 'Detalle de Paciente',
            'patient' => $patient,
            'files' => $files,
        ]);
    }

    public function create(Request $request): Response
    {
        return Response::view('patients.create', [
            'title' => 'Crear Paciente',
        ]);
    }

    public function edit(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $sql = "SELECT * FROM patients WHERE id = ?";
        $params = [$patientId];

        if ($organizationId > 0) {
            $sql .= " AND organization_id = ?";
            $params[] = $organizationId;
        }

        $patient = $this->db->selectOne($sql, $params);

        if (!$patient) {
            return Response::notFound('Paciente no encontrado');
        }

        return Response::view('patients.edit', [
            'title' => 'Editar Paciente',
            'patient' => $patient,
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $data = [
            'organization_id' => $organizationId,
            'id_type' => $request->input('id_type', 'cedula'),
            'id_number' => trim((string) $request->input('id_number')),
            'first_name' => trim((string) $request->input('first_name')),
            'last_name' => trim((string) $request->input('last_name')),
            'email' => trim((string) $request->input('email')) ?: null,
            'phone' => trim((string) $request->input('phone')),
            'birth_date' => $request->input('birth_date') ?: null,
            'gender' => $request->input('gender') ?: null,
            'address' => trim((string) $request->input('address')) ?: null,
            'city' => trim((string) $request->input('city')) ?: null,
            'province' => trim((string) $request->input('province')) ?: null,
            'notes' => trim((string) $request->input('notes')) ?: null,
            'created_by_user_id' => $userId ?: null,
        ];

        $errors = [];
        if ($data['organization_id'] <= 0) {
            $errors['organization_id'] = 'Organizacion invalida.';
        }
        if ($data['first_name'] === '') {
            $errors['first_name'] = 'Nombre es requerido.';
        }
        if ($data['last_name'] === '') {
            $errors['last_name'] = 'Apellido es requerido.';
        }
        if ($data['phone'] === '') {
            $errors['phone'] = 'Telefono es requerido.';
        }
        if ($data['id_number'] === '') {
            $errors['id_number'] = 'Numero ID es requerido.';
        }

        $exists = $this->db->selectOne(
            "SELECT id FROM patients WHERE id_number = ? AND organization_id = ?",
            [$data['id_number'], $data['organization_id']]
        );
        if ($exists) {
            $errors['id_number'] = 'Ya existe un paciente con ese numero de identificacion.';
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Complete los campos requeridos.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/patients/create');
        }

        $patientId = $this->db->insert('patients', $data);
        session()->setFlash('success', 'Paciente creado correctamente.');

        return Response::redirect('/patients/' . $patientId);
    }

    public function update(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $sql = "SELECT id FROM patients WHERE id = ?";
        $params = [$patientId];

        if ($organizationId > 0) {
            $sql .= " AND organization_id = ?";
            $params[] = $organizationId;
        }

        $patient = $this->db->selectOne($sql, $params);
        if (!$patient) {
            return Response::notFound('Paciente no encontrado');
        }

        $data = [
            'id_type' => $request->input('id_type', 'cedula'),
            'id_number' => trim((string) $request->input('id_number')),
            'first_name' => trim((string) $request->input('first_name')),
            'last_name' => trim((string) $request->input('last_name')),
            'email' => trim((string) $request->input('email')) ?: null,
            'phone' => trim((string) $request->input('phone')),
            'birth_date' => $request->input('birth_date') ?: null,
            'gender' => $request->input('gender') ?: null,
            'address' => trim((string) $request->input('address')) ?: null,
            'city' => trim((string) $request->input('city')) ?: null,
            'province' => trim((string) $request->input('province')) ?: null,
            'notes' => trim((string) $request->input('notes')) ?: null,
        ];

        $errors = [];
        if ($data['first_name'] === '') {
            $errors['first_name'] = 'Nombre es requerido.';
        }
        if ($data['last_name'] === '') {
            $errors['last_name'] = 'Apellido es requerido.';
        }
        if ($data['phone'] === '') {
            $errors['phone'] = 'Telefono es requerido.';
        }
        if ($data['id_number'] === '') {
            $errors['id_number'] = 'Numero ID es requerido.';
        }

        $exists = $this->db->selectOne(
            "SELECT id FROM patients WHERE id_number = ? AND organization_id = ? AND id != ?",
            [$data['id_number'], $organizationId, $patientId]
        );
        if ($exists) {
            $errors['id_number'] = 'Ya existe un paciente con ese numero de identificacion.';
        }

        if (!empty($errors)) {
            session()->setFlash('error', 'Complete los campos requeridos.');
            session()->setFlash('errors', $errors);
            return Response::redirect('/patients/' . $patientId . '/edit');
        }

        $this->db->update('patients', $data, 'id = ?', [$patientId]);
        session()->setFlash('success', 'Paciente actualizado correctamente.');

        return Response::redirect('/patients/' . $patientId);
    }
}
