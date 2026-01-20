<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Nueva categoría</h2>
    <p class="text-sm text-gray-500">Configure categorías de servicios.</p>
</div>

<?= $this->include('config.service-categories._form', [
    'category' => $category ?? [],
    'action' => '/config/service-categories',
    'method' => 'POST',
]); ?>

<?php $this->endSection(); ?>
