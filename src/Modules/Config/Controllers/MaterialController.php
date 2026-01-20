<?php

namespace App\Modules\Config\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class MaterialController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $materials = $this->db->select(
            'SELECT * FROM dental_materials WHERE organization_id = ? ORDER BY name',
            [$organizationId]
        );

        return Response::view('config.materials.index', [
            'title' => 'Materiales',
            'materials' => $materials,
        ]);
    }

    public function create(Request $request): Response
    {
        return Response::view('config.materials.create', [
            'title' => 'Nuevo material',
            'material' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $this->db->insert('dental_materials', [
            'organization_id' => $organizationId,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'unit' => $request->input('unit', 'unidad'),
            'unit_cost' => (float) $request->input('unit_cost', 0),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ]);

        session()->setFlash('success', 'Material creado.');
        return Response::redirect('/config/materials');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $material = $this->db->selectOne('SELECT * FROM dental_materials WHERE id = ?', [$id]);

        if (!$material) {
            return Response::notFound('Material no encontrado');
        }

        return Response::view('config.materials.edit', [
            'title' => 'Editar material',
            'material' => $material,
        ]);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->db->update('dental_materials', [
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'unit' => $request->input('unit', 'unidad'),
            'unit_cost' => (float) $request->input('unit_cost', 0),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ], 'id = ?', [$id]);

        session()->setFlash('success', 'Material actualizado.');
        return Response::redirect('/config/materials');
    }
}
