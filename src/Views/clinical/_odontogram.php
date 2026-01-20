<!-- Odontogram Tab - Interactive Dental Chart -->
<div class="bg-white shadow rounded-lg border border-gray-100">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            Odontograma
        </h2>
        <div class="flex items-center gap-2">
            <button @click="odontogramMode = 'permanent'"
                    :class="odontogramMode === 'permanent' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-3 py-1 text-xs font-medium rounded-lg transition-colors">
                Permanentes
            </button>
            <button @click="odontogramMode = 'deciduous'"
                    :class="odontogramMode === 'deciduous' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-3 py-1 text-xs font-medium rounded-lg transition-colors">
                Deciduos
            </button>
        </div>
    </div>

    <div class="p-4" x-data="odontogramComponent()" x-init="loadOdontogram()">
        <!-- Legend -->
        <div class="mb-4 flex flex-wrap gap-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500"></span> Sano</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-500"></span> Caries</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> Restaurado</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-500"></span> Corona</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-400"></span> Ausente</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-500"></span> Endodoncia</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-pink-500"></span> Implante</span>
        </div>

        <!-- Odontogram Chart -->
        <div id="odontogram-container" class="relative">
            <!-- Permanent Teeth -->
            <div x-show="odontogramMode === 'permanent'" class="space-y-6">
                <!-- Upper Arch -->
                <div class="text-center text-xs text-gray-500 mb-2">Arcada Superior</div>
                <div class="flex justify-center gap-1">
                    <!-- Upper Right (18-11) -->
                    <template x-for="tooth in ['18','17','16','15','14','13','12','11']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-8 h-10 md:w-10 md:h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105"
                             :title="getToothInfo(tooth)">
                            <span x-text="tooth"></span>
                            <div class="w-4 h-4 mt-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                        </div>
                    </template>
                    <div class="w-4"></div>
                    <!-- Upper Left (21-28) -->
                    <template x-for="tooth in ['21','22','23','24','25','26','27','28']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-8 h-10 md:w-10 md:h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105"
                             :title="getToothInfo(tooth)">
                            <span x-text="tooth"></span>
                            <div class="w-4 h-4 mt-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                        </div>
                    </template>
                </div>

                <!-- Lower Arch -->
                <div class="flex justify-center gap-1">
                    <!-- Lower Right (48-41) -->
                    <template x-for="tooth in ['48','47','46','45','44','43','42','41']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-8 h-10 md:w-10 md:h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105"
                             :title="getToothInfo(tooth)">
                            <div class="w-4 h-4 mb-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                            <span x-text="tooth"></span>
                        </div>
                    </template>
                    <div class="w-4"></div>
                    <!-- Lower Left (31-38) -->
                    <template x-for="tooth in ['31','32','33','34','35','36','37','38']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-8 h-10 md:w-10 md:h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105"
                             :title="getToothInfo(tooth)">
                            <div class="w-4 h-4 mb-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                            <span x-text="tooth"></span>
                        </div>
                    </template>
                </div>
                <div class="text-center text-xs text-gray-500 mt-2">Arcada Inferior</div>
            </div>

            <!-- Deciduous Teeth -->
            <div x-show="odontogramMode === 'deciduous'" class="space-y-6">
                <!-- Upper Arch -->
                <div class="text-center text-xs text-gray-500 mb-2">Arcada Superior (Deciduos)</div>
                <div class="flex justify-center gap-1">
                    <template x-for="tooth in ['55','54','53','52','51']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-10 h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105">
                            <span x-text="tooth"></span>
                            <div class="w-4 h-4 mt-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                        </div>
                    </template>
                    <div class="w-4"></div>
                    <template x-for="tooth in ['61','62','63','64','65']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-10 h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105">
                            <span x-text="tooth"></span>
                            <div class="w-4 h-4 mt-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                        </div>
                    </template>
                </div>

                <!-- Lower Arch -->
                <div class="flex justify-center gap-1">
                    <template x-for="tooth in ['85','84','83','82','81']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-10 h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105">
                            <div class="w-4 h-4 mb-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                            <span x-text="tooth"></span>
                        </div>
                    </template>
                    <div class="w-4"></div>
                    <template x-for="tooth in ['71','72','73','74','75']" :key="tooth">
                        <div @click="selectTooth(tooth)"
                             :class="getToothClass(tooth)"
                             class="w-10 h-12 rounded cursor-pointer flex flex-col items-center justify-center text-xs font-medium border-2 transition-all hover:scale-105">
                            <div class="w-4 h-4 mb-0.5 rounded-sm" :class="getToothStatusColor(tooth)"></div>
                            <span x-text="tooth"></span>
                        </div>
                    </template>
                </div>
                <div class="text-center text-xs text-gray-500 mt-2">Arcada Inferior (Deciduos)</div>
            </div>
        </div>

        <!-- Tooth Edit Panel -->
        <div x-show="selectedTooth" x-cloak class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Pieza <span x-text="selectedTooth"></span></h4>
                <button @click="selectedTooth = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado</label>
                    <select x-model="toothEdit.status"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                        <option value="healthy">Sano</option>
                        <option value="decayed">Caries</option>
                        <option value="filled">Restaurado</option>
                        <option value="crowned">Corona</option>
                        <option value="missing">Ausente</option>
                        <option value="extracted">Extraído</option>
                        <option value="impacted">Impactado</option>
                        <option value="implant">Implante</option>
                        <option value="root_canal">Endodoncia</option>
                        <option value="sealant">Sellante</option>
                        <option value="fracture">Fracturado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Movilidad</label>
                    <select x-model="toothEdit.mobility"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                        <option value="0">Grado 0</option>
                        <option value="I">Grado I</option>
                        <option value="II">Grado II</option>
                        <option value="III">Grado III</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado Periodontal</label>
                    <select x-model="toothEdit.periodontal_status"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                        <option value="healthy">Sano</option>
                        <option value="gingivitis">Gingivitis</option>
                        <option value="periodontitis_mild">Periodontitis Leve</option>
                        <option value="periodontitis_moderate">Periodontitis Moderada</option>
                        <option value="periodontitis_severe">Periodontitis Severa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Superficies</label>
                    <div class="flex gap-1">
                        <template x-for="surface in ['O','M','D','V','L']" :key="surface">
                            <button @click="toggleSurface(surface)"
                                    :class="toothEdit.surfaces && toothEdit.surfaces[surface] ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                    class="w-7 h-7 rounded text-xs font-medium transition-colors"
                                    x-text="surface">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                <input type="text" x-model="toothEdit.notes"
                       class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                       placeholder="Observaciones de la pieza...">
            </div>

            <div class="mt-3 flex justify-end gap-2">
                <button @click="selectedTooth = null"
                        class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button @click="saveTooth()"
                        class="px-3 py-1.5 text-sm text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark transition-colors">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function odontogramComponent() {
    return {
        odontogramMode: 'permanent',
        teethData: {},
        selectedTooth: null,
        toothEdit: {
            status: 'healthy',
            mobility: '0',
            periodontal_status: 'healthy',
            surfaces: {},
            notes: ''
        },

        async loadOdontogram() {
            try {
                const response = await fetch(`/api/clinical/patients/${this.$root.patientId}/odontogram`);
                const data = await response.json();
                if (data.success) {
                    this.teethData = data.data.teeth || {};
                }
            } catch (e) {
                console.error('Error loading odontogram:', e);
            }
        },

        selectTooth(toothNumber) {
            this.selectedTooth = toothNumber;
            const tooth = this.teethData[toothNumber];
            if (tooth) {
                this.toothEdit = {
                    status: tooth.status || 'healthy',
                    mobility: tooth.mobility || '0',
                    periodontal_status: tooth.periodontal_status || 'healthy',
                    surfaces: tooth.surfaces || {},
                    notes: tooth.notes || ''
                };
            } else {
                this.toothEdit = {
                    status: 'healthy',
                    mobility: '0',
                    periodontal_status: 'healthy',
                    surfaces: {},
                    notes: ''
                };
            }
        },

        toggleSurface(surface) {
            if (!this.toothEdit.surfaces) this.toothEdit.surfaces = {};
            if (this.toothEdit.surfaces[surface]) {
                delete this.toothEdit.surfaces[surface];
            } else {
                this.toothEdit.surfaces[surface] = this.toothEdit.status;
            }
        },

        async saveTooth() {
            try {
                const response = await fetch(`/api/clinical/patients/${this.$root.patientId}/odontogram/${this.selectedTooth}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.$root.csrfToken
                    },
                    body: JSON.stringify({
                        ...this.toothEdit,
                        appointment_id: this.$root.appointmentId
                    })
                });
                const data = await response.json();

                if (data.success) {
                    this.teethData[this.selectedTooth] = { ...this.toothEdit };
                    this.$root.showToast('Pieza actualizada');
                    this.selectedTooth = null;
                } else {
                    this.$root.showToast(data.message || 'Error al guardar', 'error');
                }
            } catch (e) {
                this.$root.showToast('Error de conexión', 'error');
            }
        },

        getToothClass(tooth) {
            const data = this.teethData[tooth];
            let classes = 'bg-white border-gray-200';

            if (this.selectedTooth === tooth) {
                classes = 'bg-shalom-light border-shalom-primary';
            } else if (data && data.status !== 'healthy') {
                classes = 'bg-gray-50 border-gray-300';
            }

            return classes;
        },

        getToothStatusColor(tooth) {
            const data = this.teethData[tooth];
            if (!data) return 'bg-green-500';

            return {
                'healthy': 'bg-green-500',
                'decayed': 'bg-red-500',
                'filled': 'bg-blue-500',
                'crowned': 'bg-yellow-500',
                'missing': 'bg-gray-400',
                'extracted': 'bg-gray-400',
                'impacted': 'bg-orange-500',
                'implant': 'bg-pink-500',
                'root_canal': 'bg-purple-500',
                'sealant': 'bg-cyan-500',
                'fracture': 'bg-red-700'
            }[data.status] || 'bg-green-500';
        },

        getToothInfo(tooth) {
            const data = this.teethData[tooth];
            if (!data) return 'Sano';

            const statusMap = {
                'healthy': 'Sano',
                'decayed': 'Caries',
                'filled': 'Restaurado',
                'crowned': 'Corona',
                'missing': 'Ausente',
                'extracted': 'Extraído',
                'impacted': 'Impactado',
                'implant': 'Implante',
                'root_canal': 'Endodoncia',
                'sealant': 'Sellante',
                'fracture': 'Fracturado'
            };

            return statusMap[data.status] || data.status;
        }
    }
}
</script>
