<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div x-data="clinicalCare()" x-init="init()" class="space-y-4">
    <!-- Header with Patient Info and Actions -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Patient Info -->
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-shalom-primary text-white flex items-center justify-center text-xl font-semibold">
                        <?= strtoupper(substr($appointment['patient_first_name'], 0, 1) . substr($appointment['patient_last_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            <?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?>
                        </h1>
                        <div class="flex items-center gap-3 text-sm text-gray-500">
                            <span><?= htmlspecialchars($appointment['patient_id_type']) ?>: <?= htmlspecialchars($appointment['patient_id_number']) ?></span>
                            <span>&bull;</span>
                            <span><?= htmlspecialchars($appointment['patient_phone']) ?></span>
                            <?php if ($appointment['patient_birth_date']): ?>
                                <span>&bull;</span>
                                <span><?= date('d/m/Y', strtotime($appointment['patient_birth_date'])) ?> (<?= floor((time() - strtotime($appointment['patient_birth_date'])) / 31536000) ?> años)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Appointment Info and Actions -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden md:block">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($appointment['appointment_type_name']) ?></div>
                        <div class="text-xs text-gray-500">
                            <?= date('d/m/Y', strtotime($appointment['scheduled_date'])) ?>
                            <?= date('H:i', strtotime($appointment['scheduled_start_time'])) ?>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        <?php echo match($appointment['status']) {
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-green-100 text-green-700',
                            'checked_in' => 'bg-yellow-100 text-yellow-700',
                            default => 'bg-gray-100 text-gray-700'
                        }; ?>">
                        <?php echo match($appointment['status']) {
                            'in_progress' => 'En Atención',
                            'completed' => 'Completada',
                            'checked_in' => 'En Sala',
                            'confirmed' => 'Confirmada',
                            default => ucfirst($appointment['status'])
                        }; ?>
                    </span>

                    <!-- Action Buttons -->
                    <?php if ($appointment['status'] === 'checked_in' || $appointment['status'] === 'confirmed'): ?>
                        <button @click="startAppointment()"
                                class="px-4 py-2 bg-shalom-primary text-white text-sm font-medium rounded-lg hover:bg-shalom-dark transition-colors">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Iniciar Atención
                            </span>
                        </button>
                    <?php elseif ($appointment['status'] === 'in_progress'): ?>
                        <button @click="completeAppointment()"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Finalizar Atención
                            </span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Medical Alerts -->
        <?php if (!empty($alerts)): ?>
        <div class="px-4 py-2 bg-red-50 border-b border-red-100">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <span class="text-sm font-medium text-red-700">Alertas Médicas:</span>
                    <div class="text-sm text-red-600">
                        <?php foreach ($alerts as $alert): ?>
                            <span class="inline-block mr-4"><?= htmlspecialchars($alert['message']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-100">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                <button @click="activeTab = 'summary'" :class="activeTab === 'summary' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors">
                    Resumen
                </button>
                <button @click="activeTab = 'notes'" :class="activeTab === 'notes' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors">
                    Notas Clínicas
                </button>
                <button @click="activeTab = 'odontogram'" :class="activeTab === 'odontogram' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors">
                    Odontograma
                </button>
                <button @click="activeTab = 'periodontal'" :class="activeTab === 'periodontal' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                    Periodontograma
                    <span x-show="periodontalAlerts > 0" class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full" x-text="periodontalAlerts"></span>
                </button>
                <button @click="activeTab = 'procedures'" :class="activeTab === 'procedures' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                    Procedimientos
                    <span x-show="procedures.length > 0" class="bg-shalom-primary text-white text-xs px-2 py-0.5 rounded-full" x-text="procedures.length"></span>
                </button>
                <button @click="activeTab = 'imaging'" :class="activeTab === 'imaging' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                    Imagenes
                    <span x-show="imagesCount > 0" class="bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full" x-text="imagesCount"></span>
                </button>
                <button @click="activeTab = 'invoice'" :class="activeTab === 'invoice' ? 'border-shalom-primary text-shalom-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                    Facturar
                    <span x-show="procedureTotals.pending > 0" class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">$<span x-text="procedureTotals.pending.toFixed(2)"></span></span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Content Area (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white border border-gray-100 rounded-lg px-4 py-3 text-sm text-gray-500 flex flex-wrap items-center gap-3">
                <span class="font-medium text-gray-700">Guia rapida:</span>
                <span>1. Registra notas clinicas</span>
                <span>2. Agrega procedimientos</span>
                <span>3. Revisa facturacion</span>
                <span>4. Finaliza la atencion</span>
            </div>

            <!-- Summary Tab -->
            <div x-show="activeTab === 'summary'" x-cloak>
                <?= $this->include('clinical._summary', [
                    'appointment' => $appointment,
                    'previousNotes' => $previousNotes,
                    'treatmentPlans' => $treatmentPlans
                ]) ?>
            </div>

            <!-- Clinical Notes Tab -->
            <div x-show="activeTab === 'notes'" x-cloak>
                <?= $this->include('clinical._notes', ['clinicalNote' => $clinicalNote]) ?>
            </div>

            <!-- Odontogram Tab -->
            <div x-show="activeTab === 'odontogram'" x-cloak>
                <?= $this->include('clinical._odontogram', ['odontogramSummary' => $odontogramSummary]) ?>
            </div>

            <!-- Periodontal Tab -->
            <div x-show="activeTab === 'periodontal'" x-cloak>
                <div x-data="periodontalChart(<?= $appointment['patient_id'] ?>)" 
                     @update-periodontal-alerts.window="periodontalAlerts = $event.detail.count">
                    <?= $this->include('clinical._periodontal') ?>
                </div>
            </div>

            <!-- Procedures Tab -->
            <div x-show="activeTab === 'procedures'" x-cloak>
                <?= $this->include('clinical._procedures', [
                    'services' => $services,
                    'serviceCategories' => $serviceCategories
                ]) ?>
            </div>

            <!-- Invoice Tab -->
            <div x-show="activeTab === 'invoice'" x-cloak>
                <?= $this->include('clinical._invoice') ?>
            </div>

            <!-- Imaging Tab -->
            <div x-show="activeTab === 'imaging'" x-cloak>
                <?= $this->include('clinical._imaging') ?>
            </div>
        </div>

        <!-- Sidebar (1 col) -->
        <div class="space-y-4">
            <!-- Quick Info Card -->
            <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Información Rápida</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Profesional:</span>
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($appointment['professional_title'] . ' ' . $appointment['professional_first_name'] . ' ' . $appointment['professional_last_name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sede:</span>
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($appointment['location_name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tipo Sangre:</span>
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($appointment['patient_blood_type'] ?: 'No registrado') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Estado cita:</span>
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($appointment['status']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Active Treatment Plans -->
            <?php if (!empty($treatmentPlans)): ?>
            <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Planes de Tratamiento Activos</h3>
                <div class="space-y-3">
                    <?php foreach ($treatmentPlans as $plan): ?>
                    <div class="border border-gray-100 rounded-lg p-3">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium text-gray-900 text-sm"><?= htmlspecialchars($plan['name']) ?></span>
                            <span class="text-xs px-2 py-0.5 rounded-full <?= $plan['status'] === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' ?>">
                                <?= $plan['status'] === 'in_progress' ? 'En Progreso' : ucfirst($plan['status']) ?>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-shalom-primary h-2 rounded-full" style="width: <?= $plan['progress'] ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-500"><?= $plan['completed_items'] ?>/<?= $plan['total_items'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Procedures Summary -->
            <div class="bg-gradient-to-br from-shalom-primary to-shalom-dark rounded-lg shadow-lg p-4 text-white">
                <h3 class="text-sm font-medium text-white/80 mb-1">Resumen de Atención</h3>
                <p class="text-xs text-white/70 mb-3">Control rapido de totales.</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-white/70">
                        <span>Procedimientos:</span>
                        <span x-text="procedures.length"></span>
                    </div>
                    <div class="flex justify-between text-white/70">
                        <span>Subtotal:</span>
                        <span>$<span x-text="procedureTotals.subtotal.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-white/70">
                        <span>IVA:</span>
                        <span>$<span x-text="procedureTotals.tax.toFixed(2)"></span></span>
                    </div>
                    <div class="border-t border-white/20 pt-2 mt-2">
                        <div class="flex justify-between font-semibold">
                            <span>Total:</span>
                            <span>$<span x-text="procedureTotals.total.toFixed(2)"></span></span>
                        </div>
                    </div>
                    <div x-show="procedureTotals.pending > 0" class="pt-2">
                        <div class="flex justify-between text-yellow-300">
                            <span>Pendiente facturar:</span>
                            <span>$<span x-text="procedureTotals.pending.toFixed(2)"></span></span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="activeTab = 'invoice'" class="mt-4 w-full px-3 py-2 text-xs font-semibold rounded-lg bg-white/20 hover:bg-white/30 transition">Ir a facturacion</button>
            </div>

            <!-- Quick Links -->
            <div class="bg-white shadow rounded-lg border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Acciones Rápidas</h3>
                <div class="space-y-2">
                    <a href="/clinical/patients/<?= $appointment['patient_id'] ?>/history"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Ver Historial Completo
                    </a>
                    <a href="/clinical/patients/<?= $appointment['patient_id'] ?>/treatment-plans"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Planes de Tratamiento
                    </a>
                    <a href="/patients/<?= $appointment['patient_id'] ?>"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Ficha del Paciente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div x-show="toast.show" x-cloak
     x-transition:enter="transform ease-out duration-300"
     x-transition:enter-start="translate-y-2 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transform ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-2 opacity-0"
     class="fixed bottom-4 right-4 z-50">
    <div :class="{
            'bg-green-500': toast.type === 'success',
            'bg-red-500': toast.type === 'error',
            'bg-yellow-500': toast.type === 'warning',
            'bg-blue-500': toast.type === 'info'
         }" class="px-4 py-3 rounded-lg shadow-lg text-white flex items-center gap-3 min-w-[280px]">
        <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span class="text-sm font-medium" x-text="toast.message"></span>
    </div>
</div>

<script>
function clinicalCare() {
    return {
        appointmentId: <?= $appointment['id'] ?>,
        patientId: <?= $appointment['patient_id'] ?>,
        status: '<?= $appointment['status'] ?>',
        activeTab: '<?= $activeTab ?>',
        csrfToken: '<?= csrf_token() ?>',

        // Data
        procedures: <?= json_encode($procedures) ?>,
        procedureTotals: {
            subtotal: <?= $procedureTotals['subtotal'] ?>,
            tax: <?= $procedureTotals['tax'] ?>,
            total: <?= $procedureTotals['total'] ?>,
            pending: <?= $procedureTotals['pending_invoice'] ?>
        },

        // Clinical Note with Enhanced Dental Fields
        clinicalNote: <?= json_encode($clinicalNote ?: [
            'chief_complaint' => '', 
            'pain_type' => '', 
            'pain_intensity' => 0,
            'pain_duration' => '',
            'pain_radiation' => '',
            'subjective' => '', 
            'objective' => '', 
            'soft_tissues' => 'normal',
            'occlusion' => 'normal',
            'tmj' => 'normal',
            'assessment' => '', 
            'diagnoses' => [],
            'treatment_priority' => 'medium',
            'complexity' => 'simple',
            'plan' => '',
            'symptoms' => [
                'halitosis' => false,
                'bleeding_gums' => false,
                'tooth_sensitivity' => false,
                'swelling' => false,
                'difficulty_chewing' => false,
                'mobility' => false,
                'fracture' => false,
                'trauma' => false
            ]
        ]) ?>,
        noteStatus: '<?= $clinicalNote['status'] ?? 'draft' ?>',
        noteSaving: false,
        noteTemplate: 'general',

        // Enhanced Services for procedures
        services: <?= json_encode($services) ?>,
        serviceCategories: <?= json_encode($serviceCategories) ?>,
        selectedCategory: null,
        searchService: '',
        searchCode: '',
        priceMin: null,
        priceMax: null,
        appliesTo: '',
        complexity: '',
        quickAddMode: false,
        showAdvanced: false,
        showFDI: false,
        quickAdd: {
            code: '',
            tooth: ''
        },
        
        // Imaging data
        imagesCount: 0,
        
        // Periodontal data
        periodontalAlerts: 0,
        
        // Toast
        toast: { show: false, message: '', type: 'success' },

        init() {
            // Auto-save clinical notes every 30 seconds
            setInterval(() => {
                if (this.noteStatus === 'draft' && this.activeTab === 'notes') {
                    this.saveNote(true);
                }
            }, 30000);
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        async startAppointment() {
            if (!confirm('¿Iniciar atención del paciente?')) return;

            try {
                const response = await fetch(`/clinical/attend/${this.appointmentId}/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    }
                });
                const data = await response.json();

                if (data.success) {
                    this.status = 'in_progress';
                    this.showToast('Atención iniciada');
                    location.reload();
                } else {
                    this.showToast(data.message || 'Error al iniciar', 'error');
                }
            } catch (e) {
                this.showToast('Error de conexión', 'error');
            }
        },

        async completeAppointment() {
            if (!confirm('¿Finalizar la atención? Asegúrese de haber registrado todos los procedimientos.')) return;

            try {
                const response = await fetch(`/clinical/attend/${this.appointmentId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    }
                });
                const data = await response.json();

                if (data.success) {
                    this.status = 'completed';
                    this.showToast('Atención completada');
                    location.reload();
                } else {
                    this.showToast(data.message || 'Error al completar', 'error');
                }
            } catch (e) {
                this.showToast('Error de conexión', 'error');
            }
        },

        async saveNote(silent = false) {
            if (this.noteStatus === 'signed') return;

            this.noteSaving = true;
            try {
                const response = await fetch(`/api/clinical/appointments/${this.appointmentId}/note`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(this.clinicalNote)
                });
                const data = await response.json();

                if (data.success) {
                    if (!silent) this.showToast('Nota guardada');
                } else {
                    this.showToast(data.message || 'Error al guardar', 'error');
                }
            } catch (e) {
                if (!silent) this.showToast('Error de conexión', 'error');
            }
            this.noteSaving = false;
        },

        async signNote() {
            if (!confirm('¿Firmar la nota clínica? Una vez firmada no podrá ser modificada.')) return;

            await this.saveNote(true);

            try {
                const response = await fetch(`/api/clinical/appointments/${this.appointmentId}/note/sign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    }
                });
                const data = await response.json();

                if (data.success) {
                    this.noteStatus = 'signed';
                    this.showToast('Nota firmada correctamente');
                } else {
                    this.showToast(data.message || 'Error al firmar', 'error');
                }
            } catch (e) {
                this.showToast('Error de conexión', 'error');
            }
        },

        async addProcedure(serviceId) {
            const service = this.services.find(s => s.id == serviceId);
            if (!service) return;

            try {
                const response = await fetch(`/api/clinical/appointments/${this.appointmentId}/procedures`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({
                        appointment_type_id: serviceId,
                        unit_price: service.price_default || 0,
                        quantity: 1
                    })
                });
                const data = await response.json();

                if (data.success) {
                    this.procedures.push(data.data);
                    this.updateTotals();
                    this.showToast('Procedimiento agregado');
                } else {
                    this.showToast(data.message || 'Error al agregar', 'error');
                }
            } catch (e) {
                this.showToast('Error de conexión', 'error');
            }
        },

        async removeProcedure(procedureId) {
            if (!confirm('¿Eliminar este procedimiento?')) return;

            try {
                const response = await fetch(`/api/clinical/procedures/${procedureId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    }
                });
                const data = await response.json();

                if (data.success) {
                    this.procedures = this.procedures.filter(p => p.id != procedureId);
                    this.updateTotals();
                    this.showToast('Procedimiento eliminado');
                } else {
                    this.showToast(data.message || 'Error al eliminar', 'error');
                }
            } catch (e) {
                this.showToast('Error de conexión', 'error');
            }
        },

        updateTotals() {
            let subtotal = 0, tax = 0, pending = 0;
            this.procedures.forEach(p => {
                subtotal += parseFloat(p.subtotal || 0);
                tax += parseFloat(p.tax_amount || 0);
                if (!p.is_invoiced) {
                    pending += parseFloat(p.total || 0);
                }
            });
            this.procedureTotals = {
                subtotal: subtotal,
                tax: tax,
                total: subtotal + tax,
                pending: pending
            };
        },

        enhancedFilteredServices() {
            let filtered = this.services;

            if (this.selectedCategory) {
                filtered = filtered.filter(s => s.category_id == this.selectedCategory);
            }

            if (this.searchCode) {
                const searchCode = this.searchCode.toLowerCase();
                filtered = filtered.filter(function(s) {
                    return (s.code && s.code.toLowerCase().includes(searchCode)) ||
                           (s.fdi_code && s.fdi_code.toLowerCase().includes(searchCode));
                });
            }

            if (this.searchService) {
                const search = this.searchService.toLowerCase();
                filtered = filtered.filter(function(s) {
                    return s.name.toLowerCase().includes(search) ||
                           s.description.toLowerCase().includes(search);
                });
            }

            if (this.priceMin !== null && this.priceMax !== null) {
                filtered = filtered.filter(function(s) {
                    const price = parseFloat(s.price_default || 0);
                    return price >= this.priceMin && price <= this.priceMax;
                });
            }

            if (this.appliesTo) {
                filtered = filtered.filter(function(s) {
                    return true;
                });
            }

            if (this.complexity) {
                filtered = filtered.filter(function(s) {
                    return s.complexity === this.complexity;
                });
            }

            return filtered;
        },

        quickAddProcedure() {
            const code = this.quickAdd.code.trim();
            const tooth = this.quickAdd.tooth.trim();

            if (!code && !tooth) {
                this.$root.showToast('Ingrese código o número de diente', 'warning');
                return;
            }

            let service = null;
            if (code) {
                service = this.services.find(s =>
                    (s.code && s.code.toLowerCase() === code.toLowerCase()) ||
                    (s.fdi_code && s.fdi_code.toLowerCase() === code.toLowerCase())
                );
            }

            if (service) {
                this.addProcedure(service.id, tooth);
                this.quickAdd = { code: '', tooth: '' };
            } else {
                this.$root.showToast('Código no encontrado', 'error');
            }
        },

        async addProcedure(serviceId, toothNumber = null) {
            const service = this.services.find(s => s.id == serviceId);
            if (!service) return;

            try {
                const procedureData = {
                    appointment_type_id: serviceId,
                    unit_price: service.price_default || 0,
                    quantity: 1
                };

                if (toothNumber) {
                    procedureData.tooth_number = toothNumber;
                }

                const response = await fetch(`/api/clinical/appointments/${this.appointmentId}/procedures`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(procedureData)
                });
                const data = await response.json();

                if (data.success) {
                    this.procedures.push(data.data);
                    this.updateTotals();
                    this.$root.showToast('Procedimiento agregado: ' + service.name, 'success');
                } else {
                    this.$root.showToast(data.message || 'Error al agregar', 'error');
                }
            } catch (e) {
                this.$root.showToast('Error de conexión', 'error');
            }
        },

        getServiceClass(service) {
            let classes = 'border border-gray-100 hover:border-shalom-primary hover:bg-shalom-light/30';

            if (service.code && ['8100', '8200', '1110', '1120'].includes(service.code)) {
                classes += ' ring-2 ring-shalom-primary/20';
            }

            const price = parseFloat(service.price_default || 0);
            if (price > 100) {
                classes += ' bg-orange-50';
            } else if (price > 50) {
                classes += ' bg-yellow-50';
            }

            return classes;
        },

        applyNoteTemplate(templateKey) {
            const templates = {
                general: {
                    chief_complaint: '',
                    subjective: '',
                    objective: '',
                    assessment: '',
                    plan: ''
                },
                urgent: {
                    chief_complaint: '',
                    pain_intensity: 7,
                    subjective: '',
                    objective: '',
                    assessment: '',
                    plan: ''
                }
            };

            const template = templates[templateKey] || templates.general;
            Object.keys(template).forEach(key => {
                if (typeof template[key] === 'object') {
                    this.clinicalNote[key] = { ...this.clinicalNote[key], ...template[key] };
                } else {
                    this.clinicalNote[key] = template[key];
                }
            });
        },

        insertToothReference() {
            const toothNumber = prompt('Ingrese número de diente (ej: 11, 21, 36, 46):');
            if (toothNumber && this.clinicalNote.subjective) {
                this.clinicalNote.subjective += ` [Diente ${toothNumber}]`;
                this.$nextTick(() => {
                    const textarea = event.target.closest('div').querySelector('textarea[x-model="clinicalNote.subjective"]');
                    if (textarea) {
                        textarea.focus();
                        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                    }
                });
            }
        },

        insertDiagnosisCode() {
            const diagnosisCode = prompt('Ingrese código diagnóstico ICD-10:');
            if (diagnosisCode) {
                if (!this.clinicalNote.diagnoses) {
                    this.clinicalNote.diagnoses = [];
                }
                this.clinicalNote.diagnoses.push({ code: diagnosisCode, description: '' });
            }
        },

        insertProcedureCode() {
            const procedureCode = prompt('Ingrese código de procedimiento dental:');
            if (procedureCode) {
                if (!this.clinicalNote.plan) {
                    this.clinicalNote.plan = '';
                }
                this.clinicalNote.plan += ` [${procedureCode}]`;
                this.$nextTick(() => {
                    const textarea = event.target.closest('div').querySelector('textarea[x-model="clinicalNote.plan"]');
                    if (textarea) {
                        textarea.focus();
                        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                    }
                });
            }
        },

        addDiagnosis() {
            if (!this.clinicalNote.diagnoses) {
                this.clinicalNote.diagnoses = [];
            }
            this.clinicalNote.diagnoses.push({ code: '', description: '' });
        },

        removeDiagnosis(index) {
            this.clinicalNote.diagnoses.splice(index, 1);
        }
    };
}
</script>

<?php $this->endsection(); ?>
