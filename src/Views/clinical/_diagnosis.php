<!-- Dental Diagnosis and Condition Codes Integration -->
<div class="space-y-4">
    <!-- Diagnosis Header -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 012-2 2 0 0v12a2 2 0 012-2V5a2 2 0 002-2H7a2 2 0 002 2v5a2 2 0 012-2V5a2 2 0 002 2z"/>
                </svg>
                Códigos Diagnósticos ICD-10
            </h2>
            <button @click="showDiagnosisSearch = !showDiagnosisSearch"
                    class="px-3 py-1.5 text-xs bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors">
                Buscar Códigos
            </button>
        </div>

        <!-- Diagnosis Search Section -->
        <div x-show="showDiagnosisSearch" x-cloak class="p-4">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría ICD-10</label>
                    <select x-model="selectedDiagnosisCategory" @change="filterDiagnoses()"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                        <option value="all">Todas las categorías</option>
                        <option value="K00-K14">Capítulo I: Enfermedades bucodentales, ciertas infecciosas y parasitarias</option>
                        <option value="K00-K93">Capítulo IX: Enfermedades del sistema circulatorio y respiratorio</option>
                        <option value="L00-L99">Capítulo XI: Enfermedades del sistema digestivo</option>
                        <option value="M00-M19">Capítulo XIII: Enfermedades del sistema musculoesquelético y del tejido conjuntivo</option>
                        <option value="N00-N99">Capítulo XIV: Enfermedades del sistema genitourinario</option>
                        <option value="O00-O09">Capítulo XV: Embarazo, parto y puerperio</option>
                        <option value="P00-P97">Capítulo XVI: Afecciones originadas en el período perinatal</option>
                        <option value="Q00-Q89">Capítulo XVII: Anormalidades dentales y de los labios bucales</option>
                        <option value="R00-R95">Capítulo XIX: Traumatismos, envenenamientos, fracturas y cuerpo extraño</option>
                        <option value="S00-T98">Capítulo XX: Malformaciones congénitas</option>
                        <option value="V01-Y82">Capítulo XXI: Otros trastornos y factores que influyen en el estado de salud</option>
                        <option value="Z00-Z99">Capítulo XXII: Factores que influyen en el estado de salud y contacto con servicios de salud</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Búsqueda por Código o Descripción</label>
                    <div class="relative">
                        <input type="text" 
                               x-model="diagnosisSearchQuery"
                               @input="filterDiagnoses()"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary pl-10"
                               placeholder="Buscar diagnóstico...">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-2m6 6m6m6m6m-6h4v6h-4m2a3 3 0 00-6-4a3 3 0 00-6 4a3 0 0 00-6 4a3 0 00-6 4a3 0 00-6 4a3 0 00-2-2V6z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Quick Diagnosis Categories -->
            <div class="flex flex-wrap gap-2">
                <button @click="selectDiagnosisCategory('K00-K14')" 
                        class="px-3 py-1.5 text-xs bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                    Infecciosas
                </button>
                <button @click="selectDiagnosisCategory('K00-K93')" 
                        class="px-3 py-1.5 text-xs bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    Respiratorias
                </button>
                <button @click="selectDiagnosisCategory('L00-L99')" 
                        class="px-3 py-1.5 text-xs bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                    Digestivo
                </button>
                <button @click="selectDiagnosisCategory('M00-M19')" 
                        class="px-3 py-1.5 text-xs bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors">
                    Musculoesquelético
                </button>
                <button @click="selectDiagnosisCategory('N00-N99')" 
                        class="px-3 py-1.5 text-xs bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    Genitourinario
                </button>
                <button @click="selectDiagnosisCategory('O00-O09')" 
                        class="px-3 py-1.5 text-xs bg-pink-50 border border-pink-200 rounded-lg hover:bg-pink-100 transition-colors">
                    Perinatal
                </button>
                <button @click="selectDiagnosisCategory('P00-P97')" 
                        class="px-3 py-1.5 text-xs bg-cyan-50 border border-cyan-200 rounded-lg hover:bg-cyan-100 transition-colors">
                    Afecciones
                </button>
                <button @click="selectDiagnosisCategory('Q00-Q89')" 
                        class="px-3 py-1.5 text-xs bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors">
                    Labios Bucales
                </button>
                <button @click="selectDiagnosisCategory('R00-R95')" 
                        class="px-3 py-1.5 text-xs bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition-colors">
                    Trauma
                </button>
                <button @click="selectDiagnosisCategory('S00-T98')" 
                        class="px-3 py-1.5 text-xs bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                    Malformaciones
                </button>
                <button @click="selectDiagnosisCategory('V01-Y82')" 
                        class="px-3 py-1.5 text-xs bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    Anormalidades
                </button>
            </div>
        </div>
    </div>

    <!-- Diagnosis Results -->
    <div x-show="filteredDiagnoses.length > 0" class="space-y-2">
        <div class="text-sm text-gray-500 mb-3">
            Se encontraron <span x-text="filteredDiagnoses.length"></span> diagnósticos
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <template x-for="diagnosis in filteredDiagnoses" :key="diagnosis.code">
                <div @click="selectDiagnosis(diagnosis)" 
                        class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-md transition-all duration-200 cursor-pointer group hover:border-shalom-primary/50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full" x-text="diagnosis.category[0]">D</span>
                                <span class="font-mono font-medium text-gray-900" x-text="diagnosis.code"></span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900" x-text="diagnosis.short_description"></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span x-text="diagnosis.long_description"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                        
                        <button @click="selectDiagnosis(diagnosis)" 
                                class="ml-2 p-2 bg-shalom-primary text-white rounded hover:bg-shalom-dark transition-colors">
                            Seleccionar
                        </button>
                    </div>
                </div>
            </template>
            
            <div x-show="!selectedDiagnosis" class="text-center py-8 text-gray-500">
                <p class="text-sm">Seleccione un diagnóstico para ver detalles y agregar a la nota clínica</p>
            </div>
        </div>
    </div>

    <!-- Diagnosis Details Modal -->
    <div x-show="selectedDiagnosis" x-cloak 
         class="fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4"
         @click.self="selectedDiagnosis = null">
        <div class="bg-white rounded-lg max-w-2xl max-h-full overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="font-mono bg-shalom-primary text-white px-2 py-1 rounded mr-2" x-text="selectedDiagnosis.code"></span>
                        <span x-text="selectedDiagnosis.short_description"></span>
                    </h3>
                    <button @click="selectedDiagnosis = null" 
                            class="text-gray-400 hover:text-gray-600 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 0v12M6 18a2 2 0 012 2 0 0v12M6 18a2 0 002 0 011-2h2a2 2 0 001-2M4a6 2 0 011-2h11a2 2 0 002 0 011-2V6z"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Diagnosis Details -->
                <div class="space-y-4">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Descripción Clínica</h4>
                        <p class="text-sm text-gray-700 leading-relaxed" x-text="selectedDiagnosis.clinical_description"></p>
                    </div>
                    
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Recomendaciones de Tratamiento</h4>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <span class="text-sm font-medium text-gray-700 w-24">•</span>
                                <span class="text-sm text-gray-600 flex-1" x-text="selectedDiagnosis.treatment_recommendations?.immediate || ''"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Procedimientos Relacionados</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-700 w-24">•</span>
                                <span class="text-sm text-gray-600 flex-1" x-text="selectedDiagnosis.related_procedures?.join(', ') || 'Ninguno'"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Notas Adicionales</h4>
                        <textarea x-model="selectedDiagnosis.additional_notes" 
                                  rows="4"
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                  placeholder="Agregar notas adicionales sobre el diagnóstico..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button @click="cancelDiagnosisSelection()" 
                                class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="addDiagnosisToClinicalNote()" 
                                class="px-4 py-2 bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors">
                            Agregar a Nota
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function diagnosisComponent() {
    return {
        showDiagnosisSearch: false,
        selectedDiagnosisCategory: 'all',
        diagnosisSearchQuery: '',
        selectedDiagnosis: null,
        filteredDiagnoses: [],
        
    </div>

    <!-- Diagnosis Details Modal -->
    <div x-show="selectedDiagnosis" x-cloak 
         class="fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4"
         @click.self="selectedDiagnosis = null">
        <div class="bg-white rounded-lg max-w-2xl max-h-full overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span class="font-mono bg-shalom-primary text-white px-2 py-1 rounded mr-2" x-text="selectedDiagnosis.code"></span>
                        <span x-text="selectedDiagnosis.short_description"></span>
                    </h3>
                    <button @click="selectedDiagnosis = null" 
                            class="text-gray-400 hover:text-gray-600 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 0v12M6 18a2 2 0 012-2M6 18a2 2 0 002 2v6z"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Diagnosis Details -->
                <div class="space-y-4">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Descripción Clínica</h4>
                        <p class="text-sm text-gray-700 leading-relaxed" x-text="selectedDiagnosis.clinical_description"></p>
                    </div>
                    
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Recomendaciones de Tratamiento</h4>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <span class="text-sm font-medium text-gray-700 w-24">•</span>
                                <span class="text-sm text-gray-600 flex-1" x-text="selectedDiagnosis.treatment_recommendations?.immediate || ''"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Procedimientos Relacionados</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-700 w-24">•</span>
                                <span class="text-sm text-gray-600 flex-1" x-text="selectedDiagnosis.related_procedures?.join(', ') || 'Ninguno'"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Notas Adicionales</h4>
                        <textarea x-model="selectedDiagnosis.additional_notes" 
                                  rows="4"
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                                  placeholder="Agregar notas adicionales sobre el diagnóstico..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button @click="cancelDiagnosisSelection()" 
                                class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="addDiagnosisToClinicalNote()" 
                                class="px-4 py-2 bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors">
                            Agregar a Nota
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
        init() {
            // Load patient diagnosis history
            this.loadPatientDiagnosis();
        },

        async loadPatientDiagnosis() {
            try {
                // In real implementation, this would fetch from API
                const response = await fetch(`/api/clinical/patients/${this.$root.patientId}/diagnoses`);
                // if (response.success) {
                //     this.patientDiagnoses = response.data;
                // }
            } catch (error) {
                console.error('Error loading patient diagnoses:', error);
            }
        },

        filterDiagnoses() {
            let filtered = this.diagnosisDatabase;
            
            // Category filter
            if (this.selectedDiagnosisCategory !== 'all') {
                filtered = filtered.filter(d => d.category === this.selectedDiagnosisCategory);
            }
            
            // Search filter
            if (this.diagnosisSearchQuery) {
                const search = this.diagnosisSearchQuery.toLowerCase();
                filtered = filtered.filter(d => 
                    d.code.toLowerCase().includes(search) ||
                    d.short_description.toLowerCase().includes(search) ||
                    d.long_description.toLowerCase().includes(search)
                );
            }
            
            this.filteredDiagnoses = filtered;
        },

        selectDiagnosis(diagnosis) {
            this.selectedDiagnosis = diagnosis;
        },

        cancelDiagnosisSelection() {
            this.selectedDiagnosis = null;
        },

        addDiagnosisToClinicalNote() {
            if (!this.selectedDiagnosis) return;
            
            // Add diagnosis to clinical note
            if (!this.clinicalNote.plan) {
                this.clinicalNote.plan = '';
            }
            
            this.clinicalNote.assessment += `\n[Diagnóstico: ${this.selectedDiagnosis.code} - ${this.selectedDiagnosis.short_description}]`;
            
            this.selectedDiagnosis = null;
            this.$root.showToast('Diagnóstico agregado a nota clínica', 'success');
        }
    };
}
</script>