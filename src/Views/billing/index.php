<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Facturacion</h2>
    <p class="text-sm text-gray-500">Accesos rapidos a facturas y monitor.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="/billing/invoices" class="bg-white border border-gray-100 rounded-lg shadow-sm p-5 hover:border-gray-200 transition">
        <div class="text-sm text-gray-500">Facturas</div>
        <div class="text-lg font-semibold text-gray-900">Ver listado</div>
    </a>
    <a href="/billing/invoices/create" class="bg-white border border-gray-100 rounded-lg shadow-sm p-5 hover:border-gray-200 transition">
        <div class="text-sm text-gray-500">Facturas</div>
        <div class="text-lg font-semibold text-gray-900">Crear nueva</div>
    </a>
    <a href="/billing/monitor" class="bg-white border border-gray-100 rounded-lg shadow-sm p-5 hover:border-gray-200 transition">
        <div class="text-sm text-gray-500">SRI</div>
        <div class="text-lg font-semibold text-gray-900">Monitor</div>
    </a>
</div>

<?php $this->endSection(); ?>
