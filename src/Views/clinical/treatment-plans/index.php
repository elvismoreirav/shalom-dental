<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php
$patientId = $patient['id'] ?? 0;
$patientName = ($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '');
$statusColors = [
    'draft' => 'bg-gray-100 text-gray-600',
    'proposed' => 'bg-blue-100 text-blue-700',
    'accepted' => 'bg-green-100 text-green-700',
    'in_progress' => 'bg-yellow-100 text-yellow-700',
    'completed' => 'bg-emerald-100 text-emerald-700',
    'cancelled' => 'bg-red-100 text-red-700',
    'on_hold' => 'bg-orange-100 text-orange-700',
];
$statusLabels = [
    'draft' => 'Borrador',
    'proposed' => 'Propuesto',
    'accepted' => 'Aceptado',
    'in_progress' => 'En Progreso',
    'completed' => 'Completado',
    'cancelled' => 'Cancelado',
    'on_hold' => 'En Espera',
];
$priorityColors = [
    'low' => 'text-gray-500',
    'normal' => 'text-blue-600',
    'high' => 'text-orange-600',
    'urgent' => 'text-red-600',
];
$priorityLabels = [
    'low' => 'Baja',
    'normal' => 'Normal',
    'high' => 'Alta',
    'urgent' => 'Urgente',
];
?>

<!-- Enhanced Header with Dental Statistics -->
<div class="mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="/patients/<?= (int) $patientId ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7-7h18"/>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-900">Planes de Tratamiento</h2>
            </div>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($patientName) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="/patients/<?= (int) $patientId ?>/clinical-record" class="px-4 py-2 rounded-lg border border-gray-200 text-sm hover:bg-gray-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Historial Clínico
            </a>
            <a href="/patients/<?= (int) $patientId ?>/odontogram" class="px-4 py-2 rounded-lg border border-gray-200 text-sm hover:bg-gray-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h5m-7 4h10a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Odontograma
            </a>
            <a href="/clinical/treatment-plans/create?patient_id=<?= (int) $patientId ?>" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm hover:bg-shalom-dark transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Crear plan
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-100 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 011 1h-14a1 1 0 01-1-1v-2z"/>
                </svg>
                Filtros y Ordenamiento
            </h3>
            <div class="flex flex-wrap items-center gap-2">
                <select class="text-sm border border-gray-200 rounded px-3 py-1.5 focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                    <option value="all">Todos los estados</option>
                    <option value="active">Activos (Aceptados y en progreso)</option>
                    <option value="draft">Borradores</option>
                    <option value="proposed">Propuestos</option>
                    <option value="completed">Completados</option>
                </select>
                <select class="text-sm border border-gray-200 rounded px-3 py-1.5 focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                    <option value="date">Fecha de creación</option>
                    <option value="priority">Prioridad</option>
                    <option value="name">Nombre</option>
                    <option value="status">Estado</option>
                </select>
            </div>
        </div>
    </div>
</div>

<?php if (empty($plans)): ?>
<div class="bg-white shadow rounded-lg border border-gray-100 p-8 text-center">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-500">No hay planes de tratamiento registrados</p>
    <p class="text-gray-400 text-sm mt-1">Cree un nuevo plan para este paciente</p>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($plans as $plan):
        $statusClass = $statusColors[$plan['status']] ?? 'bg-gray-100 text-gray-600';
        $statusLabel = $statusLabels[$plan['status']] ?? $plan['status'];
        $priorityClass = $priorityColors[$plan['priority']] ?? 'text-gray-500';
        $priorityLabel = $priorityLabels[$plan['priority']] ?? $plan['priority'];
        $progress = (int) ($plan['progress'] ?? 0);
    ?>
    <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-gray-900"><?= htmlspecialchars($plan['name'] ?? '-') ?></span>
                <span class="px-2 py-0.5 text-xs font-medium rounded <?= $statusClass ?>"><?= $statusLabel ?></span>
                <span class="text-xs <?= $priorityClass ?>">
                    <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>
                    </svg>
                    <?= $priorityLabel ?>
                </span>
            </div>
            <span class="text-xs text-gray-500"><?= htmlspecialchars($plan['created_by_name'] ?? '') ?></span>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-4 gap-4 text-sm mb-4">
                <div>
                    <div class="text-xs text-gray-500">Progreso</div>
                    <div class="font-medium text-gray-900"><?= (int) ($plan['completed_items'] ?? 0) ?> / <?= (int) ($plan['total_items'] ?? 0) ?> items</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Estimado</div>
                    <div class="font-medium text-gray-900">$<?= number_format((float) ($plan['total_estimated'] ?? 0), 2) ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Facturado</div>
                    <div class="font-medium text-gray-900">$<?= number_format((float) ($plan['total_invoiced'] ?? 0), 2) ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Fecha Estimada</div>
                    <div class="font-medium text-gray-900"><?= $plan['estimated_completion_date'] ? date('d/m/Y', strtotime($plan['estimated_completion_date'])) : '-' ?></div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="bg-shalom-primary h-2 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
                </div>
                <span class="text-sm font-medium text-gray-600"><?= $progress ?>%</span>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <a href="/clinical/treatment-plans/<?= (int) $plan['id'] ?>" class="inline-flex items-center gap-1 text-sm text-shalom-primary hover:text-shalom-dark transition-colors">
                Ver detalle
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $this->endSection(); ?>
