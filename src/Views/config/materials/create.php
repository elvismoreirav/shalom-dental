<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Nuevo material</h2>
    <p class="text-sm text-gray-500">Ingrese los datos del material.</p>
</div>

<?= $this->include('config.materials._form', [
    'material' => $material ?? [],
    'action' => '/config/materials',
    'method' => 'POST',
]); ?>

<?php $this->endSection(); ?>
