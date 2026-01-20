<!-- Enhanced Clinical Notes Tab - Dental SOAP Format -->
<div class="bg-white shadow rounded-lg border border-gray-100">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Nota Clínica Dental (Formato SOAP)
            </h2>
            <div class="flex items-center gap-2">
                <span x-show="noteStatus === 'signed'" class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Firmada
                </span>
                <span x-show="noteSaving" class="text-xs text-gray-500 flex items-center gap-1">
                    <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando...
                </span>
            </div>
        </div>
        
        <!-- Template Selection -->
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-500">Plantilla:</label>
            <select x-model="noteTemplate" @change="applyTemplate()" 
                    class="text-xs border border-gray-200 rounded px-2 py-1 focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                <option value="general">Consulta General</option>
                <option value="emergency">Urgencia Dental</option>
                <option value="restoration">Restauración</option>
                <option value="extraction">Extracción</option>
                <option value="endodontic">Endodoncia</option>
                <option value="prophylaxis">Profilaxis</option>
                <option value="orthodontic">Ortodoncia</option>
                <option value="periodontal">Periodoncia</option>
                <option value="implant">Implantología</option>
                <option value="pediatric">Odontopediatría</option>
            </select>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-1 ml-auto">
                <button @click="insertToothReference()" 
                        class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition-colors"
                        title="Insertar referencia de diente">
                    🦷 Diente
                </button>
                <button @click="insertDiagnosisCode()" 
                        class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition-colors"
                        title="Insertar código diagnóstico">
                    🏥 Diagnóstico
                </button>
                <button @click="insertProcedureCode()" 
                        class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition-colors"
                        title="Insertar código procedimiento">
                    ⚕️ Procedimiento
                </button>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-4">
        <!-- Chief Complaint with Dental Specific Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Motivo de Consulta
                </label>
                <input type="text"
                       x-model="clinicalNote.chief_complaint"
                       :disabled="noteStatus === 'signed'"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500"
                       placeholder="¿Cuál es el motivo de la consulta?">
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Tipo de Dolor
                </label>
                <select x-model="clinicalNote.pain_type"
                        :disabled="noteStatus === 'signed'"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500">
                    <option value="">Sin dolor</option>
                    <option value="spontaneous">Espontáneo</option>
                    <option value="provoked">Provocado (frío, calor, dulce)</option>
                    <option value="pulsatile">Pulsátil</option>
                    <option value="constant">Constante</option>
                    <option value="intermittent">Intermitente</option>
                </select>
            </div>
        </div>
        
        <!-- Pain Characteristics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Intensidad (EVA 0-10)
                </label>
                <div class="flex items-center gap-2">
                    <input type="range" min="0" max="10" x-model="clinicalNote.pain_intensity"
                           :disabled="noteStatus === 'signed'"
                           class="flex-1">
                    <span class="text-sm font-medium text-gray-900 w-8 text-center" x-text="clinicalNote.pain_intensity || 0"></span>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Duración
                </label>
                <select x-model="clinicalNote.pain_duration"
                        :disabled="noteStatus === 'signed'"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500">
                    <option value="">No aplica</option>
                    <option value="minutes">Minutos</option>
                    <option value="hours">Horas</option>
                    <option value="days">Días</option>
                    <option value="weeks">Semanas</option>
                    <option value="months">Meses</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Irradiación
                </label>
                <input type="text" x-model="clinicalNote.pain_radiation"
                       :disabled="noteStatus === 'signed'"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500"
                       placeholder="¿Se irradia a otra zona?">
            </div>
        </div>

        <!-- Enhanced SOAP Fields with Dental Specificity -->
        <div class="space-y-4">
            <!-- Subjective with Enhanced Details -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-1">S</span>
                    Subjetivo
                    <span class="text-gray-400 font-normal ml-1">(Historia y síntomas del paciente)</span>
                </label>
                <div class="space-y-2">
                    <textarea x-model="clinicalNote.subjective"
                              :disabled="noteStatus === 'signed'"
                              rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500 resize-none"
                              placeholder="Historia del problema actual, síntomas, evolución..."></textarea>
                    
                    <!-- Dental Specific Symptoms -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.halitosis" :disabled="noteStatus === 'signed'" class="mr-1">
                            Halitosis
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.bleeding_gums" :disabled="noteStatus === 'signed'" class="mr-1">
                            Sangrado gingival
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.tooth_sensitivity" :disabled="noteStatus === 'signed'" class="mr-1">
                            Sensibilidad dental
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.swelling" :disabled="noteStatus === 'signed'" class="mr-1">
                            Inflamación/Edema
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.difficulty_chewing" :disabled="noteStatus === 'signed'" class="mr-1">
                            Dificultad masticar
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.mobility" :disabled="noteStatus === 'signed'" class="mr-1">
                            Movilidad dental
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.fracture" :disabled="noteStatus === 'signed'" class="mr-1">
                            Fractura dental
                        </label>
                        <label class="flex items-center text-xs">
                            <input type="checkbox" x-model="clinicalNote.symptoms.trauma" :disabled="noteStatus === 'signed'" class="mr-1">
                            Trauma
                        </label>
                    </div>
                </div>
            </div>

            <!-- Objective with Dental Examination -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 text-green-700 text-xs font-bold mr-1">O</span>
                    Objetivo
                    <span class="text-gray-400 font-normal ml-1">(Examen clínico dental)</span>
                </label>
                <div class="space-y-2">
                    <textarea x-model="clinicalNote.objective"
                              :disabled="noteStatus === 'signed'"
                              rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500 resize-none"
                              placeholder="Examen intraoral y extraoral..."></textarea>
                    
                    <!-- Quick Examination Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tejidos Blandos</label>
                            <select x-model="clinicalNote.soft_tissues" :disabled="noteStatus === 'signed'"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                                <option value="normal">Normal</option>
                                <option value="inflamed">Inflamado</option>
                                <option value="ulcerated">Ulcerado</option>
                                <option value="pigmented">Pigmentado</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Oclusión</label>
                            <select x-model="clinicalNote.occlusion" :disabled="noteStatus === 'signed'"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                                <option value="normal">Normal</option>
                                <option value="class_i">Clase I</option>
                                <option value="class_ii">Clase II</option>
                                <option value="class_iii">Clase III</option>
                                <option value="cross_bite">Mordida cruzada</option>
                                <option value="open_bite">Mordida abierta</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">ATM</label>
                            <select x-model="clinicalNote.tmj" :disabled="noteStatus === 'signed'"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                                <option value="normal">Normal</option>
                                <option value="clicking">Clickeo</option>
                                <option value="limited">Limitación</option>
                                <option value="painful">Doloroso</option>
                                <option value="deviation">Desviación</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assessment with Dental Diagnoses -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold mr-1">A</span>
                    Evaluación / Diagnóstico Dental
                    <span class="text-gray-400 font-normal ml-1">(Diagnóstico principal y secundarios)</span>
                </label>
                <div class="space-y-2">
                    <textarea x-model="clinicalNote.assessment"
                              :disabled="noteStatus === 'signed'"
                              rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500 resize-none"
                              placeholder="Diagnóstico dental principal..."></textarea>
                    
                    <!-- Diagnosis Codes -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-500">Diagnósticos ICD-10:</label>
                            <button @click="addDiagnosis()" 
                                    class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200"
                                    :disabled="noteStatus === 'signed'">
                                + Agregar Diagnóstico
                            </button>
                        </div>
                        
                        <div class="space-y-1">
                            <template x-for="(diag, index) in clinicalNote.diagnoses" :key="index">
                                <div class="flex items-center gap-2">
                                    <select x-model="diag.code" :disabled="noteStatus === 'signed'"
                                            class="flex-1 text-xs border border-gray-200 rounded px-2 py-1">
                                        <option value="K02.1">K02.1 - Caries de dentina</option>
                                        <option value="K02.2">K02.2 - Caries de cemento</option>
                                        <option value="K02.3">K02.3 - Caries detenida</option>
                                        <option value="K03.0">K03.0 - Dientes con atrición excesiva</option>
                                        <option value="K04.0">K04.0 - Pulpa necrótica</option>
                                        <option value="K04.1">K04.1 - Pulpetis aguda</option>
                                        <option value="K04.2">K04.2 - Pulpetis crónica</option>
                                        <option value="K05.0">K05.0 - Absceso periapical agudo</option>
                                        <option value="K05.1">K05.1 - Absceso periapical crónico</option>
                                        <option value="K06.0">K06.0 - Trastornos de la erupción</option>
                                        <option value="K07.0">K07.0 - Anomalías dentofaciales</option>
                                        <option value="K08.0">K08.0 - Otros trastornos dientes</option>
                                    </select>
                                    <button @click="removeDiagnosis(index)" 
                                            class="text-red-500 hover:text-red-700 text-sm"
                                            :disabled="noteStatus === 'signed'">
                                        ✕
                                    </button>
        </div>

        <!-- Dental Procedures Reference -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Procedimientos Realizados en esta Cita</h4>
            <div class="space-y-2">
                <template x-for="procedure in $root.procedures" :key="procedure.id">
                    <div class="flex items-center justify-between p-2 bg-white rounded border">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-900" x-text="procedure.service_name"></span>
                            <span class="text-xs text-gray-500 ml-2" x-text="'Código: ' + (procedure.service_code || 'N/A')"></span>
                        </div>
                        <div class="text-sm text-gray-900">
                            $<span x-text="parseFloat(procedure.total || 0).toFixed(2)"></span>
                        </div>
                    </div>
                </template>
                <div x-show="$root.procedures.length === 0" class="text-center text-gray-500 text-sm py-4">
                    No se han agregado procedimientos a esta cita
                </div>
            </div>
        </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan with Treatment Planning -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-purple-100 text-purple-700 text-xs font-bold mr-1">P</span>
                    Plan de Tratamiento
                    <span class="text-gray-400 font-normal ml-1">(Plan terapéutico y seguimiento)</span>
                </label>
                <div class="space-y-2">
                    <textarea x-model="clinicalNote.plan"
                              :disabled="noteStatus === 'signed'"
                              rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary disabled:bg-gray-50 disabled:text-gray-500 resize-none"
                              placeholder="Plan de tratamiento integral..."></textarea>
                    
                    <!-- Treatment Planning -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Prioridad</label>
                            <select x-model="clinicalNote.treatment_priority" :disabled="noteStatus === 'signed'"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                                <option value="urgent">Urgente</option>
                                <option value="high">Alta</option>
                                <option value="medium">Media</option>
                                <option value="low">Baja</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Complejidad</label>
                            <select x-model="clinicalNote.complexity" :disabled="noteStatus === 'signed'"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                                <option value="simple">Simple</option>
                                <option value="moderate">Moderada</option>
                                <option value="complex">Compleja</option>
                                <option value="rehabilitation">Rehabilitación</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <div class="text-xs text-gray-500">
                <span x-show="noteStatus === 'draft'">Las notas se guardan automáticamente</span>
                <span x-show="noteStatus === 'signed'" class="text-green-600">
                    Nota firmada - No puede ser modificada
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="saveNote()"
                        x-show="noteStatus === 'draft'"
                        :disabled="noteSaving"
                        class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Guardar
                    </span>
                </button>
                <button @click="signNote()"
                        x-show="noteStatus === 'draft'"
                        class="px-4 py-2 text-sm font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Firmar Nota
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Instructions Card -->
<div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mt-4">
    <h4 class="text-sm font-medium text-blue-800 mb-2">Formato SOAP</h4>
    <ul class="text-xs text-blue-700 space-y-1">
        <li><strong>S (Subjetivo):</strong> Síntomas y quejas del paciente, historia del problema actual.</li>
        <li><strong>O (Objetivo):</strong> Hallazgos del examen clínico, signos vitales, resultados de pruebas.</li>
        <li><strong>A (Evaluación):</strong> Diagnóstico o impresión diagnóstica basada en S y O.</li>
        <li><strong>P (Plan):</strong> Plan de tratamiento, medicamentos, procedimientos, seguimiento.</li>
    </ul>
</div>
