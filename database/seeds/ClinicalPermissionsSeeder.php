<?php

namespace Database\Seeds;

use App\Core\Database;

class ClinicalPermissionsSeeder
{
    private array $permissions = [
        ['module' => 'clinical', 'resource' => 'records', 'action' => 'view', 'description' => 'Ver historial clínico'],
        ['module' => 'clinical', 'resource' => 'records', 'action' => 'edit', 'description' => 'Editar historial clínico'],
        ['module' => 'clinical', 'resource' => 'odontogram', 'action' => 'view', 'description' => 'Ver odontograma'],
        ['module' => 'clinical', 'resource' => 'odontogram', 'action' => 'edit', 'description' => 'Editar odontograma'],
        ['module' => 'clinical', 'resource' => 'notes', 'action' => 'create', 'description' => 'Crear notas clínicas'],
        ['module' => 'clinical', 'resource' => 'notes', 'action' => 'view', 'description' => 'Ver notas clínicas'],
        ['module' => 'clinical', 'resource' => 'notes', 'action' => 'sign', 'description' => 'Firmar notas clínicas'],
        ['module' => 'clinical', 'resource' => 'notes', 'action' => 'amend', 'description' => 'Enmendar notas clínicas'],
        ['module' => 'clinical', 'resource' => 'treatment_plans', 'action' => 'view', 'description' => 'Ver planes de tratamiento'],
        ['module' => 'clinical', 'resource' => 'treatment_plans', 'action' => 'create', 'description' => 'Crear planes de tratamiento'],
        ['module' => 'clinical', 'resource' => 'treatment_plans', 'action' => 'edit', 'description' => 'Editar planes de tratamiento'],
        ['module' => 'clinical', 'resource' => 'treatment_plans', 'action' => 'delete', 'description' => 'Eliminar planes de tratamiento'],
        ['module' => 'clinical', 'resource' => 'procedures', 'action' => 'create', 'description' => 'Registrar procedimientos'],
        ['module' => 'clinical', 'resource' => 'procedures', 'action' => 'view', 'description' => 'Ver procedimientos'],
        ['module' => 'clinical', 'resource' => 'procedures', 'action' => 'invoice', 'description' => 'Facturar procedimientos'],
        ['module' => 'clinical', 'resource' => 'consents', 'action' => 'view', 'description' => 'Ver consentimientos'],
        ['module' => 'clinical', 'resource' => 'consents', 'action' => 'create', 'description' => 'Crear consentimientos'],
        ['module' => 'clinical', 'resource' => 'consents', 'action' => 'sign', 'description' => 'Firmar consentimientos'],
        ['module' => 'config', 'resource' => 'service_categories', 'action' => 'view', 'description' => 'Ver categorías de servicios'],
        ['module' => 'config', 'resource' => 'service_categories', 'action' => 'manage', 'description' => 'Gestionar categorías de servicios'],
        ['module' => 'config', 'resource' => 'materials', 'action' => 'view', 'description' => 'Ver materiales'],
        ['module' => 'config', 'resource' => 'materials', 'action' => 'manage', 'description' => 'Gestionar materiales'],
    ];

    public function run(Database $db): void
    {
        foreach ($this->permissions as $permission) {
            $existing = $db->selectOne(
                'SELECT id FROM permissions WHERE module = ? AND resource = ? AND action = ?',
                [$permission['module'], $permission['resource'], $permission['action']]
            );
            if ($existing) {
                continue;
            }
            $db->insert('permissions', $permission);
        }

        $roleIds = [
            'super_admin' => $this->getRoleId($db, 'super_admin'),
            'admin' => $this->getRoleId($db, 'admin'),
            'odontologo' => $this->getRoleId($db, 'odontologo'),
        ];

        foreach ($roleIds as $code => $roleId) {
            if (!$roleId) {
                continue;
            }
            foreach ($this->permissions as $permission) {
                if ($code === 'odontologo') {
                    $isClinical = $permission['module'] === 'clinical';
                    $isConfigView = $permission['module'] === 'config'
                        && in_array($permission['resource'], ['service_categories', 'materials'], true)
                        && $permission['action'] === 'view';
                    if (!$isClinical && !$isConfigView) {
                        continue;
                    }
                }
                $permissionId = $this->getPermissionId($db, $permission);
                if (!$permissionId) {
                    continue;
                }
                $existing = $db->selectOne(
                    'SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                    [$roleId, $permissionId]
                );
                if ($existing) {
                    continue;
                }
                $db->insert('role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    private function getRoleId(Database $db, string $code): ?int
    {
        $row = $db->selectOne('SELECT id FROM roles WHERE code = ?', [$code]);
        return $row ? (int) $row['id'] : null;
    }

    private function getPermissionId(Database $db, array $permission): ?int
    {
        $row = $db->selectOne(
            'SELECT id FROM permissions WHERE module = ? AND resource = ? AND action = ?',
            [$permission['module'], $permission['resource'], $permission['action']]
        );
        return $row ? (int) $row['id'] : null;
    }
}
