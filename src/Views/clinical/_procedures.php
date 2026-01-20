<!-- Enhanced Dental Procedures Tab -->
<div class="space-y-4">
    <!-- Add Procedure Section with Dental Features -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Agregar Procedimiento Dental
                </h2>
                <div class="flex items-center gap-2">
                    <button @click="quickAddMode = !quickAddMode"
                            :class="quickAddMode ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-2 py-1 text-xs font-medium rounded-lg transition-colors">
                        🚀 Modo Rápido
                    </button>
                    <button @click="showAdvanced = !showAdvanced"
                            class="text-xs text-gray-500 hover:text-gray-700">
                        <span x-show="!showAdvanced">Mostrar Opciones</span>
                        <span x-show="showAdvanced">Ocultar Opciones</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <!-- Quick Add Mode -->
            <div x-show="quickAddMode" x-cloak class="bg-shalom-light rounded-lg p-4 border border-shalom-secondary/30">
                <h3 class="text-sm font-semibold text-shalom-primary mb-3">Modo Rápido - Agregar por Código</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Código Procedimiento</label>
                        <input type="text" x-model="quickAdd.code" 
                               @keyup.enter="quickAddProcedure()"
                               class="w-full border border-gray-200 rounded px-3 py-2 text-sm"
                               placeholder="Ej: 8100, 2100">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Pieza Dental</label>
                        <input type="text" x-model="quickAdd.tooth" 
                               @keyup.enter="quickAddProcedure()"
                               class="w-full border border-gray-200 rounded px-3 py-2 text-sm"
                               placeholder="Ej: 11, 16, 36">
                    </div>
                    <div class="flex items-end">
                        <button @click="quickAddProcedure()" 
                                class="w-full px-4 py-2 bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors">
                            Agregar Rápido
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Category Filter with FDI Support -->
            <div class="space-y-3">
                <label class="text-xs font-medium text-gray-700">Categoría Dental:</label>
                <div class="flex flex-wrap gap-2">
                    <button @click="selectedCategory = null"
                            :class="!selectedCategory ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
                        Todas las Categorías
                    </button>
                    <?php foreach ($serviceCategories as $cat): ?>
                    <button @click="selectedCategory = <?= $cat['id'] ?>"
                            :class="selectedCategory == <?= $cat['id'] ?> ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors flex items-center gap-1">
                        <span><?= htmlspecialchars($cat['name']) ?></span>
                        <span class="text-xs opacity-70" x-show="showFDI">FDI</span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Advanced Search with Multiple Filters -->
            <div x-show="showAdvanced" x-cloak class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Búsqueda por Nombre:</label>
                        <input type="text" x-model="searchService" 
                               class="w-full border border-gray-200 rounded px-3 py-2 text-sm"
                               placeholder="Nombre del procedimiento...">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Búsqueda por Código:</label>
                        <input type="text" x-model="searchCode" 
                               class="w-full border border-gray-200 rounded px-3 py-2 text-sm"
                               placeholder="Código CDT/ANS...">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Rango de Precio:</label>
                        <div class="flex items-center gap-2">
                            <input type="number" x-model="priceMin" 
                                   class="w-full border border-gray-200 rounded px-2 py-2 text-sm"
                                   placeholder="Mín">
                            <span class="text-gray-400">-</span>
                            <input type="number" x-model="priceMax" 
                                   class="w-full border border-gray-200 rounded px-2 py-2 text-sm"
                                   placeholder="Máx">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Aplica a:</label>
                        <select x-model="appliesTo" class="w-full border border-gray-200 rounded px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="deciduous">Dientes Deciduos</option>
                            <option value="permanent">Dientes Permanentes</option>
                            <option value="anterior">Anteriores</option>
                            <option value="posterior">Posteriores</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 mb-1">Complejidad:</label>
                        <select x-model="complexity" class="w-full border border-gray-200 rounded px-3 py-2 text-sm">
                            <option value="">Todas</option>
                            <option value="simple">Simple</option>
                            <option value="moderate">Moderada</option>
                            <option value="complex">Compleja</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Simple Search (when advanced is hidden) -->
            <div x-show="!showAdvanced" x-cloak>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           x-model="searchService"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary"
                           placeholder="Buscar procedimiento dental...">
                </div>
            </div>

            <!-- Enhanced Services Grid with FDI Notation -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-80 overflow-y-auto">
                <template x-for="service in filteredServices()" :key="service.id">
                    <button @click="addProcedure(service.id)"
                            :class="getServiceClass(service)"
                            class="flex items-center justify-between p-3 border border-gray-100 rounded-lg transition-all text-left hover:scale-105">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-medium text-gray-900 truncate" x-text="service.name"></div>
                                <template x-if="service.fdi_code">
                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded font-mono" x-text="service.fdi_code"></span>
                                </template>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="text-xs text-gray-500" x-text="service.code"></div>
                                <template x-if="service.tooth_range">
                                    <span class="text-xs text-gray-400" x-text="'Piezas: ' + service.tooth_range"></span>
                                </template>
                                <template x-if="service.duration">
                                    <span class="text-xs text-gray-400" x-text="service.duration + ' min'"></span>
                                </template>
                            </div>
                            <template x-if="service.description">
                                <div class="text-xs text-gray-600 mt-1 truncate" x-text="service.description"></div>
                            </template>
                        </div>
                        <div class="text-right ml-2">
                            <div class="text-sm font-semibold text-shalom-primary">
                                $<span x-text="(service.price_default || 0).toFixed(2)"></span>
                            </div>
                            <template x-if="service.tax_percentage > 0">
                                <div class="text-xs text-gray-500">
                                    +<span x-text="service.tax_percentage"></span>% IVA
                                </div>
                            </template>
                        </div>
                    </button>
                </template>
            </div>

            <template x-if="filteredServices().length === 0">
                <div class="text-center py-8 text-gray-500 text-sm">
                    No se encontraron servicios
                </div>
            </template>
        </div>
    </div>

    <!-- Procedures List -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Procedimientos Realizados
                <span class="text-gray-400 font-normal" x-text="'(' + procedures.length + ')'"></span>
            </h2>
        </div>

        <div class="divide-y divide-gray-100">
            <template x-if="procedures.length === 0">
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 text-sm">No hay procedimientos registrados</p>
                    <p class="text-gray-400 text-xs mt-1">Seleccione un servicio para agregarlo</p>
                </div>
            </template>

            <template x-for="procedure in procedures" :key="procedure.id">
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900" x-text="procedure.service_name"></span>
                                <span class="text-xs text-gray-400" x-text="procedure.service_code"></span>
                                <template x-if="procedure.is_invoiced">
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Facturado</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                                <template x-if="procedure.tooth_number">
                                    <span>Pieza: <span x-text="procedure.tooth_number"></span></span>
                                </template>
                                <template x-if="procedure.surfaces">
                                    <span>Superficies: <span x-text="procedure.surfaces"></span></span>
                                </template>
                                <span>Cant: <span x-text="procedure.quantity"></span></span>
                            </div>
                            <template x-if="procedure.description">
                                <p class="text-xs text-gray-500 mt-1" x-text="procedure.description"></p>
                            </template>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-semibold text-gray-900">$<span x-text="parseFloat(procedure.total || 0).toFixed(2)"></span></div>
                            <div class="text-xs text-gray-500">
                                <span x-text="parseFloat(procedure.unit_price || 0).toFixed(2)"></span> x <span x-text="procedure.quantity"></span>
                            </div>
                        </div>

                        <template x-if="!procedure.is_invoiced">
                            <button @click="removeProcedure(procedure.id)"
                                    class="text-red-400 hover:text-red-600 p-1 transition-colors"
                                    title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Totals -->
        <template x-if="procedures.length > 0">
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-end">
                    <div class="w-64 space-y-1 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>$<span x-text="procedureTotals.subtotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>IVA:</span>
                            <span>$<span x-text="procedureTotals.tax.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-900 pt-1 border-t border-gray-200">
                            <span>Total:</span>
                            <span>$<span x-text="procedureTotals.total.toFixed(2)"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
