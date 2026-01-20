<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Categorías de servicios</h2>
        <p class="text-sm text-gray-500">Catálogo de categorías clínicas.</p>
    </div>
    <a href="/config/service-categories/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nueva categoría</a>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Código</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nombre</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Activo</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="px-4 py-3 text-gray-600"><?= e($category['code'] ?? '') ?></td>
                        <td class="px-4 py-3 text-gray-900"><?= e($category['name'] ?? '') ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= (int) ($category['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                        <td class="px-4 py-3 text-right">
                            <a href="/config/service-categories/<?= e((string) $category['id']) ?>/edit" class="text-shalom-primary text-sm">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin categorías.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>
