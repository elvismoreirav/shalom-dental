<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Notification Template Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Notifications\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class TemplateController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $templates = $this->db->select(
            "SELECT id, code, name, channel, event_type, is_active, created_at
             FROM notification_templates" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY created_at DESC",
            $organizationId > 0 ? [$organizationId] : []
        );

        return Response::view('notifications.templates', [
            'title' => 'Plantillas de Notificacion',
            'templates' => $templates,
        ]);
    }

    public function create(Request $request): Response
    {
        return Response::view('notifications.template-form', [
            'title' => 'Nueva Plantilla',
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $data = [
            'organization_id' => $organizationId,
            'code' => trim((string) $request->input('code')),
            'name' => trim((string) $request->input('name')),
            'channel' => $request->input('channel'),
            'event_type' => trim((string) $request->input('event_type')),
            'subject_template' => trim((string) $request->input('subject_template')) ?: null,
            'body_template' => trim((string) $request->input('body_template')),
            'is_active' => (int) ($request->input('is_active') ? 1 : 0),
        ];

        if ($data['organization_id'] <= 0 || $data['code'] === '' || $data['name'] === '' || $data['channel'] === '' || $data['event_type'] === '' || $data['body_template'] === '') {
            session()->setFlash('error', 'Complete los campos requeridos.');
            return Response::redirect('/notifications/templates/create');
        }

        $this->db->insert('notification_templates', $data);
        session()->setFlash('success', 'Plantilla creada.');
        return Response::redirect('/notifications/templates');
    }

    public function edit(Request $request): Response
    {
        $templateId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $template = $this->db->selectOne(
            "SELECT * FROM notification_templates WHERE id = ?" .
            ($organizationId > 0 ? " AND organization_id = ?" : ""),
            $organizationId > 0 ? [$templateId, $organizationId] : [$templateId]
        );

        if (!$template) {
            return Response::notFound('Plantilla no encontrada');
        }

        return Response::view('notifications.template-form', [
            'title' => 'Editar Plantilla',
            'template' => $template,
        ]);
    }

    public function update(Request $request): Response
    {
        $templateId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $template = $this->db->selectOne(
            "SELECT id FROM notification_templates WHERE id = ?" .
            ($organizationId > 0 ? " AND organization_id = ?" : ""),
            $organizationId > 0 ? [$templateId, $organizationId] : [$templateId]
        );

        if (!$template) {
            return Response::notFound('Plantilla no encontrada');
        }

        $data = [
            'code' => trim((string) $request->input('code')),
            'name' => trim((string) $request->input('name')),
            'channel' => $request->input('channel'),
            'event_type' => trim((string) $request->input('event_type')),
            'subject_template' => trim((string) $request->input('subject_template')) ?: null,
            'body_template' => trim((string) $request->input('body_template')),
            'is_active' => (int) ($request->input('is_active') ? 1 : 0),
        ];

        if ($data['code'] === '' || $data['name'] === '' || $data['channel'] === '' || $data['event_type'] === '' || $data['body_template'] === '') {
            session()->setFlash('error', 'Complete los campos requeridos.');
            return Response::redirect('/notifications/templates/' . $templateId . '/edit');
        }

        $this->db->update('notification_templates', $data, 'id = ?', [$templateId]);
        session()->setFlash('success', 'Plantilla actualizada.');
        return Response::redirect('/notifications/templates');
    }

    public function delete(Request $request): Response
    {
        $templateId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);

        $template = $this->db->selectOne(
            "SELECT id FROM notification_templates WHERE id = ?" .
            ($organizationId > 0 ? " AND organization_id = ?" : ""),
            $organizationId > 0 ? [$templateId, $organizationId] : [$templateId]
        );

        if (!$template) {
            return Response::notFound('Plantilla no encontrada');
        }

        $this->db->delete('notification_templates', 'id = ?', [$templateId]);
        session()->setFlash('success', 'Plantilla eliminada.');
        return Response::redirect('/notifications/templates');
    }
}
