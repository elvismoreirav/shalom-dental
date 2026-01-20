<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Historial Clínico</h2>
        <p class="text-sm text-gray-500">Registro médico y odontológico del paciente.</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" form="clinical-record-form" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar cambios</button>
        <a href="/patients/<?= e((string) $patientId) ?>" class="px-4 py-2 rounded-lg border text-sm">Volver</a>
    </div>
</div>

<form id="clinical-record-form" action="/patients/<?= e((string) $patientId) ?>/clinical-record" method="post" class="space-y-6">
    <?= csrf_field() ?>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Antecedentes</h3>
        <p class="text-sm text-gray-500 mb-4">Información médica base para una atención segura.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Antecedentes médicos (JSON)</label>
                <textarea name="medical_history" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e(json_encode($record['medical_history'] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Antecedentes quirúrgicos</label>
                <textarea name="surgical_history" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($record['surgical_history'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Antecedentes familiares</label>
                <textarea name="family_history" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($record['family_history'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Hábitos</h3>
        <p class="text-sm text-gray-500 mb-4">Identifica factores de riesgo y frecuencia de cuidados.</p>
        <div class="grid grid-cols-1 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">Hábitos (JSON)</label>
                <textarea name="habits" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e(json_encode($record['habits'] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Historia odontológica</h3>
        <p class="text-sm text-gray-500 mb-4">Resumen del tratamiento previo y hábitos de higiene.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Antecedentes odontológicos</label>
                <textarea name="dental_history" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($record['dental_history'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Última visita dental</label>
                <input type="date" name="last_dental_visit" value="<?= e($record['last_dental_visit'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Frecuencia de higiene oral</label>
                <input type="text" name="oral_hygiene_frequency" value="<?= e($record['oral_hygiene_frequency'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" placeholder="Ej: 2x/día">
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Examen</h3>
        <p class="text-sm text-gray-500 mb-4">Observaciones clínicas relevantes en la primera evaluación.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">Examen extraoral (JSON)</label>
                <textarea name="extraoral_exam" rows="4" class="w-full border rounded-lg px-3 py-2"><?= e(json_encode($record['extraoral_exam'] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Examen intraoral (JSON)</label>
                <textarea name="intraoral_exam" rows="4" class="w-full border rounded-lg px-3 py-2"><?= e(json_encode($record['intraoral_exam'] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Oclusión y diagnóstico</h3>
        <p class="text-sm text-gray-500 mb-4">Diagnóstico general y observaciones sobre la oclusión.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">Tipo de oclusión</label>
                <input type="text" name="occlusion_type" value="<?= e($record['occlusion_type'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Notas de oclusión</label>
                <textarea name="occlusion_notes" rows="2" class="w-full border rounded-lg px-3 py-2"><?= e($record['occlusion_notes'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Diagnóstico general</label>
                <textarea name="general_diagnosis" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($record['general_diagnosis'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/patients/<?= e((string) $patientId) ?>" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>

<?php $this->endSection(); ?>
