<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php
$patientId = $patient['id'] ?? 0;
$patientName = $patient ? ($patient['first_name'] . ' ' . $patient['last_name']) : '';
?>

<div x-data="treatmentPlanCreator()" x-init="init()">
    <!-- Header -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="<?= $patient ? '/clinical/patients/' . (int) $patientId . '/treatment-plans' : '/patients' ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-900">Nuevo Plan de Tratamiento</h2>
            </div>
            <?php if ($patient): ?>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($patientName) ?></p>
            <?php else: ?>
            <p class="text-sm text-gray-500 mt-1">Selecciona un paciente para iniciar el plan.</p>
            <?php endif; ?>
        </div>
        <div class="text-sm text-gray-500">Completa la informacion base y agrega procedimientos.</div>
    </div>

    <form action="/clinical/treatment-plans" method="POST" @submit="prepareSubmit">
        <?= csrf_field() ?>
        <?php if ($patient): ?>
        <input type="hidden" name="patient_id" value="<?= (int) $patientId ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Plan Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Información del Plan</h3>
                    <p class="text-xs text-gray-500 mb-4">Define objetivos, estado inicial y expectativas de cierre.</p>

                    <?php if (!$patient): ?>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">
                            Paciente <span class="text-red-400">*</span>
                        </label>
                        <select name="patient_id" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                            <option value="">Seleccionar paciente...</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                Nombre del Plan <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="name" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                   placeholder="Ej: Plan de rehabilitación oral">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Estado Inicial</label>
                            <select name="status"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                <option value="draft">Borrador</option>
                                <option value="proposed">Propuesto</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Prioridad</label>
                            <select name="priority"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                <option value="normal">Normal</option>
                                <option value="low">Baja</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Fecha Estimada de Finalización</label>
                            <input type="date" name="estimated_completion_date"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Código (opcional)</label>
                            <input type="text" name="code"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                   placeholder="Se genera automáticamente">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Descripción</label>
                            <textarea name="description" rows="2"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                      placeholder="Descripción general del plan de tratamiento"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Notas</label>
                            <textarea name="notes" rows="2"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                      placeholder="Notas adicionales o instrucciones especiales"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Plan Items -->
                <div class="bg-white shadow rounded-lg border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700">Procedimientos del Plan</h3>
                            <p class="text-xs text-gray-500">Agrega servicios, fases y piezas asociadas.</p>
                        </div>
                        <span class="text-xs text-gray-500" x-text="items.length + ' items'"></span>
                    </div>

                    <!-- Items List -->
                    <div class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-500 mb-1">Servicio</label>
                                            <select x-model="item.appointment_type_id" @change="onServiceChange(item)"
                                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($categories ?? [] as $cat): ?>
                                                <optgroup label="<?= htmlspecialchars($cat['name']) ?>">
                                                    <?php foreach ($services ?? [] as $svc): ?>
                                                    <?php if (($svc['category_id'] ?? null) == $cat['id']): ?>
                                                    <option value="<?= $svc['id'] ?>"
                                                            data-price="<?= $svc['price_default'] ?? 0 ?>"
                                                            data-teeth="<?= $svc['applies_to_teeth'] ? '1' : '0' ?>">
                                                        <?= htmlspecialchars($svc['code'] . ' - ' . $svc['name']) ?>
                                                    </option>
                                                    <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <?php endforeach; ?>
                                                <!-- Services without category -->
                                                <?php
                                                $noCategory = array_filter($services ?? [], fn($s) => empty($s['category_id']));
                                                if (!empty($noCategory)):
                                                ?>
                                                <optgroup label="Sin categoría">
                                                    <?php foreach ($noCategory as $svc): ?>
                                                    <option value="<?= $svc['id'] ?>"
                                                            data-price="<?= $svc['price_default'] ?? 0 ?>"
                                                            data-teeth="<?= $svc['applies_to_teeth'] ? '1' : '0' ?>">
                                                        <?= htmlspecialchars($svc['code'] . ' - ' . $svc['name']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Pieza</label>
                            <input type="text" x-model="item.tooth_number"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                   placeholder="Ej: 11"
                                   :disabled="item.requires_teeth === false">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Precio Est.</label>
                                            <input type="number" step="0.01" x-model="item.estimated_price"
                                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-500 mb-1">Fase</label>
                                            <input type="text" x-model="item.phase"
                                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                                   placeholder="Ej: Fase 1 - Diagnóstico">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Superficies</label>
                                            <input type="text" x-model="item.surfaces"
                                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                                   placeholder="Ej: O,M,D">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                                            <input type="text" x-model="item.description"
                                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                                   placeholder="Notas adicionales">
                                        </div>
                                    </div>
                                    <button type="button" @click="removeItem(index)"
                                            class="text-red-400 hover:text-red-600 p-1 mt-5 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="items.length === 0">
                            <div class="p-8 text-center text-gray-500 text-sm">
                                No hay procedimientos agregados. Click en "Agregar Item" para comenzar.
                            </div>
                        </template>
                    </div>

                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        <button type="button" @click="addItem()"
                                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Agregar Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="bg-white shadow rounded-lg border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Resumen del Plan</h3>
                    <p class="text-xs text-gray-500 mb-4">Valida antes de crear el plan.</p>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Items</span>
                            <span class="font-medium" x-text="items.length"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Fases detectadas</span>
                            <span class="font-medium" x-text="phaseCount"></span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-gray-100">
                            <span class="text-gray-700 font-medium">Total Estimado</span>
                            <span class="font-semibold text-lg text-shalom-primary">$<span x-text="totalEstimated.toFixed(2)"></span></span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 space-y-3">
                        <button type="submit"
                                class="w-full px-4 py-3 bg-shalom-primary text-white text-sm font-medium rounded-lg hover:bg-shalom-dark transition-colors">
                            Crear Plan de Tratamiento
                        </button>
                        <a href="<?= $patient ? '/clinical/patients/' . (int) $patientId . '/treatment-plans' : '/patients' ?>"
                           class="block w-full px-4 py-2 border border-gray-200 text-center text-sm rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                    </div>
                </div>

                <!-- Quick Add Services -->
                <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Agregar Rápido</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <?php foreach ($services ?? [] as $svc): ?>
                        <button type="button" @click="quickAddService(<?= $svc['id'] ?>, '<?= htmlspecialchars($svc['name'], ENT_QUOTES) ?>', <?= $svc['price_default'] ?? 0 ?>)"
                                class="w-full text-left p-2 border border-gray-100 rounded-lg hover:border-shalom-primary hover:bg-shalom-light/30 transition-colors text-sm">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-gray-900"><?= htmlspecialchars($svc['name']) ?></span>
                                    <span class="text-xs text-gray-400 ml-1"><?= htmlspecialchars($svc['code'] ?? '') ?></span>
                                </div>
                                <span class="text-shalom-primary font-medium">$<?= number_format($svc['price_default'] ?? 0, 2) ?></span>
                            </div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden items data -->
        <div x-ref="itemsContainer"></div>
    </form>
</div>

<script>
function treatmentPlanCreator() {
    return {
        items: [],

        get totalEstimated() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.estimated_price) || 0), 0);
        },

        get phaseCount() {
            const phases = this.items
                .map(item => (item.phase || '').trim())
                .filter(phase => phase.length > 0);
            return new Set(phases).size || 0;
        },

        init() {
            // Add first empty item
            this.addItem();
        },

        addItem() {
            this.items.push({
                appointment_type_id: '',
                tooth_number: '',
                surfaces: '',
                phase: '',
                description: '',
                estimated_price: 0,
                requires_teeth: true
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        quickAddService(id, name, price) {
            this.items.push({
                appointment_type_id: id,
                tooth_number: '',
                surfaces: '',
                phase: '',
                description: '',
                estimated_price: price,
                requires_teeth: true
            });
        },

        onServiceChange(item) {
            const select = event.target;
            const option = select.options[select.selectedIndex];
            if (option && option.dataset.price) {
                item.estimated_price = parseFloat(option.dataset.price) || 0;
            }
            item.requires_teeth = option ? option.dataset.teeth === '1' : true;
            if (item.requires_teeth === false) {
                item.tooth_number = '';
            }
        },

        prepareSubmit() {
            // Create hidden inputs for items
            const container = this.$refs.itemsContainer;
            container.innerHTML = '';

            this.items.forEach((item, index) => {
                if (!item.appointment_type_id) return;

                Object.keys(item).forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `items[${index}][${key}]`;
                    input.value = item[key] || '';
                    container.appendChild(input);
                });
            });
        }
    }
}
</script>

<?php $this->endSection(); ?>
