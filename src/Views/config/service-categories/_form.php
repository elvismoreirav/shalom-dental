<?php
$category = $category ?? [];
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
            <input type="text" name="code" value="<?= e($category['code'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nombre *</label>
            <input type="text" name="name" value="<?= e($category['name'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Color</label>
            <input type="color" name="color_hex" value="<?= e($category['color_hex'] ?? '#1E4D3A') ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Orden</label>
            <input type="number" name="sort_order" value="<?= e((string) ($category['sort_order'] ?? 0)) ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-600 mb-1">Descripción</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($category['description'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" <?= !isset($category['is_active']) || (int) $category['is_active'] === 1 ? 'checked' : '' ?>>
                Activo
            </label>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/config/service-categories" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>
