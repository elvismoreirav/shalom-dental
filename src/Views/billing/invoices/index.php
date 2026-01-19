<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Facturas</h2>
        <p class="text-sm text-gray-500">Total: <?= e((string) ($total ?? 0)) ?> • Monto: $<?= number_format((float) ($totalAmount ?? 0), 2) ?></p>
    </div>
    <a href="/billing/invoices/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nueva factura</a>
</div>

<form method="get" action="/billing/invoices" class="mb-4">
    <div class="flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="<?= e($query ?? '') ?>" placeholder="Buscar paciente" class="w-full md:w-72 border rounded-lg px-3 py-2 text-sm">
        <select name="status" class="border rounded-lg px-3 py-2 text-sm">
            <?php $currentStatus = $status ?? ''; ?>
            <option value="">Estado (todos)</option>
            <?php foreach (['draft','pending','sent','authorized','rejected','voided','contingency'] as $opt): ?>
                <option value="<?= e($opt) ?>" <?= $currentStatus === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" value="<?= e($dateFrom ?? '') ?>" class="border rounded-lg px-3 py-2 text-sm">
        <input type="date" name="date_to" value="<?= e($dateTo ?? '') ?>" class="border rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="px-4 py-2 rounded-lg border text-sm">Filtrar</button>
        <?php if (!empty($query) || !empty($currentStatus) || !empty($dateFrom) || !empty($dateTo)): ?>
            <a href="/billing/invoices" class="px-3 py-2 text-sm text-gray-500">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Fecha</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Paciente</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Total</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700">
                                <a class="text-shalom-primary hover:underline" href="/billing/invoices/<?= e((string) $invoice['id']) ?>">
                                    #<?= e((string) $invoice['id']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?= e($invoice['issue_date'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-900">
                                <?= e(trim(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? ''))) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-700">$<?= number_format((float) ($invoice['total'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-gray-600">
                                <?php
                                    $status = $invoice['status'] ?? '-';
                                    $badge = match ($status) {
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'sent' => 'bg-blue-100 text-blue-800',
                                        'authorized' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'voided' => 'bg-gray-200 text-gray-800',
                                        'contingency' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                ?>
                                <span class="px-2 py-1 rounded text-xs <?= $badge ?>"><?= e($status) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay facturas.</td>
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
                    'status' => $status ?? '',
                    'date_from' => $dateFrom ?? '',
                    'date_to' => $dateTo ?? '',
                ];
                $build = function(array $extra = []) use ($queryParams) {
                    return http_build_query(array_merge($queryParams, $extra));
                };
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
            ?>
            <?php if ($page > 1): ?>
                <a class="px-3 py-1 border rounded" href="/billing/invoices?<?= $build(['page' => $page - 1]) ?>">Anterior</a>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-3 py-1 border rounded bg-gray-100"><?= $i ?></span>
                <?php else: ?>
                    <a class="px-3 py-1 border rounded" href="/billing/invoices?<?= $build(['page' => $i]) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="px-3 py-1 border rounded" href="/billing/invoices?<?= $build(['page' => $page + 1]) ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php $this->endSection(); ?>
