<?php

namespace App\Modules\Config\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class ServiceCategoryController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $categories = $this->db->select(
            'SELECT * FROM dental_service_categories WHERE organization_id = ? ORDER BY sort_order, name',
            [$organizationId]
        );

        return Response::view('config.service-categories.index', [
            'title' => 'Categorías de servicios',
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): Response
    {
        return Response::view('config.service-categories.create', [
            'title' => 'Nueva categoría',
            'category' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $this->db->insert('dental_service_categories', [
            'organization_id' => $organizationId,
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'color_hex' => $request->input('color_hex', '#1E4D3A'),
            'icon' => $request->input('icon'),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        session()->setFlash('success', 'Categoría creada.');
        return Response::redirect('/config/service-categories');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $category = $this->db->selectOne('SELECT * FROM dental_service_categories WHERE id = ?', [$id]);

        if (!$category) {
            return Response::notFound('Categoría no encontrada');
        }

        return Response::view('config.service-categories.edit', [
            'title' => 'Editar categoría',
            'category' => $category,
        ]);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->db->update('dental_service_categories', [
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'color_hex' => $request->input('color_hex', '#1E4D3A'),
            'icon' => $request->input('icon'),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'sort_order' => (int) $request->input('sort_order', 0),
        ], 'id = ?', [$id]);

        session()->setFlash('success', 'Categoría actualizada.');
        return Response::redirect('/config/service-categories');
    }
}
