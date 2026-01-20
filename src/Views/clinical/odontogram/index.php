<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php
$teethRows = [
    ['11','12','13','14','15','16','17','18'],
    ['21','22','23','24','25','26','27','28'],
    ['31','32','33','34','35','36','37','38'],
    ['41','42','43','44','45','46','47','48'],
];
$statusOptions = [
    'healthy' => 'Sano',
    'decayed' => 'Caries',
    'filled' => 'Restaurado',
    'crowned' => 'Corona',
    'missing' => 'Ausente',
    'extracted' => 'Extraído',
    'impacted' => 'Impactado',
    'implant' => 'Implante',
    'bridge_pontic' => 'Puente póntico',
    'bridge_abutment' => 'Puente pilar',
    'root_canal' => 'Endodoncia',
    'prosthetic' => 'Prótesis',
    'sealant' => 'Sellante',
    'veneer' => 'Carilla',
    'fracture' => 'Fractura',
];
$periodontalOptions = [
    'healthy' => 'Sano',
    'gingivitis' => 'Gingivitis',
    'periodontitis_mild' => 'Periodontitis leve',
    'periodontitis_moderate' => 'Periodontitis moderada',
    'periodontitis_severe' => 'Periodontitis severa',
];
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Odontograma</h2>
        <p class="text-sm text-gray-500">Actualice el estado por pieza dental.</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="text-xs text-gray-500 hidden sm:block">Guardado individual por pieza.</div>
        <a href="/patients/<?= e((string) $patientId) ?>" class="px-4 py-2 rounded-lg border text-sm">Volver</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3 bg-white shadow rounded-lg border border-gray-100 p-6">
        <div class="space-y-4">
            <?php foreach ($teethRows as $row): ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <?php foreach ($row as $tooth): ?>
                        <?php $data = $odontogram[$tooth] ?? []; ?>
                        <div class="border rounded-lg p-3 space-y-2 hover:border-shalom-primary/40 transition" data-tooth="<?= e($tooth) ?>">
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                <span>Pieza <?= e($tooth) ?></span>
                                <span class="text-xs text-gray-400" data-status-label></span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                                <select class="w-full border rounded px-2 py-1 text-sm tooth-status">
                                    <?php foreach ($statusOptions as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= ($data['tooth_status'] ?? 'healthy') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Superficies</label>
                                <input type="text" class="w-full border rounded px-2 py-1 text-sm tooth-surfaces" value="<?= e($data['surfaces'] ?? '') ?>" placeholder="O,M,D,V,L">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Movilidad</label>
                                    <select class="w-full border rounded px-2 py-1 text-sm tooth-mobility">
                                        <?php foreach (['0','I','II','III'] as $mobility): ?>
                                            <option value="<?= e($mobility) ?>" <?= ($data['mobility'] ?? '0') === $mobility ? 'selected' : '' ?>><?= e($mobility) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Periodontal</label>
                                    <select class="w-full border rounded px-2 py-1 text-sm tooth-periodontal">
                                        <?php foreach ($periodontalOptions as $value => $label): ?>
                                            <option value="<?= e($value) ?>" <?= ($data['periodontal_status'] ?? 'healthy') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                                <input type="text" class="w-full border rounded px-2 py-1 text-sm tooth-notes" value="<?= e($data['notes'] ?? '') ?>">
                            </div>
                            <button type="button" class="w-full mt-1 px-3 py-1.5 rounded bg-shalom-primary text-white text-xs save-tooth">Guardar</button>
                            <div class="text-xs text-green-600 hidden save-success">Guardado</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <aside class="space-y-4">
        <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Guia rapida</h3>
            <div class="space-y-2 text-xs text-gray-500">
                <div><span class="font-medium text-gray-700">Estado:</span> selecciona el diagnostico principal.</div>
                <div><span class="font-medium text-gray-700">Superficies:</span> usa O, M, D, V, L.</div>
                <div><span class="font-medium text-gray-700">Movilidad:</span> clasificacion 0 a III.</div>
                <div><span class="font-medium text-gray-700">Periodontal:</span> nivel de afectacion.</div>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ultimos cambios</h3>
            <?php if (!empty($history ?? [])): ?>
                <div class="space-y-2 text-xs">
                    <?php foreach (array_slice($history, 0, 6) as $entry): ?>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                            <div class="text-gray-600">
                                <span class="font-medium">Pieza <?= e($entry['tooth_number']) ?></span>
                                <span class="text-gray-400">→ <?= e($entry['new_status']) ?></span>
                            </div>
                            <div class="text-gray-400">
                                <?= e($entry['changed_at'] ?? '') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-xs text-gray-500">Sin cambios registrados.</div>
            <?php endif; ?>
        </div>
    </aside>
</div>

<div class="mt-6 bg-white shadow rounded-lg border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Historial de cambios</h3>
    <?php if (!empty($history ?? [])): ?>
        <div class="space-y-2 text-sm">
            <?php foreach ($history as $entry): ?>
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <div class="text-gray-700">
                        <span class="font-medium">Pieza <?= e($entry['tooth_number']) ?></span>
                        <span class="text-gray-500">→ <?= e($entry['new_status']) ?></span>
                    </div>
                    <div class="text-gray-500 text-xs">
                        <?= e($entry['changed_at'] ?? '') ?> · <?= e(trim(($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? ''))) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-sm text-gray-500">Sin cambios registrados.</div>
    <?php endif; ?>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const statusLabels = {
    healthy: 'Sano',
    decayed: 'Caries',
    filled: 'Restaurado',
    crowned: 'Corona',
    missing: 'Ausente',
    extracted: 'Extraido',
    impacted: 'Impactado',
    implant: 'Implante',
    bridge_pontic: 'Puente pontico',
    bridge_abutment: 'Puente pilar',
    root_canal: 'Endodoncia',
    prosthetic: 'Protesis',
    sealant: 'Sellante',
    veneer: 'Carilla',
    fracture: 'Fractura'
};

const updateStatusLabels = () => {
    document.querySelectorAll('[data-tooth]').forEach(card => {
        const status = card.querySelector('.tooth-status')?.value || 'healthy';
        const label = card.querySelector('[data-status-label]');
        if (label) {
            label.textContent = statusLabels[status] || '';
        }
    });
};

updateStatusLabels();
document.querySelectorAll('.tooth-status').forEach(select => {
    select.addEventListener('change', updateStatusLabels);
});

document.querySelectorAll('.save-tooth').forEach(btn => {
    btn.addEventListener('click', async () => {
        const card = btn.closest('[data-tooth]');
        const tooth = card.dataset.tooth;
        btn.disabled = true;
        btn.classList.add('opacity-70');
        const payload = {
            tooth_status: card.querySelector('.tooth-status')?.value || 'healthy',
            surfaces: card.querySelector('.tooth-surfaces')?.value || '',
            mobility: card.querySelector('.tooth-mobility')?.value || '0',
            periodontal_status: card.querySelector('.tooth-periodontal')?.value || 'healthy',
            notes: card.querySelector('.tooth-notes')?.value || ''
        };
        const res = await fetch(`/patients/<?= e((string) $patientId) ?>/odontogram/${tooth}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            const ok = card.querySelector('.save-success');
            ok?.classList.remove('hidden');
            setTimeout(() => ok?.classList.add('hidden'), 1200);
        }
        btn.disabled = false;
        btn.classList.remove('opacity-70');
    });
});
</script>

<?php $this->endSection(); ?>
