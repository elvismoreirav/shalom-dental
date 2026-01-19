<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Plantillas de notificacion</h2>
        <p class="text-sm text-gray-500">Gestiona las plantillas activas.</p>
    </div>
    <?php if (can('notifications.templates.manage')): ?>
        <a href="/notifications/templates/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nueva plantilla</a>
    <?php endif; ?>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Codigo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Nombre</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Canal</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Evento</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($templates)): ?>
                    <?php foreach ($templates as $template): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700"><?= e($template['code']) ?></td>
                            <td class="px-4 py-3 text-gray-900"><?= e($template['name']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($template['channel']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($template['event_type']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= $template['is_active'] ? 'Activo' : 'Inactivo' ?></td>
                            <td class="px-4 py-3 text-right">
                                <?php if (can('notifications.templates.manage')): ?>
                                    <a href="/notifications/templates/<?= e((string) $template['id']) ?>/edit" class="text-shalom-primary hover:underline">Editar</a>
                                    <form action="/notifications/templates/<?= e((string) $template['id']) ?>" method="post" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="ml-3 text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin plantillas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->endSection(); ?>
