<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Agenda del dia</h2>
        <p class="text-sm text-gray-500">Citas programadas para hoy.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="/agenda/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nueva cita</a>
        <a href="/agenda/calendar" class="px-4 py-2 rounded-lg border text-sm">Ver calendario</a>
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
