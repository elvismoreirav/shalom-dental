<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php
$patientId = $plan['patient_id'] ?? 0;
$patientName = ($plan['patient_first_name'] ?? '') . ' ' . ($plan['patient_last_name'] ?? '');
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
$priorityLabels = [
    'low' => 'Baja',
    'normal' => 'Normal',
    'high' => 'Alta',
    'urgent' => 'Urgente',
];
$itemStatusColors = [
    'pending' => 'bg-gray-100 text-gray-600',
    'scheduled' => 'bg-blue-100 text-blue-700',
    'completed' => 'bg-green-100 text-green-700',
    'cancelled' => 'bg-red-100 text-red-600',
];
$itemStatusLabels = [
    'pending' => 'Pendiente',
    'scheduled' => 'Programado',
    'completed' => 'Completado',
    'cancelled' => 'Cancelado',
];
$statusClass = $statusColors[$plan['status']] ?? 'bg-gray-100 text-gray-600';
$statusLabel = $statusLabels[$plan['status']] ?? $plan['status'];
$priorityLabel = $priorityLabels[$plan['priority']] ?? $plan['priority'];
$progress = $plan['total_items'] > 0 ? round(($plan['completed_items'] / $plan['total_items']) * 100) : 0;
?>

<div x-data="treatmentPlanDetail()">
    <!-- Header -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="/clinical/patients/<?= (int) $patientId ?>/treatment-plans" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($plan['name'] ?? 'Plan') ?></h2>
                <span class="px-2 py-0.5 text-xs font-medium rounded <?= $statusClass ?>"><?= $statusLabel ?></span>
            </div>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($patientName) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <?php if (!in_array($plan['status'], ['completed', 'cancelled'])): ?>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors inline-flex items-center gap-2">
                    Cambiar Estado
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-10">
                    <?php
                    $transitions = [
                        'draft' => ['proposed', 'cancelled'],
                        'proposed' => ['accepted', 'draft', 'cancelled'],
                        'accepted' => ['in_progress', 'on_hold', 'cancelled'],
                        'in_progress' => ['completed', 'on_hold', 'cancelled'],
                        'on_hold' => ['in_progress', 'cancelled'],
                    ];
                    $allowedTransitions = $transitions[$plan['status']] ?? [];
                    foreach ($allowedTransitions as $newStatus):
                    ?>
                    <button @click="updateStatus('<?= $newStatus ?>')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">
                        <?= $statusLabels[$newStatus] ?? $newStatus ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <a href="/patients/<?= (int) $patientId ?>" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                Ver Paciente
            </a>
            <a href="/patients/<?= (int) $patientId ?>/odontogram" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                Ver Odontograma
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="bg-white shadow rounded-lg border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <div class="text-xs text-gray-500">Prioridad</div>
                <div class="font-medium text-gray-900"><?= htmlspecialchars($priorityLabel) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Fecha Estimada</div>
                <div class="font-medium text-gray-900"><?= $plan['estimated_completion_date'] ? date('d/m/Y', strtotime($plan['estimated_completion_date'])) : '-' ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Total Estimado</div>
                <div class="font-medium text-gray-900">$<?= number_format((float) ($plan['total_estimated'] ?? 0), 2) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Total Facturado</div>
                <div class="font-medium text-gray-900">$<?= number_format((float) ($plan['total_invoiced'] ?? 0), 2) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Creado por</div>
                <div class="font-medium text-gray-900"><?= htmlspecialchars($plan['created_by_name'] ?? '-') ?></div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-600">Progreso</span>
                <span class="font-medium"><?= (int) ($plan['completed_items'] ?? 0) ?> / <?= (int) ($plan['total_items'] ?? 0) ?> items (<?= $progress ?>%)</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="bg-shalom-primary h-3 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
            </div>
        </div>
        <?php if (!empty($plan['description'])): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="text-xs text-gray-500 mb-1">Descripción</div>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($plan['patient_observations'])): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="text-xs text-gray-500 mb-1">Observaciones del Paciente</div>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($plan['patient_observations'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Items by Phase -->
    <?php if (!empty($itemsByPhase)): ?>
    <?php foreach ($itemsByPhase as $phase => $phaseItems): ?>
    <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-sm font-semibold text-gray-700">
                <?php if ($phase !== 'General'): ?>
                Fase: <?= htmlspecialchars($phase) ?>
                <?php else: ?>
                Procedimientos
                <?php endif; ?>
                <span class="text-gray-400 font-normal">(<?= count($phaseItems) ?>)</span>
            </h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($phaseItems as $item):
                $itemStatusClass = $itemStatusColors[$item['status']] ?? 'bg-gray-100 text-gray-600';
                $itemStatusLabel = $itemStatusLabels[$item['status']] ?? $item['status'];
            ?>
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <?php if (!empty($item['category_color'])): ?>
                            <span class="w-2 h-2 rounded-full" style="background-color: <?= htmlspecialchars($item['category_color']) ?>"></span>
                            <?php endif; ?>
                            <span class="font-medium text-gray-900"><?= htmlspecialchars($item['service_name'] ?? '-') ?></span>
                            <span class="text-xs text-gray-400"><?= htmlspecialchars($item['service_code'] ?? '') ?></span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded <?= $itemStatusClass ?>"><?= $itemStatusLabel ?></span>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                            <?php if (!empty($item['tooth_number'])): ?>
                            <span>Pieza: <?= htmlspecialchars($item['tooth_number']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['surfaces'])): ?>
                            <span>Superficies: <?= htmlspecialchars($item['surfaces']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['scheduled_date'])): ?>
                            <span class="text-blue-600">
                                <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <?= date('d/m/Y', strtotime($item['scheduled_date'])) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['completed_at'])): ?>
                            <span class="text-green-600">Completado: <?= date('d/m/Y', strtotime($item['completed_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                        <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-gray-900">$<?= number_format((float) ($item['estimated_price'] ?? 0), 2) ?></div>
                        <?php if ($item['is_invoiced']): ?>
                        <span class="text-xs text-green-600">Facturado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="bg-white shadow rounded-lg border border-gray-100 p-8 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-500">No hay procedimientos en este plan</p>
    </div>
    <?php endif; ?>

    <!-- Notes -->
    <?php if (!empty($plan['notes'])): ?>
    <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4 mt-4">
        <h4 class="text-sm font-medium text-yellow-800 mb-2">Notas del Plan</h4>
        <p class="text-sm text-yellow-700"><?= nl2br(htmlspecialchars($plan['notes'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
function treatmentPlanDetail() {
    return {
        async updateStatus(newStatus) {
            if (!confirm('¿Cambiar el estado del plan a ' + newStatus + '?')) return;

            try {
                const response = await fetch('/api/clinical/treatment-plans/<?= (int) $plan['id'] ?>/status', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= csrf_token() ?>'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al actualizar estado');
                }
            } catch (e) {
                alert('Error de conexión');
            }
        }
    }
}
</script>

<?php $this->endSection(); ?>
