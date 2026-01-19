<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $template = $template ?? []; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900"><?= e($title ?? 'Plantilla') ?></h2>
    <p class="text-sm text-gray-500">Define el contenido de la notificacion.</p>
</div>

<form action="<?= e($template['id'] ?? 0 ? '/notifications/templates/' . $template['id'] . '/update' : '/notifications/templates') ?>" method="post" class="space-y-4">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Codigo *</label>
            <input type="text" name="code" value="<?= e($template['code'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Nombre *</label>
            <input type="text" name="name" value="<?= e($template['name'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Canal *</label>
            <?php $channel = $template['channel'] ?? 'email'; ?>
            <select name="channel" class="w-full border rounded-lg px-3 py-2" required>
                <option value="email" <?= $channel === 'email' ? 'selected' : '' ?>>Email</option>
                <option value="sms" <?= $channel === 'sms' ? 'selected' : '' ?>>SMS</option>
                <option value="whatsapp" <?= $channel === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Evento *</label>
            <input type="text" name="event_type" value="<?= e($template['event_type'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Asunto</label>
            <input type="text" name="subject_template" value="<?= e($template['subject_template'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Contenido *</label>
            <textarea name="body_template" rows="6" class="w-full border rounded-lg px-3 py-2" required><?= e($template['body_template'] ?? '') ?></textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Activo</label>
            <input type="checkbox" name="is_active" value="1" <?= !empty($template['is_active']) ? 'checked' : '' ?>>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/notifications/templates" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>

<?php $this->endSection(); ?>
