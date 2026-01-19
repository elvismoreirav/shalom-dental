<?php
/** @var array $appointment */
/** @var array $patients */
/** @var array $appointmentTypes */
/** @var array $professionals */
/** @var string $action */
/** @var string $method */
$appointment = $appointment ?? [];
?>
<?php $errors = getFlash('errors', []); ?>
<form action="<?= e($action ?? '/agenda') ?>" method="post" class="space-y-4">
    <?= csrf_field() ?>
    <?php if (!empty($method) && strtoupper($method) !== 'POST'): ?>
        <input type="hidden" name="_method" value="<?= e($method) ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Paciente *</label>
            <select name="patient_id" class="w-full border rounded-lg px-3 py-2 <?= isset($errors['patient_id']) ? 'border-red-400' : '' ?>" required>
                <option value="">Seleccionar</option>
                <?php foreach (($patients ?? []) as $patient): ?>
                    <?php $selected = (int) ($appointment['patient_id'] ?? 0) === (int) $patient['id']; ?>
                    <option value="<?= e((string) $patient['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                        <?= e($patient['last_name'] . ' ' . $patient['first_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['patient_id'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errors['patient_id']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Profesional *</label>
            <select name="professional_id" class="w-full border rounded-lg px-3 py-2 <?= isset($errors['professional_id']) ? 'border-red-400' : '' ?>" required>
                <option value="">Seleccionar</option>
                <?php foreach (($professionals ?? []) as $professional): ?>
                    <?php $selected = (int) ($appointment['professional_id'] ?? 0) === (int) $professional['id']; ?>
                    <option value="<?= e((string) $professional['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                        <?= e($professional['last_name'] . ' ' . $professional['first_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['professional_id'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errors['professional_id']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Tipo de cita *</label>
            <select name="appointment_type_id" class="w-full border rounded-lg px-3 py-2 <?= isset($errors['appointment_type_id']) ? 'border-red-400' : '' ?>" required>
                <option value="">Seleccionar</option>
                <?php foreach (($appointmentTypes ?? []) as $type): ?>
                    <?php $selected = (int) ($appointment['appointment_type_id'] ?? 0) === (int) $type['id']; ?>
                    <option value="<?= e((string) $type['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                        <?= e($type['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['appointment_type_id'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errors['appointment_type_id']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Estado</label>
            <?php $status = $appointment['status'] ?? 'scheduled'; ?>
            <select name="status" class="w-full border rounded-lg px-3 py-2">
                <?php foreach (['scheduled','confirmed','checked_in','in_progress','completed','cancelled','no_show','rescheduled','late'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Fecha *</label>
            <input type="date" name="scheduled_date" value="<?= e($appointment['scheduled_date'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 <?= isset($errors['scheduled_date']) ? 'border-red-400' : '' ?>" required>
            <?php if (!empty($errors['scheduled_date'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errors['scheduled_date']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Hora inicio *</label>
            <input type="time" name="scheduled_start_time" value="<?= e($appointment['scheduled_start_time'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 <?= isset($errors['scheduled_start_time']) ? 'border-red-400' : '' ?>" required>
            <?php if (!empty($errors['scheduled_start_time'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errors['scheduled_start_time']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Hora fin *</label>
            <input type="time" name="scheduled_end_time" value="<?= e($appointment['scheduled_end_time'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 <?= isset($errors['scheduled_end_time']) ? 'border-red-400' : '' ?>" required>
            <?php if (!empty($errors['scheduled_end_time'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errors['scheduled_end_time']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Duracion (min)</label>
            <input type="number" name="duration_minutes" value="<?= e((string) ($appointment['duration_minutes'] ?? 30)) ?>" class="w-full border rounded-lg px-3 py-2" min="5">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Notas</label>
            <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2"><?= e($appointment['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Guardar</button>
        <a href="/agenda" class="px-4 py-2 rounded-lg border text-sm">Cancelar</a>
    </div>
</form>
