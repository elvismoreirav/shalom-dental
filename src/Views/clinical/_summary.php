<!-- Summary Tab - Patient Overview -->
<div class="space-y-4">
    <!-- Today's Appointment Info -->
    <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-700">Cita de Hoy</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-xs text-gray-500">Tipo</div>
                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($appointment['appointment_type_name']) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Hora</div>
                <div class="text-sm font-medium text-gray-900"><?= date('H:i', strtotime($appointment['scheduled_start_time'])) ?> - <?= date('H:i', strtotime($appointment['scheduled_end_time'])) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Duración</div>
                <div class="text-sm font-medium text-gray-900"><?= $appointment['duration_minutes'] ?> min</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Precio Base</div>
                <div class="text-sm font-medium text-gray-900">$<?= number_format($appointment['service_price'] ?? 0, 2) ?></div>
            </div>
        </div>
        <?php if (!empty($appointment['notes'])): ?>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <div class="text-xs text-gray-500 mb-1">Notas de la Cita</div>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($appointment['notes'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Medical Information -->
    <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-700">Información Médica</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-xs text-gray-500 mb-1">Alergias</div>
                <p class="text-sm text-gray-900 bg-gray-50 rounded p-2 min-h-[40px]">
                    <?= htmlspecialchars($appointment['patient_allergies'] ?: 'No registradas') ?>
                </p>
            </div>
            <div>
                <div class="text-xs text-gray-500 mb-1">Medicamentos Actuales</div>
                <p class="text-sm text-gray-900 bg-gray-50 rounded p-2 min-h-[40px]">
                    <?= htmlspecialchars($appointment['patient_medications'] ?: 'No registrados') ?>
                </p>
            </div>
            <div class="md:col-span-2">
                <div class="text-xs text-gray-500 mb-1">Condiciones Médicas</div>
                <p class="text-sm text-gray-900 bg-gray-50 rounded p-2 min-h-[40px]">
                    <?= htmlspecialchars($appointment['patient_conditions'] ?: 'No registradas') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Previous Clinical Notes -->
    <?php if (!empty($previousNotes)): ?>
    <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-700">Últimas Notas Clínicas</h3>
            </div>
            <a href="/clinical/patients/<?= $appointment['patient_id'] ?>/history" class="text-xs text-shalom-primary hover:underline">
                Ver todas
            </a>
        </div>
        <div class="space-y-3">
            <?php foreach (array_slice($previousNotes, 0, 3) as $note): ?>
            <div class="border border-gray-100 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($note['created_at'])) ?></span>
                    <span class="text-xs text-gray-500"><?= htmlspecialchars($note['professional']) ?></span>
                </div>
                <?php if (!empty($note['chief_complaint'])): ?>
                <p class="text-sm text-gray-700"><strong>Motivo:</strong> <?= htmlspecialchars($note['chief_complaint']) ?></p>
                <?php endif; ?>
                <?php if (!empty($note['assessment'])): ?>
                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars(substr($note['assessment'], 0, 150)) ?><?= strlen($note['assessment']) > 150 ? '...' : '' ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Active Treatment Plans Summary -->
    <?php if (!empty($treatmentPlans)): ?>
    <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-700">Planes de Tratamiento</h3>
            </div>
            <a href="/clinical/patients/<?= $appointment['patient_id'] ?>/treatment-plans" class="text-xs text-shalom-primary hover:underline">
                Ver todos
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                        <th class="pb-2 font-medium">Plan</th>
                        <th class="pb-2 font-medium">Estado</th>
                        <th class="pb-2 font-medium">Progreso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($treatmentPlans as $plan): ?>
                    <tr>
                        <td class="py-2 font-medium text-gray-900"><?= htmlspecialchars($plan['name']) ?></td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 rounded-full text-xs <?= $plan['status'] === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' ?>">
                                <?= ucfirst($plan['status']) ?>
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5 w-20">
                                    <div class="bg-shalom-primary h-1.5 rounded-full" style="width: <?= $plan['progress'] ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-500"><?= $plan['progress'] ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
