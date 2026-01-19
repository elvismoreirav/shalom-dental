<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Pacientes</h2>
        <p class="text-sm text-gray-500">Total: <?= e((string) ($total ?? 0)) ?> resultados.</p>
    </div>
    <div class="flex items-center gap-2">
        <?php if (can('reports.export.excel')): ?>
            <a href="/patients/export/csv" class="px-4 py-2 rounded-lg border text-sm">Exportar CSV</a>
        <?php endif; ?>
        <a href="/patients/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nuevo paciente</a>
    </div>
</div>

<form method="get" action="/patients" class="mb-4">
    <div class="flex items-center gap-2">
        <input type="text" name="q" value="<?= e($query ?? '') ?>" placeholder="Buscar por nombre, ID, email o telefono" class="w-full md:w-96 border rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="px-4 py-2 rounded-lg border text-sm">Buscar</button>
        <?php if (!empty($query)): ?>
            <a href="/patients" class="px-3 py-2 text-sm text-gray-500">Limpiar</a>
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
                        ];
                        $toggle = fn($field) => http_build_query(array_merge($queryParams, [
                            'sort' => $field,
                            'dir' => (($sort ?? 'created') === $field && ($dir ?? 'desc') === 'asc') ? 'desc' : 'asc'
                        ]));
                    ?>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/patients?<?= $toggle('name') ?>" class="hover:underline">Paciente</a></th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/patients?<?= $toggle('email') ?>" class="hover:underline">Email</a></th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Telefono</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600"><a href="/patients?<?= $toggle('created') ?>" class="hover:underline">Creado</a></th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($patients)): ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    <?= e($patient['first_name'] . ' ' . $patient['last_name']) ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?= e($patient['email'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($patient['phone'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($patient['created_at'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="/patients/<?= e((string) $patient['id']) ?>" class="text-shalom-primary hover:underline">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            No hay pacientes registrados.
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
                $queryParam = !empty($query) ? ('&q=' . urlencode($query)) : '';
                $sortParam = !empty($sort) ? ('&sort=' . urlencode($sort) . '&dir=' . urlencode($dir ?? 'desc')) : '';
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
            ?>
            <?php if ($page > 1): ?>
                <a class="px-3 py-1 border rounded" href="/patients?page=<?= $page - 1 ?><?= $queryParam ?><?= $sortParam ?>">Anterior</a>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-3 py-1 border rounded bg-gray-100"><?= $i ?></span>
                <?php else: ?>
                    <a class="px-3 py-1 border rounded" href="/patients?page=<?= $i ?><?= $queryParam ?><?= $sortParam ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="px-3 py-1 border rounded" href="/patients?page=<?= $page + 1 ?><?= $queryParam ?><?= $sortParam ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php $this->endSection(); ?>
