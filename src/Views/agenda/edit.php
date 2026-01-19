<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Editar cita</h2>
    <p class="text-sm text-gray-500">Actualiza los datos de la cita.</p>
</div>

<?= $this->include('agenda._form', [
    'action' => '/agenda/' . ($appointment['id'] ?? 0),
    'method' => 'PUT',
    'appointment' => $appointment ?? [],
    'patients' => $patients ?? [],
    'services' => $services ?? [],
    'appointmentTypes' => $appointmentTypes ?? [],
    'today' => $today ?? '',
    'startTime' => $startTime ?? '',
    'endTime' => $endTime ?? '',
    'slots' => $slots ?? [],
]) ?>

<?php $this->endSection(); ?>
