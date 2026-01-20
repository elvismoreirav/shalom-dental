<?php

namespace App\Modules\Config\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class AppointmentTypeController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $types = $this->db->select(
            'SELECT at.*, c.name as category_name
             FROM appointment_types at
             LEFT JOIN dental_service_categories c ON c.id = at.category_id
             WHERE at.organization_id = ?
             ORDER BY at.name',
            [$organizationId]
        );
        $categories = $this->db->select(
            'SELECT id, name FROM dental_service_categories WHERE organization_id = ? ORDER BY name',
            [$organizationId]
        );

        return Response::view('config.appointment-types.index', [
            'title' => 'Tipos de cita',
            'types' => $types,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $categories = $this->db->select(
            'SELECT id, name FROM dental_service_categories WHERE organization_id = ? ORDER BY name',
            [$organizationId]
        );
        return Response::view('config.appointment-types.create', [
            'title' => 'Nuevo tipo de cita',
            'type' => [],
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $this->db->insert('appointment_types', [
            'organization_id' => $organizationId,
            'category_id' => (int) $request->input('category_id') ?: null,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'default_duration_minutes' => (int) $request->input('default_duration_minutes', 30),
            'buffer_before_minutes' => (int) $request->input('buffer_before_minutes', 0),
            'buffer_after_minutes' => (int) $request->input('buffer_after_minutes', 5),
            'color_hex' => $request->input('color_hex', '#1E4D3A'),
            'price_default' => (float) $request->input('price_default', 0),
            'tax_percentage' => (float) $request->input('tax_percentage', 15),
            'requires_consent' => $request->input('requires_consent') ? 1 : 0,
            'applies_to_teeth' => $request->input('applies_to_teeth') ? 1 : 0,
            'max_teeth_per_session' => (int) $request->input('max_teeth_per_session') ?: null,
            'is_active' => $request->input('is_active') ? 1 : 0,
        ]);

        session()->setFlash('success', 'Tipo de cita creado.');
        return Response::redirect('/config/appointment-types');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $type = $this->db->selectOne('SELECT * FROM appointment_types WHERE id = ?', [$id]);
        if (!$type) {
            return Response::notFound('Tipo no encontrado');
        }
        $organizationId = (int) session('organization_id', 0);
        $categories = $this->db->select(
            'SELECT id, name FROM dental_service_categories WHERE organization_id = ? ORDER BY name',
            [$organizationId]
        );
        return Response::view('config.appointment-types.edit', [
            'title' => 'Editar tipo de cita',
            'type' => $type,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->db->update('appointment_types', [
            'category_id' => (int) $request->input('category_id') ?: null,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'default_duration_minutes' => (int) $request->input('default_duration_minutes', 30),
            'buffer_before_minutes' => (int) $request->input('buffer_before_minutes', 0),
            'buffer_after_minutes' => (int) $request->input('buffer_after_minutes', 5),
            'color_hex' => $request->input('color_hex', '#1E4D3A'),
            'price_default' => (float) $request->input('price_default', 0),
            'tax_percentage' => (float) $request->input('tax_percentage', 15),
            'requires_consent' => $request->input('requires_consent') ? 1 : 0,
            'applies_to_teeth' => $request->input('applies_to_teeth') ? 1 : 0,
            'max_teeth_per_session' => (int) $request->input('max_teeth_per_session') ?: null,
            'is_active' => $request->input('is_active') ? 1 : 0,
        ], 'id = ?', [$id]);

        session()->setFlash('success', 'Tipo de cita actualizado.');
        return Response::redirect('/config/appointment-types');
    }
}
