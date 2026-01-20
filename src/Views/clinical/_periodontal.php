<div x-data="periodontalChart()" x-init="init()" class="space-y-4">
    <!-- Periodontal Chart Header -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Periodontograma</h3>
                    <p class="text-sm text-gray-500 mt-1">Registro completo de salud periodontal</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Date Selection -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Fecha:</label>
                        <input type="date" x-model="chartDate" @change="loadChartData()" 
                               class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary">
                    </div>
                    <!-- Compare Mode -->
                    <button @click="toggleCompareMode()" :class="compareMode ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Comparar
                        </span>
                    </button>
                    <!-- Save Button -->
                    <button @click="saveChart()" :disabled="saving"
                            class="px-4 py-2 bg-shalom-primary text-white text-sm font-medium rounded-lg hover:bg-shalom-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="flex items-center gap-2">
                            <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                            </svg>
                            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Measurement Mode Selection -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-sm font-medium text-gray-700">Modo de Medición:</span>
                <div class="flex items-center gap-2">
                    <button @click="measurementMode = 'probing'" :class="measurementMode === 'probing' ? 'bg-shalom-primary text-white' : 'bg-white text-gray-700 border border-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                        Sondaje
                    </button>
                    <button @click="measurementMode = 'recession'" :class="measurementMode === 'recession' ? 'bg-shalom-primary text-white' : 'bg-white text-gray-700 border border-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                        Recesión
                    </button>
                    <button @click="measurementMode = 'bleeding'" :class="measurementMode === 'bleeding' ? 'bg-shalom-primary text-white' : 'bg-white text-gray-700 border border-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                        Sangrado
                    </button>
                    <button @click="measurementMode = 'mobility'" :class="measurementMode === 'mobility' ? 'bg-shalom-primary text-white' : 'bg-white text-gray-700 border border-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                        Movilidad
                    </button>
                    <button @click="measurementMode = 'furcation'" :class="measurementMode === 'furcation' ? 'bg-shalom-primary text-white' : 'bg-white text-gray-700 border border-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                        Furcación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Periodontal Chart -->
    <div class="bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
        <div class="p-4 overflow-x-auto">
            <!-- Teeth Grid -->
            <div class="min-w-[800px]">
                <!-- Upper Arch -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Arcada Superior</h4>
                    <div class="grid grid-cols-16 gap-1 text-xs">
                        <!-- Tooth Numbers Header -->
                        <div></div>
                        <template x-for="n in 16" :key="n">
                            <div class="text-center font-semibold text-gray-700" x-text="18 - n"></div>
                        </template>
                        
                        <!-- Probing Depths -->
                        <div class="text-right pr-2 font-medium text-gray-600">Sondaje</div>
                        <template x-for="n in 16" :key="'probing-' + n">
                            <div class="text-center">
                                <div class="grid grid-rows-3 gap-0.5">
                                    <template x-for="site in ['B', 'M', 'L']" :key="'site-' + site">
                                        <input type="number" min="0" max="20" 
                                               x-model="chartData.upper[18-n].probing[site]"
                                               @focus="selectTooth(18-n, 'upper')"
                                               @blur="updateToothData(18-n, 'upper')"
                                               class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary"
                                               :class="getProbingColor(chartData.upper[18-n].probing[site])">
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Bleeding on Probing -->
                        <div class="text-right pr-2 font-medium text-gray-600">Sangrado</div>
                        <template x-for="n in 16" :key="'bleeding-' + n">
                            <div class="text-center">
                                <div class="grid grid-rows-3 gap-0.5">
                                    <template x-for="site in ['B', 'M', 'L']" :key="'bleeding-site-' + site">
                                        <select x-model="chartData.upper[18-n].bleeding[site]"
                                                @focus="selectTooth(18-n, 'upper')"
                                                @change="updateToothData(18-n, 'upper')"
                                                class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                            <option value="">-</option>
                                            <option value="-">-</option>
                                            <option value="+">+</option>
                                            <option value="++">++</option>
                                        </select>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Recession -->
                        <div class="text-right pr-2 font-medium text-gray-600">Recesión</div>
                        <template x-for="n in 16" :key="'recession-' + n">
                            <div class="text-center">
                                <div class="grid grid-rows-3 gap-0.5">
                                    <template x-for="site in ['B', 'M', 'L']" :key="'recession-site-' + site">
                                        <input type="number" min="0" max="10" 
                                               x-model="chartData.upper[18-n].recession[site]"
                                               @focus="selectTooth(18-n, 'upper')"
                                               @blur="updateToothData(18-n, 'upper')"
                                               class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Mobility -->
                        <div class="text-right pr-2 font-medium text-gray-600">Movilidad</div>
                        <template x-for="n in 16" :key="'mobility-' + n">
                            <div class="text-center">
                                <select x-model="chartData.upper[18-n].mobility"
                                        @focus="selectTooth(18-n, 'upper')"
                                        @change="updateToothData(18-n, 'upper')"
                                        class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </template>

                        <!-- Furcation -->
                        <div class="text-right pr-2 font-medium text-gray-600">Furcación</div>
                        <template x-for="n in 16" :key="'furcation-' + n">
                            <div class="text-center">
                                <select x-model="chartData.upper[18-n].furcation"
                                        @focus="selectTooth(18-n, 'upper')"
                                        @change="updateToothData(18-n, 'upper')"
                                        class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Lower Arch -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Arcada Inferior</h4>
                    <div class="grid grid-cols-16 gap-1 text-xs">
                        <!-- Tooth Numbers Header -->
                        <div></div>
                        <template x-for="n in 16" :key="'lower-' + n">
                            <div class="text-center font-semibold text-gray-700" x-text="n + 31"></div>
                        </template>
                        
                        <!-- Probing Depths -->
                        <div class="text-right pr-2 font-medium text-gray-600">Sondaje</div>
                        <template x-for="n in 16" :key="'lower-probing-' + n">
                            <div class="text-center">
                                <div class="grid grid-rows-3 gap-0.5">
                                    <template x-for="site in ['B', 'M', 'L']" :key="'lower-site-' + site">
                                        <input type="number" min="0" max="20" 
                                               x-model="chartData.lower[n+31].probing[site]"
                                               @focus="selectTooth(n+31, 'lower')"
                                               @blur="updateToothData(n+31, 'lower')"
                                               class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary"
                                               :class="getProbingColor(chartData.lower[n+31].probing[site])">
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Bleeding on Probing -->
                        <div class="text-right pr-2 font-medium text-gray-600">Sangrado</div>
                        <template x-for="n in 16" :key="'lower-bleeding-' + n">
                            <div class="text-center">
                                <div class="grid grid-rows-3 gap-0.5">
                                    <template x-for="site in ['B', 'M', 'L']" :key="'lower-bleeding-site-' + site">
                                        <select x-model="chartData.lower[n+31].bleeding[site]"
                                                @focus="selectTooth(n+31, 'lower')"
                                                @change="updateToothData(n+31, 'lower')"
                                                class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                            <option value="">-</option>
                                            <option value="-">-</option>
                                            <option value="+">+</option>
                                            <option value="++">++</option>
                                        </select>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Recession -->
                        <div class="text-right pr-2 font-medium text-gray-600">Recesión</div>
                        <template x-for="n in 16" :key="'lower-recession-' + n">
                            <div class="text-center">
                                <div class="grid grid-rows-3 gap-0.5">
                                    <template x-for="site in ['B', 'M', 'L']" :key="'lower-recession-site-' + site">
                                        <input type="number" min="0" max="10" 
                                               x-model="chartData.lower[n+31].recession[site]"
                                               @focus="selectTooth(n+31, 'lower')"
                                               @blur="updateToothData(n+31, 'lower')"
                                               class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Mobility -->
                        <div class="text-right pr-2 font-medium text-gray-600">Movilidad</div>
                        <template x-for="n in 16" :key="'lower-mobility-' + n">
                            <div class="text-center">
                                <select x-model="chartData.lower[n+31].mobility"
                                        @focus="selectTooth(n+31, 'lower')"
                                        @change="updateToothData(n+31, 'lower')"
                                        class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </template>

                        <!-- Furcation -->
                        <div class="text-right pr-2 font-medium text-gray-600">Furcación</div>
                        <template x-for="n in 16" :key="'lower-furcation-' + n">
                            <div class="text-center">
                                <select x-model="chartData.lower[n+31].furcation"
                                        @focus="selectTooth(n+31, 'lower')"
                                        @change="updateToothData(n+31, 'lower')"
                                        class="w-full text-center border border-gray-200 rounded px-1 py-0.5 text-xs focus:ring-1 focus:ring-shalom-primary focus:border-shalom-primary">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- General Statistics -->
        <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Estadísticas Generales</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Profundidad Promedio:</span>
                    <span class="font-medium" x-text="getAverageProbing() + ' mm'"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Sitios > 4mm:</span>
                    <span class="font-medium text-red-600" x-text="getDeepSites()"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Sangrado (+):</span>
                    <span class="font-medium text-orange-600" x-text="getBleedingSites()"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Movilidad > 0:</span>
                    <span class="font-medium text-yellow-600" x-text="getMobilityCount()"></span>
                </div>
            </div>
        </div>

        <!-- Risk Assessment -->
        <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Evaluación de Riesgo</h4>
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" :class="getRiskLevel().color"></div>
                    <span class="text-sm font-medium" x-text="getRiskLevel().label"></span>
                </div>
                <p class="text-xs text-gray-600" x-text="getRiskLevel().description"></p>
                <div class="pt-2 border-t border-gray-100">
                    <div class="text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Riesgo de Progresión:</span>
                            <span class="font-medium" x-text="getProgressionRisk()"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Recomendación:</span>
                            <span class="font-medium text-shalom-primary" x-text="getRecommendation()"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clinical Notes -->
        <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Notas Periodontales</h4>
            <textarea x-model="periodontalNotes" @input="updateNotes()" 
                      placeholder="Agregar notas específicas del examen periodontal..."
                      class="w-full h-20 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary resize-none"></textarea>
            <div class="mt-2 flex justify-between items-center">
                <span class="text-xs text-gray-500">
                    <span x-text="periodontalNotes.length"></span>/500 caracteres
                </span>
                <div class="flex gap-2">
                    <button @click="addQuickNote('Higiene oral mejorada')" 
                            class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition-colors">
                        Higiene
                    </button>
                    <button @click="addQuickNote('Referencia a especialista')" 
                            class="text-xs px-2 py-1 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition-colors">
                        Referencia
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Compare Mode Panel -->
    <div x-show="compareMode" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-blue-900 mb-3">Modo Comparación</h4>
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-blue-700">Comparar con:</label>
                <select x-model="compareDate" @change="loadCompareData()" 
                        class="mt-1 w-full text-sm border border-blue-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccionar fecha anterior...</option>
                    <option value="2024-01-15">15/01/2024</option>
                    <option value="2023-12-01">01/12/2023</option>
                    <option value="2023-09-15">15/09/2023</option>
                </select>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-green-500 rounded"></div>
                    <span class="text-gray-700">Mejoría</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-red-500 rounded"></div>
                    <span class="text-gray-700">Deterioro</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-gray-400 rounded"></div>
                    <span class="text-gray-700">Sin cambios</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function periodontalChart(patientId) {
    return {
        patientId: patientId,
        chartDate: new Date().toISOString().split('T')[0],
        compareMode: false,
        compareDate: '',
        measurementMode: 'probing',
        saving: false,
        selectedTooth: null,
        selectedArch: null,
        periodontalNotes: '',
        chartData: {
            upper: {},
            lower: {}
        },
        
        init() {
            this.initializeChartData();
            this.loadChartData();
        },
        
        initializeChartData() {
            // Initialize upper arch (18-11)
            for (let tooth = 18; tooth >= 11; tooth--) {
                this.chartData.upper[tooth] = {
                    probing: { B: '', M: '', L: '' },
                    bleeding: { B: '', M: '', L: '' },
                    recession: { B: '', M: '', L: '' },
                    mobility: '',
                    furcation: ''
                };
            }
            
            // Initialize lower arch (31-46)
            for (let tooth = 31; tooth <= 46; tooth++) {
                this.chartData.lower[tooth] = {
                    probing: { B: '', M: '', L: '' },
                    bleeding: { B: '', M: '', L: '' },
                    recession: { B: '', M: '', L: '' },
                    mobility: '',
                    furcation: ''
                };
            }
        },
        
        async loadChartData() {
            try {
                const response = await fetch(`/api/clinical/patients/${this.patientId}/periodontal-chart?date=${this.chartDate}`);
                const data = await response.json();
                
                if (data.success) {
                    if (data.chart) {
                        this.chartData = JSON.parse(data.chart.chart_data);
                        this.periodontalNotes = data.chart.notes || '';
                    }
                } else {
                    console.error('Error loading chart:', data.message);
                    this.$dispatch('show-notification', {
                        type: 'error',
                        message: 'Error al cargar datos del periodontograma'
                    });
                }
            } catch (error) {
                console.error('Network error:', error);
                this.$dispatch('show-notification', {
                    type: 'error',
                    message: 'Error de conexión al cargar datos'
                });
            }
        },
        
        selectTooth(tooth, arch) {
            this.selectedTooth = tooth;
            this.selectedArch = arch;
        },
        
        updateToothData(tooth, arch) {
            // Auto-calculate attachment loss
            const toothData = this.chartData[arch][tooth];
            // Implementation for auto-calculation would go here
            console.log('Updated tooth data:', tooth, arch, toothData);
        },
        
        toggleCompareMode() {
            this.compareMode = !this.compareMode;
            if (!this.compareMode) {
                this.compareDate = '';
            }
        },
        
        loadCompareData() {
            if (this.compareDate) {
                console.log('Loading compare data for:', this.compareDate);
            }
        },
        
        getProbingColor(value) {
            const depth = parseInt(value);
            if (isNaN(depth) || depth === '') return 'bg-white';
            if (depth >= 7) return 'bg-red-100 text-red-700 border-red-300';
            if (depth >= 5) return 'bg-orange-100 text-orange-700 border-orange-300';
            if (depth >= 4) return 'bg-yellow-100 text-yellow-700 border-yellow-300';
            return 'bg-green-50 text-green-700 border-green-300';
        },
        
        getAverageProbing() {
            let total = 0;
            let count = 0;
            
            Object.values(this.chartData.upper).forEach(tooth => {
                Object.values(tooth.probing).forEach(value => {
                    const depth = parseInt(value);
                    if (!isNaN(depth) && depth > 0) {
                        total += depth;
                        count++;
                    }
                });
            });
            
            Object.values(this.chartData.lower).forEach(tooth => {
                Object.values(tooth.probing).forEach(value => {
                    const depth = parseInt(value);
                    if (!isNaN(depth) && depth > 0) {
                        total += depth;
                        count++;
                    }
                });
            });
            
            return count > 0 ? (total / count).toFixed(1) : '0.0';
        },
        
        getDeepSites() {
            let count = 0;
            
            Object.values(this.chartData.upper).forEach(tooth => {
                Object.values(tooth.probing).forEach(value => {
                    const depth = parseInt(value);
                    if (!isNaN(depth) && depth >= 4) {
                        count++;
                    }
                });
            });
            
            Object.values(this.chartData.lower).forEach(tooth => {
                Object.values(tooth.probing).forEach(value => {
                    const depth = parseInt(value);
                    if (!isNaN(depth) && depth >= 4) {
                        count++;
                    }
                });
            });
            
            return count;
        },
        
        getBleedingSites() {
            let count = 0;
            
            Object.values(this.chartData.upper).forEach(tooth => {
                Object.values(tooth.bleeding).forEach(value => {
                    if (value === '+' || value === '++') {
                        count++;
                    }
                });
            });
            
            Object.values(this.chartData.lower).forEach(tooth => {
                Object.values(tooth.bleeding).forEach(value => {
                    if (value === '+' || value === '++') {
                        count++;
                    }
                });
            });
            
            return count;
        },
        
        getMobilityCount() {
            let count = 0;
            
            Object.values(this.chartData.upper).forEach(tooth => {
                const mobility = parseInt(tooth.mobility);
                if (!isNaN(mobility) && mobility > 0) {
                    count++;
                }
            });
            
            Object.values(this.chartData.lower).forEach(tooth => {
                const mobility = parseInt(tooth.mobility);
                if (!isNaN(mobility) && mobility > 0) {
                    count++;
                }
            });
            
            return count;
        },
        
        getRiskLevel() {
            const avgProbing = parseFloat(this.getAverageProbing());
            const deepSites = this.getDeepSites();
            const bleedingSites = this.getBleedingSites();
            const mobilityCount = this.getMobilityCount();
            
            if (avgProbing >= 5 || deepSites >= 10 || mobilityCount >= 3) {
                return {
                    label: 'Riesgo Alto',
                    color: 'bg-red-500',
                    description: 'Enfermedad periodontal severa que requiere tratamiento inmediato'
                };
            } else if (avgProbing >= 4 || deepSites >= 5 || bleedingSites >= 15) {
                return {
                    label: 'Riesgo Moderado',
                    color: 'bg-yellow-500',
                    description: 'Enfermedad periodontal moderada que requiere tratamiento'
                };
            } else if (avgProbing >= 3 || bleedingSites >= 10) {
                return {
                    label: 'Riesgo Leve',
                    color: 'bg-orange-500',
                    description: 'Gingivitis o periodontitis leve'
                };
            } else {
                return {
                    label: 'Riesgo Bajo',
                    color: 'bg-green-500',
                    description: 'Salud periodontal buena'
                };
            }
        },
        
        getProgressionRisk() {
            const risk = this.getRiskLevel();
            if (risk.label === 'Riesgo Alto') return 'Alto';
            if (risk.label === 'Riesgo Moderado') return 'Moderado';
            if (risk.label === 'Riesgo Leve') return 'Leve';
            return 'Mínimo';
        },
        
        getRecommendation() {
            const risk = this.getRiskLevel();
            if (risk.label === 'Riesgo Alto') return 'Tratamiento inmediato';
            if (risk.label === 'Riesgo Moderado') return 'Tratamiento requerido';
            if (risk.label === 'Riesgo Leve') return 'Mejorar higiene';
            return 'Mantenimiento';
        },
        
        updateAlertsCount() {
            // Count alerts based on risk assessment
            const avgProbing = parseFloat(this.getAverageProbing());
            const deepSites = this.getDeepSites();
            const mobilityCount = this.getMobilityCount();
            
            let alerts = 0;
            
            if (avgProbing >= 5) alerts++;
            if (deepSites >= 10) alerts++;
            if (mobilityCount >= 3) alerts++;
            
            // Update parent component's alerts count
            this.$dispatch('update-periodontal-alerts', { count: alerts });
        },
        
        updateNotes() {
            if (this.periodontalNotes.length > 500) {
                this.periodontalNotes = this.periodontalNotes.substring(0, 500);
            }
        },
        
        addQuickNote(note) {
            this.periodontalNotes += (this.periodontalNotes ? '. ' : '') + note;
            this.updateNotes();
        },
        
        async saveChart() {
            this.saving = true;
            try {
                const response = await fetch(`/api/clinical/patients/${this.patientId}/periodontal-chart`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        date: this.chartDate,
                        chart_data: this.chartData,
                        notes: this.periodontalNotes
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.$dispatch('show-notification', {
                        type: 'success',
                        message: 'Periodontograma guardado exitosamente'
                    });
                    
                    // Update alerts count if needed
                    this.updateAlertsCount();
                } else {
                    this.$dispatch('show-notification', {
                        type: 'error',
                        message: data.message || 'Error al guardar el periodontograma'
                    });
                }
            } catch (error) {
                console.error('Save error:', error);
                this.$dispatch('show-notification', {
                    type: 'error',
                    message: 'Error de conexión al guardar'
                });
            } finally {
                this.saving = false;
            }
        },
    }
}
</script>

<style>
.grid-cols-16 {
    display: grid;
    grid-template-columns: repeat(16, minmax(0, 1fr));
}
</style>