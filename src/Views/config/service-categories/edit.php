<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Editar categoría</h2>
    <p class="text-sm text-gray-500">Actualice los datos de la categoría.</p>
</div>

<?= $this->include('config.service-categories._form', [
    'category' => $category ?? [],
    'action' => '/config/service-categories/' . ($category['id'] ?? 0),
    'method' => 'PUT',
]); ?>

<?php $this->endSection(); ?>
