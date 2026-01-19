<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Monitor SRI</h2>
    <p class="text-sm text-gray-500">Facturas pendientes o rechazadas.</p>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Fecha</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Cliente</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Total</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Accion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700">#<?= e((string) $invoice['id']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($invoice['issue_date'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-900"><?= e($invoice['buyer_name'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-700">$<?= number_format((float) ($invoice['total'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= e($invoice['status'] ?? '-') ?>
                                <?php if (!empty($invoice['sri_error_messages'])): ?>
                                    <div class="text-xs text-red-600 mt-1">
                                        <?= e(is_string($invoice['sri_error_messages']) ? $invoice['sri_error_messages'] : json_encode($invoice['sri_error_messages'], JSON_UNESCAPED_UNICODE)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if (can('billing.sri_monitor.retry')): ?>
                                    <form action="/billing/monitor/<?= e((string) $invoice['id']) ?>/retry" method="post" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-3 py-1 border rounded text-sm">Reintentar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin registros.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->endSection(); ?>
