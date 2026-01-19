<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Factura #<?= e((string) ($invoice['id'] ?? 0)) ?></h2>
        <p class="text-sm text-gray-500">Detalle de la factura.</p>
    </div>
    <div class="space-x-2">
        <a href="/billing/invoices" class="px-4 py-2 rounded-lg border text-sm">Volver</a>
        <a href="/billing/invoices/<?= e((string) ($invoice['id'] ?? 0)) ?>/edit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Editar</a>
        <a href="/billing/invoices/<?= e((string) ($invoice['id'] ?? 0)) ?>/pdf" class="px-4 py-2 rounded-lg border text-sm">PDF</a>
        <?php if (can('billing.invoices.void')): ?>
            <form action="/billing/invoices/<?= e((string) ($invoice['id'] ?? 0)) ?>/void" method="post" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="px-4 py-2 rounded-lg border text-sm text-red-600">Anular</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white shadow rounded-lg border border-gray-100 p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-gray-500">Comprador</div>
            <div class="font-medium text-gray-900"><?= e($invoice['buyer_name'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Fecha</div>
            <div class="font-medium text-gray-900"><?= e($invoice['issue_date'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Vencimiento</div>
            <div class="font-medium text-gray-900"><?= e($invoice['due_date'] ?? '-') ?></div>
        </div>
        <div>
            <div class="text-gray-500">Identificacion</div>
            <div class="font-medium text-gray-900"><?= e(($invoice['buyer_id_type'] ?? '-') . ' ' . ($invoice['buyer_id_number'] ?? '-')) ?></div>
        </div>
        <div>
            <div class="text-gray-500">Total</div>
            <div class="font-medium text-gray-900">$<?= number_format((float) ($invoice['total'] ?? 0), 2) ?></div>
        </div>
    </div>
</div>

<div class="mt-6 bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Descripcion</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Cantidad</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Precio</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Descuento</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">IVA %</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="px-4 py-3 text-gray-900"><?= e($item['description'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= e((string) ($item['quantity'] ?? 0)) ?></td>
                        <td class="px-4 py-3 text-gray-600">$<?= number_format((float) ($item['unit_price'] ?? 0), 2) ?></td>
                        <td class="px-4 py-3 text-gray-600">$<?= number_format((float) ($item['discount_amount'] ?? 0), 2) ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= e((string) ($item['tax_percentage'] ?? 0)) ?></td>
                        <td class="px-4 py-3 text-gray-600">$<?= number_format((float) ($item['total'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin items.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-6 bg-white shadow rounded-lg border border-gray-100 p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div class="flex justify-between text-gray-600">
            <span>Subtotal sin impuestos</span>
            <span>$<?= number_format((float) ($invoice['subtotal_no_tax'] ?? $invoice['subtotal'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Subtotal IVA 0%</span>
            <span>$<?= number_format((float) ($invoice['subtotal_0'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Subtotal IVA 12%</span>
            <span>$<?= number_format((float) ($invoice['subtotal_12'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Subtotal IVA 15%</span>
            <span>$<?= number_format((float) ($invoice['subtotal_15'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Total descuento</span>
            <span>$<?= number_format((float) ($invoice['total_discount'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Total IVA</span>
            <span>$<?= number_format((float) ($invoice['total_tax'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Total ICE</span>
            <span>$<?= number_format((float) ($invoice['total_ice'] ?? 0), 2) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Propina</span>
            <span>$<?= number_format((float) ($invoice['tip'] ?? 0), 2) ?></span>
        </div>
        <?php
            $subtotalNoTax = (float) ($invoice['subtotal_no_tax'] ?? $invoice['subtotal'] ?? 0);
            $totalTax = (float) ($invoice['total_tax'] ?? 0);
            $totalIce = (float) ($invoice['total_ice'] ?? 0);
            $totalWithTax = $subtotalNoTax + $totalTax + $totalIce;
        ?>
        <div class="flex justify-between font-medium text-gray-900">
            <span>Total con impuestos</span>
            <span>$<?= number_format($totalWithTax, 2) ?></span>
        </div>
        <div class="flex justify-between font-bold text-gray-900">
            <span>Total con impuestos + propina</span>
            <span>$<?= number_format((float) ($invoice['total'] ?? 0), 2) ?></span>
        </div>
    </div>
</div>

<div class="mt-6 bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Metodo</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Monto</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Referencia</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td class="px-4 py-3 text-gray-900"><?= e($payment['payment_method_name'] ?? $payment['payment_method_code'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-gray-600">$<?= number_format((float) ($payment['amount'] ?? 0), 2) ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= e($payment['reference_number'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin pagos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <form action="/billing/invoices/<?= e((string) ($invoice['id'] ?? 0)) ?>/payments" method="post" class="space-y-2">
        <?= csrf_field() ?>
        <?= $this->include('billing.invoices._payments', ['payments' => $payments ?? []]); ?>
        <div class="mt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar pagos</button>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>
