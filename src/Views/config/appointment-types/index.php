<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Tipos de cita</h2>
        <p class="text-sm text-gray-500">Servicios parametrizados.</p>
    </div>
    <a href="/config/appointment-types/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nuevo tipo</a>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Código</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nombre</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Categoría</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Precio</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Activo</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (!empty($types)): ?>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td class="px-4 py-3 text-gray-600"><?= e($type['code'] ?? '') ?></td>
                        <td class="px-4 py-3 text-gray-900"><?= e($type['name'] ?? '') ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= e($type['category_name'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-gray-600">$<?= number_format((float) ($type['price_default'] ?? 0), 2) ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= (int) ($type['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                        <td class="px-4 py-3 text-right">
                            <a href="/config/appointment-types/<?= e((string) $type['id']) ?>/edit" class="text-shalom-primary text-sm">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin tipos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>
