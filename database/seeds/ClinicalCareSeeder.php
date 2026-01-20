<?php

namespace Database\Seeds;

use App\Core\Database;

class ClinicalCareSeeder
{
    public function run(Database $db): void
    {
        $organizationId = $this->resolveOrganizationId($db);

        $categories = [
            ['code' => 'PREV', 'name' => 'Prevención', 'description' => 'Profilaxis, sellantes, educación', 'color_hex' => '#1E4D3A'],
            ['code' => 'REST', 'name' => 'Restaurativa', 'description' => 'Resinas, amalgamas, incrustaciones', 'color_hex' => '#2F6B4F'],
            ['code' => 'ENDO', 'name' => 'Endodoncia', 'description' => 'Tratamientos de conducto', 'color_hex' => '#3B7A57'],
            ['code' => 'PERIO', 'name' => 'Periodoncia', 'description' => 'Curetajes, mantenimientos', 'color_hex' => '#4A8A63'],
            ['code' => 'CIR', 'name' => 'Cirugía', 'description' => 'Extracciones, cirugías', 'color_hex' => '#5C9A6E'],
            ['code' => 'PROT', 'name' => 'Prótesis', 'description' => 'Prótesis fija y removible', 'color_hex' => '#6DAB7A'],
            ['code' => 'ORTHO', 'name' => 'Ortodoncia', 'description' => 'Brackets, alineadores', 'color_hex' => '#7EBB86'],
            ['code' => 'DIAG', 'name' => 'Diagnóstico', 'description' => 'Radiografías, evaluación', 'color_hex' => '#8FCC93'],
        ];

        foreach ($categories as $category) {
            $existing = $db->selectOne(
                'SELECT id FROM dental_service_categories WHERE organization_id = ? AND code = ?',
                [$organizationId, $category['code']]
            );
            if ($existing) {
                continue;
            }
            $db->insert('dental_service_categories', [
                'organization_id' => $organizationId,
                'code' => $category['code'],
                'name' => $category['name'],
                'description' => $category['description'],
                'color_hex' => $category['color_hex'],
                'is_active' => 1,
            ]);
        }

        $materials = [
            ['code' => 'ANEST', 'name' => 'Anestesia', 'category' => 'quirurgico', 'unit' => 'unidad', 'unit_cost' => 1.50],
            ['code' => 'RESINA-A2', 'name' => 'Resina A2', 'category' => 'restaurativo', 'unit' => 'unidad', 'unit_cost' => 8.50],
            ['code' => 'RESINA-A3', 'name' => 'Resina A3', 'category' => 'restaurativo', 'unit' => 'unidad', 'unit_cost' => 8.50],
            ['code' => 'GUANTES', 'name' => 'Guantes', 'category' => 'consumible', 'unit' => 'par', 'unit_cost' => 0.30],
            ['code' => 'MASC', 'name' => 'Mascarilla', 'category' => 'consumible', 'unit' => 'unidad', 'unit_cost' => 0.20],
            ['code' => 'AGUJA', 'name' => 'Aguja', 'category' => 'consumible', 'unit' => 'unidad', 'unit_cost' => 0.10],
            ['code' => 'LIMA', 'name' => 'Lima endodóntica', 'category' => 'endodontico', 'unit' => 'unidad', 'unit_cost' => 0.80],
            ['code' => 'CEMENTO', 'name' => 'Cemento dental', 'category' => 'restaurativo', 'unit' => 'unidad', 'unit_cost' => 3.50],
        ];

        foreach ($materials as $material) {
            $existing = $db->selectOne(
                'SELECT id FROM dental_materials WHERE organization_id = ? AND code = ?',
                [$organizationId, $material['code']]
            );
            if ($existing) {
                continue;
            }
            $db->insert('dental_materials', [
                'organization_id' => $organizationId,
                'code' => $material['code'],
                'name' => $material['name'],
                'category' => $material['category'],
                'unit' => $material['unit'],
                'unit_cost' => $material['unit_cost'],
                'is_active' => 1,
            ]);
        }
    }

    private function resolveOrganizationId(Database $db): int
    {
        $row = $db->selectOne('SELECT id FROM organizations ORDER BY id ASC LIMIT 1');
        return (int) ($row['id'] ?? 1);
    }
}
