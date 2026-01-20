<?php 
$this->extend('layouts.app');

// Set breadcrumbs for enhanced navigation
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Pacientes', 'url' => '/patients']
];

// Page context information
$pageContext = [
    'count' => ($total ?? 0) . ' pacientes',
    'action' => [
        'text' => 'Nuevo Paciente',
        'onclick' => 'window.location.href="/patients/create"'
    ]
];

// Header actions
$headerActions = [
    [
        'type' => 'link',
        'url' => '/patients/create',
        'text' => 'Nuevo Paciente',
        'class' => 'bg-shalom-primary text-white hover:bg-shalom-dark',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>'
    ]
];

if (can('reports.export.excel')) {
    $headerActions[] = [
        'type' => 'link',
        'url' => '/patients/export/csv',
        'text' => 'Exportar CSV',
        'class' => 'border border-gray-300 text-gray-700 hover:bg-gray-50',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>'
    ];
}
?>

<?php $this->section('content'); ?>

<!-- Enhanced Search and Filters Section -->
<div class="mb-6 bg-white rounded-lg border border-gray-100 p-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Búsqueda Avanzada</h3>
            <form method="get" action="/patients" class="space-y-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar paciente</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="q" 
                                value="<?= e($query ?? '') ?>" 
                                placeholder="Nombre, ID, email o teléfono..." 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary"
                            >
                            <div class="absolute left-3 top-2.5">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-6 py-2 bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors">
                            Buscar
                        </button>
                        <?php if (!empty($query)): ?>
                        <a href="/patients" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Limpiar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Filters -->
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600">Filtros rápidos:</span>
                    <button onclick="filterBy('recent')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Recientes
                    </button>
                    <button onclick="filterBy('active')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Activos
                    </button>
                    <button onclick="filterBy('today')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Visitas hoy
                    </button>
                </div>
            </form>
        </div>
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
