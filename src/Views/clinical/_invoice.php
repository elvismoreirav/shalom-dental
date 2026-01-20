<!-- Invoice Tab - Generate invoice from procedures -->
<div class="space-y-4" x-data="invoiceGenerator()" x-init="loadPendingProcedures()">
    <div class="bg-white shadow rounded-lg border border-gray-100 p-4 text-sm text-gray-500 flex flex-wrap items-center gap-3">
        <span class="font-medium text-gray-700">Guia de facturacion:</span>
        <span>Selecciona procedimientos</span>
        <span>Elige punto de emision</span>
        <span>Genera factura en borrador</span>
    </div>

    <!-- Pending Procedures to Invoice -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Procedimientos Pendientes de Facturar
            </h2>
        </div>

        <div class="p-4">
            <template x-if="pendingProcedures.length === 0">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">No hay procedimientos pendientes de facturar</p>
                    <p class="text-gray-400 text-xs mt-1">Todos los procedimientos han sido facturados</p>
                    <button type="button" @click="$root.activeTab = 'procedures'" class="mt-4 inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                        Volver a procedimientos
                    </button>
                </div>
            </template>

            <template x-if="pendingProcedures.length > 0">
                <div>
                    <!-- Select All -->
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-3 border-b border-gray-100">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   @change="toggleSelectAll($event)"
                                   :checked="selectedProcedures.length === pendingProcedures.length"
                                   class="w-4 h-4 text-shalom-primary border-gray-300 rounded focus:ring-shalom-primary">
                            <span class="text-sm text-gray-700">Seleccionar todos</span>
                        </label>
                        <span class="text-xs text-gray-500">
                            <span x-text="selectedProcedures.length"></span> de <span x-text="pendingProcedures.length"></span> seleccionados
                        </span>
                    </div>

                    <!-- Procedures List -->
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="proc in pendingProcedures" :key="proc.id">
                            <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg cursor-pointer hover:border-shalom-primary transition-colors"
                                   :class="selectedProcedures.includes(proc.id) ? 'bg-shalom-light/30 border-shalom-primary' : ''">
                                <input type="checkbox"
                                       :value="proc.id"
                                       @change="toggleProcedure(proc.id)"
                                       :checked="selectedProcedures.includes(proc.id)"
                                       class="w-4 h-4 text-shalom-primary border-gray-300 rounded focus:ring-shalom-primary">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900" x-text="proc.service_name"></div>
                                    <div class="text-xs text-gray-500">
                                        <span x-text="proc.service_code"></span>
                                        <template x-if="proc.tooth_number">
                                            <span> - Pieza <span x-text="proc.tooth_number"></span></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    $<span x-text="parseFloat(proc.total || 0).toFixed(2)"></span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Invoice Summary -->
    <template x-if="selectedProcedures.length > 0">
        <div class="bg-white shadow rounded-lg border border-gray-100">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Resumen de Factura
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Revisa cliente, seleccion y totales antes de emitir.</p>
                    </div>
                    <span class="text-xs text-gray-500">Seleccionados: <span x-text="selectedProcedures.length"></span></span>
                </div>
            </div>

            <div class="p-4">
                <!-- Buyer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="text-xs text-gray-500">Cliente</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Identificación</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($appointment['patient_id_type']) ?>: <?= htmlspecialchars($appointment['patient_id_number']) ?></div>
                    </div>
                </div>

                <!-- Selected Items Summary -->
                <div class="space-y-2 mb-4">
                    <template x-for="proc in getSelectedProcedureDetails()" :key="proc.id">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600" x-text="proc.service_name"></span>
                            <span class="font-medium">$<span x-text="parseFloat(proc.total || 0).toFixed(2)"></span></span>
                        </div>
                    </template>
                </div>

                <!-- Totals -->
                <div class="border-t border-gray-100 pt-4 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal:</span>
                        <span>$<span x-text="invoiceTotals.subtotal.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>IVA (<span x-text="invoiceTotals.taxPercentage"></span>%):</span>
                        <span>$<span x-text="invoiceTotals.tax.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Total:</span>
                        <span>$<span x-text="invoiceTotals.total.toFixed(2)"></span></span>
                    </div>
                </div>

                <!-- Emission Point -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Punto de Emisión <span class="text-red-400">*</span>
                    </label>
                    <select x-model="emissionPointId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                        <option value="">Seleccionar punto de emisión</option>
                        <?php
                        // Get emission points for current location
                        $db = \App\Core\Database::getInstance();
                        $locationId = (int) session('current_location_id', 0);
                        $emissionPoints = $db->select(
                            "SELECT ep.id, ep.code, ep.description, l.sri_establishment_code
                             FROM emission_points ep
                             JOIN locations l ON ep.location_id = l.id
                             WHERE ep.location_id = ? AND ep.is_active = 1",
                            [$locationId]
                        );
                        foreach ($emissionPoints as $ep):
                        ?>
                        <option value="<?= $ep['id'] ?>"><?= htmlspecialchars($ep['sri_establishment_code'] . '-' . $ep['code'] . ($ep['description'] ? ' - ' . $ep['description'] : '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Generate Button -->
                <div class="mt-4">
                    <button @click="generateInvoice()"
                            :disabled="!emissionPointId || generating"
                            class="w-full px-4 py-3 text-sm font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            <template x-if="generating">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!generating">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </template>
                            <span x-text="generating ? 'Generando...' : 'Generar Factura'"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Help Card -->
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
        <h4 class="text-sm font-medium text-blue-800 mb-2">Facturación desde Atención Clínica</h4>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>Seleccione los procedimientos que desea facturar.</li>
            <li>Puede facturar todos los procedimientos juntos o hacer facturas parciales.</li>
            <li>Una vez facturado, el procedimiento no puede ser eliminado.</li>
            <li>La factura se generará en estado borrador para su revisión.</li>
        </ul>
    </div>
</div>

<script>
function invoiceGenerator() {
    return {
        pendingProcedures: [],
        selectedProcedures: [],
        emissionPointId: '',
        generating: false,

        get invoiceTotals() {
            let subtotal = 0, tax = 0;
            this.getSelectedProcedureDetails().forEach(p => {
                subtotal += parseFloat(p.subtotal || 0);
                tax += parseFloat(p.tax_amount || 0);
            });
            return {
                subtotal: subtotal,
                tax: tax,
                total: subtotal + tax,
                taxPercentage: tax > 0 && subtotal > 0 ? Math.round((tax / subtotal) * 100) : 15
            };
        },

        async loadPendingProcedures() {
            // Filter from parent's procedures
            this.pendingProcedures = this.$root.procedures.filter(p => !p.is_invoiced);
            // Select all by default
            this.selectedProcedures = this.pendingProcedures.map(p => p.id);
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedProcedures = this.pendingProcedures.map(p => p.id);
            } else {
                this.selectedProcedures = [];
            }
        },

        toggleProcedure(id) {
            const index = this.selectedProcedures.indexOf(id);
            if (index > -1) {
                this.selectedProcedures.splice(index, 1);
            } else {
                this.selectedProcedures.push(id);
            }
        },

        getSelectedProcedureDetails() {
            return this.pendingProcedures.filter(p => this.selectedProcedures.includes(p.id));
        },

        async generateInvoice() {
            if (!this.emissionPointId) {
                this.$root.showToast('Seleccione un punto de emisión', 'error');
                return;
            }

            if (this.selectedProcedures.length === 0) {
                this.$root.showToast('Seleccione al menos un procedimiento', 'error');
                return;
            }

            if (!confirm('¿Generar factura con los procedimientos seleccionados?')) return;

            this.generating = true;

            try {
                const response = await fetch(`/api/clinical/appointments/${this.$root.appointmentId}/invoice/generate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.$root.csrfToken
                    },
                    body: JSON.stringify({
                        procedure_ids: this.selectedProcedures,
                        emission_point_id: this.emissionPointId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.$root.showToast('Factura generada: ' + (data.invoice_number || ''));

                    // Mark procedures as invoiced in parent
                    this.selectedProcedures.forEach(id => {
                        const proc = this.$root.procedures.find(p => p.id === id);
                        if (proc) proc.is_invoiced = true;
                    });

                    // Update pending list
                    this.pendingProcedures = this.pendingProcedures.filter(p => !this.selectedProcedures.includes(p.id));
                    this.selectedProcedures = [];

                    // Update totals
                    this.$root.updateTotals();

                    // Optionally redirect to invoice
                    if (data.invoice_id && confirm('¿Desea ver la factura generada?')) {
                        window.open('/billing/invoices/' + data.invoice_id, '_blank');
                    }
                } else {
                    this.$root.showToast(data.message || 'Error al generar factura', 'error');
                }
            } catch (e) {
                this.$root.showToast('Error de conexión', 'error');
            }

            this.generating = false;
        }
    }
}
</script>
