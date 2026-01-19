<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Editar paciente</h2>
    <p class="text-sm text-gray-500">Actualiza la informacion basica.</p>
</div>

<?= $this->include('patients._form', [
    'action' => '/patients/' . ($patient['id'] ?? 0),
    'method' => 'PUT',
    'patient' => $patient ?? [],
]) ?>

<?php $this->endSection(); ?>
