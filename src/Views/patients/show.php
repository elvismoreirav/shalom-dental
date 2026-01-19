<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Detalle del paciente</h2>
        <p class="text-sm text-gray-500">Información básica registrada.</p>
    </div>
    <div class="space-x-2">
        <a href="/patients" class="px-4 py-2 rounded-lg border text-sm">Volver</a>
        <a href="/patients/<?= e((string) ($patient['id'] ?? 0)) ?>/edit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Editar</a>
    </div>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-gray-500">Nombre</div>
            <div class="font-medium text-gray-900"><?= e(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?></div>
        </div>
        <div>
            <div class="text-gray-500">Identificacion</div>
            <div class="font-medium text-gray-900"><?= e(($patient['id_type'] ?? '-') . ' ' . ($patient['id_number'] ?? '-')) ?></div>
        </div>
        <div>
            <div class="text-gray-500">Email</div>
            <div class="font-medium text-gray-900"><?= e($patient['email'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Telefono</div>
            <div class="font-medium text-gray-900"><?= e($patient['phone'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Direccion</div>
            <div class="font-medium text-gray-900"><?= e($patient['address'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Ciudad</div>
            <div class="font-medium text-gray-900"><?= e($patient['city'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Provincia</div>
            <div class="font-medium text-gray-900"><?= e($patient['province'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Nacimiento</div>
            <div class="font-medium text-gray-900"><?= e($patient['birth_date'] ?? '-') ?></div>
        </div>
    </div>
</div>

<?= $this->include('patients._files', [
    'files' => $files ?? [],
    'patientId' => $patient['id'] ?? 0,
    'canViewAll' => $canViewAll ?? false,
    'canViewOwn' => $canViewOwn ?? false,
    'userId' => $userId ?? 0,
]) ?>

<?php $this->endSection(); ?>
