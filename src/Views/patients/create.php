<?php
$this->extend('layouts.app');
?>

<?php $this->section('content'); ?>

<!-- Enhanced Header with Context -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="/patients" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            Pacientes
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-shalom-primary font-medium">Nuevo Paciente</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <div class="w-12 h-12 bg-shalom-primary rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                Crear Nuevo Paciente
            </h1>
            <p class="mt-2 text-gray-600">Registra un nuevo paciente en el sistema. La información será utilizada para agendar citas y mantener un historial médico completo.</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="hidden lg:flex items-center space-x-4">
            <div class="bg-shalom-light rounded-lg px-4 py-3 text-center">
                <p class="text-xs text-shalom-primary font-medium">Total Pacientes</p>
                <p class="text-xl font-bold text-shalom-primary"><?= number_format((int) ($stats['total_patients'] ?? 0)) ?></p>
            </div>
            <div class="bg-green-50 rounded-lg px-4 py-3 text-center">
                <p class="text-xs text-green-600 font-medium">Nuevos este mes</p>
                <p class="text-xl font-bold text-green-600"><?= number_format((int) ($stats['new_patients_month'] ?? 0)) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Help Tips Card -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Consejo para un mejor registro</h3>
            <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc list-inside space-y-1">
                    <li>Los campos marcados con <span class="text-red-500">*</span> son obligatorios</li>
                    <li>El teléfono es crucial para confirmar citas y enviar recordatorios</li>
                    <li>Las notas médicas ayudan a personalizar el tratamiento dental</li>
                    <li>Puedes completar la información básica ahora y agregar detalles después</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Form -->
<?= $this->include('patients._form', [
    'action' => '/patients',
    'method' => 'POST',
]) ?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
