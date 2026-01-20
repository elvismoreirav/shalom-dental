<?php
$material = $material ?? [];
$action = $action ?? '';
$method = $method ?? 'POST';
?>
<form action="<?= e($action) ?>" method="post" class="space-y-4">
    <?= csrf_field() ?>
    <?php if (strtoupper($method) !== 'POST'): ?>
        <input type="hidden" name="_method" value="<?= e($method) ?>">
    <?php endif; ?>
    <div class="bg-white shadow rounded-lg border border-gray-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <label class="block text-gray-600 mb-1">Código *</label>
            <input type="text" name="code" value="<?= e($material['code'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nombre *</label>
            <input type="text" name="name" value="<?= e($material['name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Categoría</label>
            <input type="text" name="category" value="<?= e($material['category'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Unidad</label>
            <input type="text" name="unit" value="<?= e($material['unit'] ?? 'unidad') ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Costo unitario</label>
            <input type="number" step="0.01" name="unit_cost" value="<?= e((string) ($material['unit_cost'] ?? 0)) ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-600 mb-1">Descripción</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($material['description'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" <?= !isset($material['is_active']) || (int) $material['is_active'] === 1 ? 'checked' : '' ?>>
                Activo
            </label>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/config/materials" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>
