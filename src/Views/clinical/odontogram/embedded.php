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
$statusClasses = [
    'healthy' => 'bg-green-50 text-green-700 border-green-200',
    'decayed' => 'bg-red-50 text-red-700 border-red-200',
    'filled' => 'bg-amber-50 text-amber-700 border-amber-200',
    'crowned' => 'bg-blue-50 text-blue-700 border-blue-200',
    'missing' => 'bg-gray-100 text-gray-600 border-gray-200',
    'extracted' => 'bg-gray-100 text-gray-600 border-gray-200',
    'implant' => 'bg-purple-50 text-purple-700 border-purple-200',
];
?>

<div class="bg-white shadow rounded-lg border border-gray-100 p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Odontograma</h3>
            <p class="text-xs text-gray-500">Actualice estado, superficies y notas por pieza.</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-green-50 text-green-700">Sano</span>
            <span class="px-2 py-1 rounded-full bg-red-50 text-red-700">Caries</span>
            <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-700">Restaurado</span>
            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-600">Ausente</span>
        </div>
    </div>
    <div class="mb-6">
        <h4 class="text-xs font-semibold text-gray-500 mb-2">Mapa rápido</h4>
        <div class="grid grid-cols-8 gap-2">
            <?php foreach ($teethRows as $row): ?>
                <?php foreach ($row as $tooth): ?>
                    <?php $data = ($odontogram ?? [])[$tooth] ?? []; ?>
                    <?php $status = $data['tooth_status'] ?? 'healthy'; ?>
                    <?php $class = $statusClasses[$status] ?? 'bg-gray-50 text-gray-600 border-gray-200'; ?>
                    <button type="button" class="tooth-chip text-[10px] border rounded-md px-1.5 py-1 <?= $class ?>" data-tooth="<?= e($tooth) ?>" title="Pieza <?= e($tooth) ?>">
                        <?= e($tooth) ?>
                    </button>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="space-y-4">
        <?php foreach ($teethRows as $row): ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <?php foreach ($row as $tooth): ?>
                    <?php $data = ($odontogram ?? [])[$tooth] ?? []; ?>
                    <div class="border rounded-lg p-3 space-y-2 hover:border-shalom-primary/40 transition" data-tooth="<?= e($tooth) ?>">
                        <div class="text-sm font-semibold text-gray-700">Pieza <?= e($tooth) ?></div>
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
                            <p class="text-[10px] text-gray-400 mt-1">Use O,M,D,V,L</p>
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
const odontogramCsrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
document.querySelectorAll('.save-tooth').forEach(btn => {
    btn.addEventListener('click', async () => {
        const card = btn.closest('[data-tooth]');
        const tooth = card.dataset.tooth;
        const payload = {
            tooth_status: card.querySelector('.tooth-status')?.value || 'healthy',
            surfaces: card.querySelector('.tooth-surfaces')?.value || '',
            mobility: card.querySelector('.tooth-mobility')?.value || '0',
            periodontal_status: card.querySelector('.tooth-periodontal')?.value || 'healthy',
            notes: card.querySelector('.tooth-notes')?.value || ''
        };
        const res = await fetch(`/patients/<?= e((string) ($patientId ?? 0)) ?>/odontogram/${tooth}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': odontogramCsrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            const ok = card.querySelector('.save-success');
            ok?.classList.remove('hidden');
            setTimeout(() => ok?.classList.add('hidden'), 1200);
        }
    });
});

document.querySelectorAll('.tooth-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        const tooth = chip.dataset.tooth;
        const card = document.querySelector(`[data-tooth="${tooth}"]`);
        card?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card?.classList.add('ring-2', 'ring-shalom-primary/50');
        setTimeout(() => card?.classList.remove('ring-2', 'ring-shalom-primary/50'), 1200);
    });
});
</script>
