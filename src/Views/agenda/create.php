<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Nueva cita</h2>
    <p class="text-sm text-gray-500">Registra una cita en la agenda.</p>
</div>

<?= $this->include('agenda._form', [
    'action' => '/agenda',
    'method' => 'POST',
    'patients' => $patients ?? [],
    'services' => $services ?? [],
    'appointmentTypes' => $appointmentTypes ?? [],
    'today' => $today ?? '',
    'startTime' => $startTime ?? '',
    'endTime' => $endTime ?? '',
    'slots' => $slots ?? [],
]) ?>

<?php $this->endSection(); ?>
