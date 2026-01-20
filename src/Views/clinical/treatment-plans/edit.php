<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Editar plan de tratamiento</h2>
    <p class="text-sm text-gray-500">Actualice procedimientos y estados.</p>
</div>

<?= $this->include('clinical.treatment-plans._form', [
    'patientId' => $patientId,
    'plan' => $plan ?? [],
    'items' => $items ?? [],
    'appointmentTypes' => $appointmentTypes ?? [],
    'action' => "/patients/{$patientId}/treatment-plans/" . ($plan['id'] ?? 0),
    'method' => 'PUT',
]); ?>

<?php $this->endSection(); ?>
