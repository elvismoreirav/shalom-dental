<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Editar tipo de cita</h2>
    <p class="text-sm text-gray-500">Actualice el servicio.</p>
</div>

<?= $this->include('config.appointment-types._form', [
    'type' => $type ?? [],
    'categories' => $categories ?? [],
    'action' => '/config/appointment-types/' . ($type['id'] ?? 0),
    'method' => 'PUT',
]); ?>

<?php $this->endSection(); ?>
