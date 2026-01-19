<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Detalle de cita</h2>
        <p class="text-sm text-gray-500">Informacion general y auditoria.</p>
    </div>
    <div class="space-x-2">
        <a href="/agenda" class="px-4 py-2 rounded-lg border text-sm">Volver</a>
        <a href="/agenda/<?= e((string) ($appointment['id'] ?? 0)) ?>/edit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Editar</a>
    </div>
</div>

<div class="mb-4 flex flex-wrap gap-2">
    <?php if (can('agenda.appointments.cancel_all') || can('agenda.appointments.cancel_own')): ?>
        <form action="/agenda/<?= e((string) ($appointment['id'] ?? 0)) ?>/cancel" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="reason" value="Cancelado desde detalle">
            <button type="submit" class="px-3 py-2 rounded-lg border text-sm text-red-600">Cancelar</button>
        </form>
    <?php endif; ?>
    <?php if (can('agenda.appointments.no_show')): ?>
        <form action="/agenda/<?= e((string) ($appointment['id'] ?? 0)) ?>/no-show" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="reason" value="No show registrado">
            <button type="submit" class="px-3 py-2 rounded-lg border text-sm">No-show</button>
        </form>
    <?php endif; ?>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-gray-500">Paciente</div>
            <div class="font-medium text-gray-900"><?= e(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '')) ?></div>
        </div>
        <div>
            <div class="text-gray-500">Profesional</div>
            <div class="font-medium text-gray-900"><?= e(($appointment['professional_first_name'] ?? '') . ' ' . ($appointment['professional_last_name'] ?? '')) ?></div>
        </div>
        <div>
            <div class="text-gray-500">Tipo</div>
            <div class="font-medium text-gray-900"><?= e($appointment['appointment_type_name'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Estado</div>
            <div class="font-medium text-gray-900"><?= e($appointment['status'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Fecha</div>
            <div class="font-medium text-gray-900"><?= e($appointment['scheduled_date'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Horario</div>
            <div class="font-medium text-gray-900"><?= e(($appointment['scheduled_start_time'] ?? '-') . ' - ' . ($appointment['scheduled_end_time'] ?? '-')) ?></div>
        </div>
        <div class="md:col-span-2">
            <div class="text-gray-500">Notas</div>
            <div class="font-medium text-gray-900"><?= e($appointment['notes'] ?? '-') ?></div>
        </div>
    </div>
</div>

<div class="mt-6 bg-white shadow rounded-lg border border-gray-100 p-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Auditoria</h3>
    <div class="space-y-2 text-sm">
        <?php if (!empty($audit)): ?>
            <?php foreach ($audit as $entry): ?>
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-medium text-gray-900"><?= e($entry['action'] ?? '-') ?></div>
                        <div class="text-gray-500 text-xs"><?= e($entry['created_at'] ?? '-') ?></div>
                    </div>
                    <div class="text-gray-500 text-xs max-w-md">
                        <?= e(json_encode($entry['new_values'] ?? [], JSON_UNESCAPED_UNICODE)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-gray-500">Sin registros de auditoria.</div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
