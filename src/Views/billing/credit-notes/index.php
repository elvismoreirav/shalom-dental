<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Notas de credito</h2>
        <p class="text-sm text-gray-500">Listado de notas emitidas.</p>
    </div>
    <a href="/billing/credit-notes/create" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Nueva nota</a>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Factura</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Cliente</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Fecha</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Monto</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($notes)): ?>
                    <?php foreach ($notes as $note): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700">#<?= e((string) $note['id']) ?></td>
                            <td class="px-4 py-3 text-gray-600">#<?= e((string) $note['invoice_id']) ?></td>
                            <td class="px-4 py-3 text-gray-900"><?= e($note['buyer_name'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($note['issue_date'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-gray-700">$<?= number_format((float) ($note['amount'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= e($note['status'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin notas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->endSection(); ?>
