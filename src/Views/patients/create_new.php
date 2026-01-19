<?php
$this->extend('layouts.app');
?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Crear paciente</h2>
    <p class="text-sm text-gray-500">Completa la informacion basica.</p>
</div>

<form action="/patients" method="post" class="space-y-4">
    <input type="text" name="first_name" placeholder="First Name">
    <input type="text" name="last_name" placeholder="Last Name">
</form>

<?php $this->endSection(); ?>