<?php
$type = $type ?? [];
$categories = $categories ?? [];
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
            <input type="text" name="code" value="<?= e($type['code'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nombre *</label>
            <input type="text" name="name" value="<?= e($type['name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Categoría</label>
            <select name="category_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">Sin categoría</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e((string) $category['id']) ?>" <?= (int) ($type['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Duración (min)</label>
            <input type="number" name="default_duration_minutes" value="<?= e((string) ($type['default_duration_minutes'] ?? 30)) ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Precio</label>
            <input type="number" step="0.01" name="price_default" value="<?= e((string) ($type['price_default'] ?? 0)) ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">IVA %</label>
            <input type="number" step="0.01" name="tax_percentage" value="<?= e((string) ($type['tax_percentage'] ?? 15)) ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Color</label>
            <input type="color" name="color_hex" value="<?= e($type['color_hex'] ?? '#1E4D3A') ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-600 mb-1">Descripción</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($type['description'] ?? '') ?></textarea>
        </div>
        <div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="requires_consent" value="1" <?= !empty($type['requires_consent']) ? 'checked' : '' ?>>
                Requiere consentimiento
            </label>
        </div>
        <div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="applies_to_teeth" value="1" <?= !empty($type['applies_to_teeth']) ? 'checked' : '' ?>>
                Aplica a piezas dentales
            </label>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Máximo piezas por sesión</label>
            <input type="number" name="max_teeth_per_session" value="<?= e((string) ($type['max_teeth_per_session'] ?? '')) ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" <?= !isset($type['is_active']) || (int) $type['is_active'] === 1 ? 'checked' : '' ?>>
                Activo
            </label>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/config/appointment-types" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>
