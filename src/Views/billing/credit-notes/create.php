<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Crear nota de credito</h2>
    <p class="text-sm text-gray-500">Selecciona la factura y el motivo.</p>
</div>

<form action="/billing/credit-notes" method="post" class="space-y-4">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Factura *</label>
            <select name="invoice_id" class="w-full border rounded-lg px-3 py-2" required>
                <option value="">Seleccionar</option>
                <?php foreach (($invoices ?? []) as $invoice): ?>
                    <option value="<?= e((string) $invoice['id']) ?>">#<?= e((string) $invoice['id']) ?> - <?= e($invoice['buyer_name'] ?? '-') ?> ($<?= number_format((float) ($invoice['total'] ?? 0), 2) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Monto *</label>
            <input type="number" step="0.01" name="amount" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Motivo *</label>
            <textarea name="reason" rows="3" class="w-full border rounded-lg px-3 py-2" required></textarea>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/billing/credit-notes" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>

<?php $this->endSection(); ?>
