<?php 
$this->extend('layouts.app');

// Set breadcrumbs for enhanced navigation
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Agenda', 'url' => '/agenda']
];

// Page context information
$pageContext = [
    'status' => [
        'text' => 'Vista Diaria',
        'class' => 'bg-blue-100 text-blue-800'
    ],
    'count' => count($appointments ?? []) . ' citas',
    'action' => [
        'text' => 'Nueva Cita',
        'onclick' => 'window.location.href="/agenda/create"'
    ]
];

// Header actions
$headerActions = [
    [
        'type' => 'link',
        'url' => '/agenda/create',
        'text' => 'Nueva Cita',
        'class' => 'bg-shalom-primary text-white hover:bg-shalom-dark',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'
    ],
    [
        'type' => 'link', 
        'url' => '/agenda/calendar',
        'text' => 'Ver Calendario',
        'class' => 'border border-gray-300 text-gray-700 hover:bg-gray-50',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'
    ]
];
?>

<?php $this->section('content'); ?>

<!-- Enhanced Header with Stats -->
<div class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Citas Hoy</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($appointments ?? []) ?></p>
                </div>
                <div class="bg-blue-100 rounded-lg p-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Confirmadas</p>
                    <p class="text-2xl font-bold text-green-600"><?= count(array_filter($appointments ?? [], fn($a) => ($a['status'] ?? '') === 'confirmed')) ?></p>
                </div>
                <div class="bg-green-100 rounded-lg p-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">En Espera</p>
                    <p class="text-2xl font-bold text-yellow-600"><?= count(array_filter($appointments ?? [], fn($a) => ($a['status'] ?? '') === 'waiting')) ?></p>
                </div>
                <div class="bg-yellow-100 rounded-lg p-2">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Completadas</p>
                    <p class="text-2xl font-bold text-gray-600"><?= count(array_filter($appointments ?? [], fn($a) => ($a['status'] ?? '') === 'completed')) ?></p>
                </div>
                <div class="bg-gray-100 rounded-lg p-2">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="get" action="/agenda" class="mb-4">
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <input type="text" name="q" value="<?= e($query ?? '') ?>" placeholder="Buscar paciente o tipo" class="w-full md:w-64 border rounded-lg px-3 py-2">
        <input type="date" name="date" value="<?= e($date ?? '') ?>" class="border rounded-lg px-3 py-2">
        <select name="status" class="border rounded-lg px-3 py-2">
            <?php $currentStatus = $status ?? ''; ?>
            <option value="">Estado (todos)</option>
            <?php foreach (['scheduled','confirmed','checked_in','in_progress','completed','cancelled','no_show','rescheduled','late'] as $option): ?>
                <option value="<?= e($option) ?>" <?= $currentStatus === $option ? 'selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="px-4 py-2 rounded-lg border">Filtrar</button>
        <?php if (!empty($query) || !empty($status) || !empty($date)): ?>
            <a href="/agenda" class="px-3 py-2 text-gray-500">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <?php
                        $queryParams = [
                            'q' => $query ?? '',
                            'date' => $date ?? '',
                            'status' => $status ?? '',
                        ];
                        $toggle = fn($field) => http_build_query(array_merge($queryParams, [
                            'sort' => $field,
                            'dir' => (($sort ?? 'time') === $field && ($dir ?? 'asc') === 'asc') ? 'desc' : 'asc'
                        ]));
                    ?>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/agenda?<?= $toggle('time') ?>" class="hover:underline">Hora</a></th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/agenda?<?= $toggle('patient') ?>" class="hover:underline">Paciente</a></th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/agenda?<?= $toggle('type') ?>" class="hover:underline">Tipo</a></th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/agenda?<?= $toggle('status') ?>" class="hover:underline">Estado</a></th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($appointments)): ?>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700">
                                <?= e(($appointment['scheduled_start_time'] ?? '-') . ' - ' . ($appointment['scheduled_end_time'] ?? '-')) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-900">
                                <?= e(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '')) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?= e($appointment['appointment_type_name'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($appointment['status'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-right">
                                <?php if (can('clinical.notes.view')): ?>
                                    <a href="/clinical/attend/<?= e((string) $appointment['id']) ?>" class="text-shalom-primary hover:underline">Atender</a>
                                <?php endif; ?>
                                <a href="/agenda/<?= e((string) $appointment['id']) ?>" class="text-shalom-primary hover:underline">Ver</a>
                                <a href="/agenda/<?= e((string) $appointment['id']) ?>/edit" class="ml-3 text-shalom-primary hover:underline">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            No hay citas para hoy.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($totalPages ?? 1) > 1): ?>
    <div class="mt-4 flex items-center justify-between text-sm">
        <div class="text-gray-500">Pagina <?= e((string) ($page ?? 1)) ?> de <?= e((string) ($totalPages ?? 1)) ?></div>
        <div class="flex items-center gap-2">
            <?php
                $page = $page ?? 1;
                $totalPages = $totalPages ?? 1;
                $queryParams = [
                    'q' => $query ?? '',
                    'date' => $date ?? '',
                    'status' => $status ?? '',
                    'sort' => $sort ?? 'time',
                    'dir' => $dir ?? 'asc',
                ];
                $build = function(array $extra = []) use ($queryParams) {
                    return http_build_query(array_merge($queryParams, $extra));
                };
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
            ?>
            <?php if ($page > 1): ?>
                <a class="px-3 py-1 border rounded" href="/agenda?<?= $build(['page' => $page - 1]) ?>">Anterior</a>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-3 py-1 border rounded bg-gray-100"><?= $i ?></span>
                <?php else: ?>
                    <a class="px-3 py-1 border rounded" href="/agenda?<?= $build(['page' => $i]) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="px-3 py-1 border rounded" href="/agenda?<?= $build(['page' => $page + 1]) ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php $this->endSection(); ?>
