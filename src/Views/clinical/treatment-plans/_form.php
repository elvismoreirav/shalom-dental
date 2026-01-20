<?php
$plan = $plan ?? [];
$items = $items ?? [];
$appointmentTypes = $appointmentTypes ?? [];
$action = $action ?? '';
$method = $method ?? 'POST';
?>

<form action="<?= e($action) ?>" method="post" class="space-y-6">
    <?= csrf_field() ?>
    <?php if (strtoupper($method) !== 'POST'): ?>
        <input type="hidden" name="_method" value="<?= e($method) ?>">
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos del plan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">Código</label>
                <input type="text" name="code" value="<?= e($plan['code'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Nombre *</label>
                <input type="text" name="name" value="<?= e($plan['name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Estado</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2">
                    <?php foreach (['draft','proposed','accepted','in_progress','completed','cancelled','on_hold'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($plan['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Prioridad</label>
                <select name="priority" class="w-full border rounded-lg px-3 py-2">
                    <?php foreach (['low','normal','high','urgent'] as $priority): ?>
                        <option value="<?= e($priority) ?>" <?= ($plan['priority'] ?? 'normal') === $priority ? 'selected' : '' ?>><?= e($priority) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Fecha estimada de cierre</label>
                <input type="date" name="estimated_completion_date" value="<?= e($plan['estimated_completion_date'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Descripción</label>
                <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($plan['description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Notas</label>
                <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($plan['notes'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Observaciones del paciente</label>
                <textarea name="patient_observations" rows="2" class="w-full border rounded-lg px-3 py-2"><?= e($plan['patient_observations'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Procedimientos</h3>
            <button type="button" id="addPlanItem" class="px-3 py-2 rounded-lg bg-shalom-primary text-white text-sm">Agregar item</button>
        </div>
        <div class="space-y-3" id="planItems">
            <?php $index = 0; ?>
            <?php foreach ($items as $item): ?>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-2 border rounded-lg p-3 plan-item">
                    <select name="items[<?= $index ?>][appointment_type_id]" class="border rounded px-2 py-1">
                        <option value="">Servicio</option>
                        <?php foreach ($appointmentTypes as $type): ?>
                            <option value="<?= e((string) $type['id']) ?>" <?= (int) ($item['appointment_type_id'] ?? 0) === (int) $type['id'] ? 'selected' : '' ?>><?= e($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="items[<?= $index ?>][tooth_number]" placeholder="Pieza" value="<?= e($item['tooth_number'] ?? '') ?>" class="border rounded px-2 py-1">
                    <input type="text" name="items[<?= $index ?>][surfaces]" placeholder="Superficies" value="<?= e($item['surfaces'] ?? '') ?>" class="border rounded px-2 py-1">
                    <input type="number" step="0.01" name="items[<?= $index ?>][estimated_price]" placeholder="Precio" value="<?= e((string) ($item['estimated_price'] ?? 0)) ?>" class="border rounded px-2 py-1">
                    <select name="items[<?= $index ?>][status]" class="border rounded px-2 py-1">
                        <?php foreach (['pending','scheduled','in_progress','completed','cancelled'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($item['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="remove-item px-2 py-1 border rounded text-sm">Quitar</button>
                </div>
                <?php $index++; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/patients/<?= e((string) $patientId) ?>/treatment-plans" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>

<script>
const planItems = document.getElementById('planItems');
const addPlanItem = document.getElementById('addPlanItem');
let itemIndex = <?= (int) $index ?>;
addPlanItem?.addEventListener('click', () => {
    const wrapper = document.createElement('div');
    wrapper.className = 'grid grid-cols-1 md:grid-cols-6 gap-2 border rounded-lg p-3 plan-item';
    wrapper.innerHTML = `
        <select name="items[${itemIndex}][appointment_type_id]" class="border rounded px-2 py-1">
            <option value="">Servicio</option>
            <?php foreach ($appointmentTypes as $type): ?>
                <option value="<?= e((string) $type['id']) ?>"><?= e($type['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="items[${itemIndex}][tooth_number]" placeholder="Pieza" class="border rounded px-2 py-1">
        <input type="text" name="items[${itemIndex}][surfaces]" placeholder="Superficies" class="border rounded px-2 py-1">
        <input type="number" step="0.01" name="items[${itemIndex}][estimated_price]" placeholder="Precio" class="border rounded px-2 py-1">
        <select name="items[${itemIndex}][status]" class="border rounded px-2 py-1">
            <option value="pending">pending</option>
            <option value="scheduled">scheduled</option>
            <option value="in_progress">in_progress</option>
            <option value="completed">completed</option>
            <option value="cancelled">cancelled</option>
        </select>
        <button type="button" class="remove-item px-2 py-1 border rounded text-sm">Quitar</button>
    `;
    planItems?.appendChild(wrapper);
    itemIndex++;
});

planItems?.addEventListener('click', (event) => {
    if (event.target.classList.contains('remove-item')) {
        event.target.closest('.plan-item')?.remove();
    }
});
</script>
