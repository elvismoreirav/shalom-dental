<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div x-data="{ activeTab: 'notes' }">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <a href="/patients/<?= (int) $patient['id'] ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-900">Historial Clínico</h2>
            </div>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/clinical/patients/<?= (int) $patient['id'] ?>/treatment-plans" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                Ver Planes
            </a>
        </div>
    </div>

    <!-- Patient Summary Card -->
    <div class="bg-white shadow rounded-lg border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-xs text-gray-500">Identificación</div>
                <div class="font-medium text-gray-900"><?= htmlspecialchars(($patient['id_type'] ?? '') . ': ' . ($patient['id_number'] ?? '-')) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Total Citas</div>
                <div class="font-medium text-gray-900"><?= (int) ($patient['total_appointments'] ?? 0) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Última Visita</div>
                <div class="font-medium text-gray-900"><?= $patient['last_visit'] ? date('d/m/Y', strtotime($patient['last_visit'])) : '-' ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Tipo de Sangre</div>
                <div class="font-medium text-gray-900"><?= htmlspecialchars($patient['blood_type'] ?? '-') ?></div>
            </div>
        </div>

        <?php if (!empty($patient['allergies']) || !empty($patient['medical_conditions']) || !empty($patient['current_medications'])): ?>
        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
            <?php if (!empty($patient['allergies'])): ?>
            <div class="flex items-start gap-2 text-sm">
                <span class="inline-flex items-center px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-medium">Alergias</span>
                <span class="text-gray-700"><?= htmlspecialchars($patient['allergies']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($patient['medical_conditions'])): ?>
            <div class="flex items-start gap-2 text-sm">
                <span class="inline-flex items-center px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs font-medium">Condiciones</span>
                <span class="text-gray-700"><?= htmlspecialchars($patient['medical_conditions']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($patient['current_medications'])): ?>
            <div class="flex items-start gap-2 text-sm">
                <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium">Medicamentos</span>
                <span class="text-gray-700"><?= htmlspecialchars($patient['current_medications']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-6 -mb-px">
            <button @click="activeTab = 'notes'"
                    :class="activeTab === 'notes' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                Notas Clínicas
                <span class="ml-1.5 px-1.5 py-0.5 bg-gray-100 text-gray-600 text-xs rounded"><?= count($clinicalNotes) ?></span>
            </button>
            <button @click="activeTab = 'odontogram'"
                    :class="activeTab === 'odontogram' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                Odontograma
            </button>
            <button @click="activeTab = 'plans'"
                    :class="activeTab === 'plans' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                Planes de Tratamiento
                <span class="ml-1.5 px-1.5 py-0.5 bg-gray-100 text-gray-600 text-xs rounded"><?= count($treatmentPlans) ?></span>
            </button>
            <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                Historial de Cambios
            </button>
        </nav>
    </div>

    <!-- Tab Content: Clinical Notes -->
    <div x-show="activeTab === 'notes'" x-transition:enter class="space-y-4">
        <?php if (empty($clinicalNotes)): ?>
        <div class="bg-white shadow rounded-lg border border-gray-100 p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500">No hay notas clínicas registradas</p>
        </div>
        <?php else: ?>
        <?php foreach ($clinicalNotes as $note): ?>
        <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-900">
                        <?= $note['scheduled_date'] ? date('d/m/Y', strtotime($note['scheduled_date'])) : date('d/m/Y', strtotime($note['created_at'])) ?>
                    </span>
                    <?php
                    $statusColors = [
                        'draft' => 'bg-gray-100 text-gray-600',
                        'signed' => 'bg-green-100 text-green-700',
                        'amended' => 'bg-yellow-100 text-yellow-700',
                    ];
                    $statusLabels = [
                        'draft' => 'Borrador',
                        'signed' => 'Firmada',
                        'amended' => 'Enmendada',
                    ];
                    $statusClass = $statusColors[$note['status']] ?? 'bg-gray-100 text-gray-600';
                    $statusLabel = $statusLabels[$note['status']] ?? $note['status'];
                    ?>
                    <span class="px-2 py-0.5 text-xs font-medium rounded <?= $statusClass ?>"><?= $statusLabel ?></span>
                </div>
                <span class="text-xs text-gray-500"><?= htmlspecialchars($note['professional_name'] ?? '') ?></span>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <?php if (!empty($note['chief_complaint'])): ?>
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Motivo de consulta</div>
                    <div class="text-gray-900"><?= nl2br(htmlspecialchars($note['chief_complaint'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($note['subjective'])): ?>
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Subjetivo</div>
                    <div class="text-gray-700"><?= nl2br(htmlspecialchars($note['subjective'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($note['objective'])): ?>
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Objetivo</div>
                    <div class="text-gray-700"><?= nl2br(htmlspecialchars($note['objective'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($note['assessment'])): ?>
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Evaluación / Diagnóstico</div>
                    <div class="text-gray-900 font-medium"><?= nl2br(htmlspecialchars($note['assessment'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($note['plan'])): ?>
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Plan</div>
                    <div class="text-gray-700"><?= nl2br(htmlspecialchars($note['plan'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tab Content: Odontogram -->
    <div x-show="activeTab === 'odontogram'" x-transition:enter>
        <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
            <?php if (empty($odontogram)): ?>
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <p class="text-gray-500">No hay datos de odontograma registrados</p>
            </div>
            <?php else: ?>
            <!-- Odontogram Grid -->
            <div class="space-y-6">
                <!-- Upper Jaw -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Maxilar Superior</h3>
                    <div class="flex justify-center gap-1">
                        <?php
                        $upperTeeth = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
                        foreach ($upperTeeth as $tooth):
                            $toothData = array_filter($odontogram, fn($t) => $t['tooth_number'] == $tooth);
                            $toothData = reset($toothData) ?: null;
                            $status = $toothData['tooth_status'] ?? 'healthy';
                            $statusColors = [
                                'healthy' => 'bg-green-100 border-green-300 text-green-700',
                                'caries' => 'bg-red-100 border-red-300 text-red-700',
                                'filled' => 'bg-blue-100 border-blue-300 text-blue-700',
                                'extracted' => 'bg-gray-200 border-gray-400 text-gray-500',
                                'crown' => 'bg-yellow-100 border-yellow-300 text-yellow-700',
                                'implant' => 'bg-purple-100 border-purple-300 text-purple-700',
                                'root_canal' => 'bg-orange-100 border-orange-300 text-orange-700',
                            ];
                            $colorClass = $statusColors[$status] ?? 'bg-white border-gray-200 text-gray-600';
                        ?>
                        <div class="w-8 h-10 flex flex-col items-center justify-center border rounded text-xs font-medium <?= $colorClass ?>" title="<?= $tooth ?>: <?= ucfirst($status) ?>">
                            <span><?= $tooth ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Lower Jaw -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Maxilar Inferior</h3>
                    <div class="flex justify-center gap-1">
                        <?php
                        $lowerTeeth = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];
                        foreach ($lowerTeeth as $tooth):
                            $toothData = array_filter($odontogram, fn($t) => $t['tooth_number'] == $tooth);
                            $toothData = reset($toothData) ?: null;
                            $status = $toothData['tooth_status'] ?? 'healthy';
                            $colorClass = $statusColors[$status] ?? 'bg-white border-gray-200 text-gray-600';
                        ?>
                        <div class="w-8 h-10 flex flex-col items-center justify-center border rounded text-xs font-medium <?= $colorClass ?>" title="<?= $tooth ?>: <?= ucfirst($status) ?>">
                            <span><?= $tooth ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-6 pt-4 border-t border-gray-100">
                <div class="flex flex-wrap justify-center gap-4 text-xs">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span> Sano</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-300"></span> Caries</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 border border-blue-300"></span> Restauración</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-200 border border-gray-400"></span> Extraído</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-300"></span> Corona</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-100 border border-purple-300"></span> Implante</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-orange-100 border border-orange-300"></span> Endodoncia</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab Content: Treatment Plans -->
    <div x-show="activeTab === 'plans'" x-transition:enter>
        <?php if (empty($treatmentPlans)): ?>
        <div class="bg-white shadow rounded-lg border border-gray-100 p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500">No hay planes de tratamiento</p>
            <a href="/clinical/treatment-plans/create?patient_id=<?= (int) $patient['id'] ?>" class="inline-block mt-4 px-4 py-2 bg-shalom-primary text-white text-sm rounded-lg hover:bg-shalom-dark transition-colors">
                Crear Plan
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($treatmentPlans as $plan): ?>
            <?php
            $statusColors = [
                'draft' => 'bg-gray-100 text-gray-600',
                'proposed' => 'bg-blue-100 text-blue-700',
                'accepted' => 'bg-green-100 text-green-700',
                'in_progress' => 'bg-yellow-100 text-yellow-700',
                'completed' => 'bg-emerald-100 text-emerald-700',
                'cancelled' => 'bg-red-100 text-red-700',
                'on_hold' => 'bg-orange-100 text-orange-700',
            ];
            $statusLabels = [
                'draft' => 'Borrador',
                'proposed' => 'Propuesto',
                'accepted' => 'Aceptado',
                'in_progress' => 'En Progreso',
                'completed' => 'Completado',
                'cancelled' => 'Cancelado',
                'on_hold' => 'En Espera',
            ];
            $statusClass = $statusColors[$plan['status']] ?? 'bg-gray-100 text-gray-600';
            $statusLabel = $statusLabels[$plan['status']] ?? $plan['status'];
            $progress = $plan['total_items'] > 0 ? round(($plan['completed_items'] / $plan['total_items']) * 100) : 0;
            ?>
            <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($plan['name']) ?></span>
                        <span class="px-2 py-0.5 text-xs font-medium rounded <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </div>
                    <a href="/clinical/treatment-plans/<?= (int) $plan['id'] ?>" class="text-shalom-primary text-sm hover:underline">Ver detalle</a>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-4 text-sm mb-3">
                        <div>
                            <div class="text-xs text-gray-500">Items</div>
                            <div class="font-medium"><?= (int) $plan['completed_items'] ?> / <?= (int) $plan['total_items'] ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Estimado</div>
                            <div class="font-medium">$<?= number_format((float) ($plan['total_estimated'] ?? 0), 2) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Facturado</div>
                            <div class="font-medium">$<?= number_format((float) ($plan['total_invoiced'] ?? 0), 2) ?></div>
                        </div>
                    </div>
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-shalom-primary h-2 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1"><?= $progress ?>% completado</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tab Content: Odontogram History -->
    <div x-show="activeTab === 'history'" x-transition:enter>
        <?php if (empty($odontogramHistory)): ?>
        <div class="bg-white shadow rounded-lg border border-gray-100 p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-500">No hay historial de cambios</p>
        </div>
        <?php else: ?>
        <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-100">
                <?php foreach ($odontogramHistory as $entry): ?>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-gray-900">Pieza <?= htmlspecialchars($entry['tooth_number']) ?></span>
                                <?php if ($entry['previous_status'] && $entry['new_status']): ?>
                                <span class="text-xs text-gray-500">
                                    <?= htmlspecialchars(ucfirst($entry['previous_status'])) ?>
                                    <svg class="w-3 h-3 inline mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    <?= htmlspecialchars(ucfirst($entry['new_status'])) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($entry['notes'])): ?>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($entry['notes']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-1">
                                Por <?= htmlspecialchars($entry['changed_by_name']) ?>
                                <?php if ($entry['scheduled_date']): ?>
                                - Cita del <?= date('d/m/Y', strtotime($entry['scheduled_date'])) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="text-xs text-gray-400">
                            <?= date('d/m/Y H:i', strtotime($entry['changed_at'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
