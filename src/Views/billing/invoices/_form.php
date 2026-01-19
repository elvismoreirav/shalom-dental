<?php
/**
 * Professional Invoice Form
 * Modern design with service catalog, autocomplete, and real-time calculations
 */
$invoice = $invoice ?? [];
$items = $items ?? [];
$action = $action ?? '/billing/invoices';
$method = $method ?? 'POST';
$additionalInfo = $invoice['additional_info'] ?? [];
if (is_string($additionalInfo)) {
    $decoded = json_decode($additionalInfo, true);
    $additionalInfo = is_array($decoded) ? $decoded : [];
}

// Prepare patients data for auto-fill
$patientsData = [];
foreach (($patients ?? []) as $patient) {
    $patientsData[$patient['id']] = [
        'name' => trim($patient['last_name'] . ' ' . $patient['first_name']),
        'id_type' => $patient['id_type'] ?? '05',
        'id_number' => $patient['id_number'] ?? '',
        'email' => $patient['email'] ?? '',
        'phone' => $patient['phone'] ?? '',
        'address' => $patient['address'] ?? '',
    ];
}

// Prepare services catalog for autocomplete
$servicesData = [];
foreach (($appointmentTypes ?? []) as $service) {
    $servicesData[] = [
        'id' => $service['id'],
        'name' => $service['name'],
        'price' => (float) ($service['price_default'] ?? 0),
        'code' => $service['code'] ?? 'SERV',
        'tax' => (float) ($service['tax_percentage'] ?? 15),
    ];
}

// Payment methods
$paymentMethods = [
    ['code' => '01', 'name' => 'Sin utilizacion del sistema financiero', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
    ['code' => '15', 'name' => 'Compensacion de deudas', 'icon' => 'M4 7h16M4 12h10M4 17h16'],
    ['code' => '16', 'name' => 'Tarjeta de debito', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
    ['code' => '17', 'name' => 'Dinero electronico', 'icon' => 'M12 8c-2.2 0-4 1.8-4 4 0 2 1.5 3.6 3.4 3.9V18h1.2v-2.1c2.3-.3 3.9-2.1 3.9-4.3 0-2.1-1.8-3.6-4.5-3.6z'],
    ['code' => '18', 'name' => 'Tarjeta prepago', 'icon' => 'M5 8h14M7 12h10M6 16h12'],
    ['code' => '19', 'name' => 'Tarjeta de credito', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
    ['code' => '20', 'name' => 'Otros con utilizacion del sistema financiero', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
    ['code' => '21', 'name' => 'Endoso de titulos', 'icon' => 'M6 12h12M6 16h9M6 8h6'],
];
?>

<div
    x-data="invoiceApp(<?= htmlspecialchars(json_encode([
        'patients' => $patientsData,
        'services' => $servicesData,
        'paymentMethods' => $paymentMethods,
        'invoice' => $invoice,
        'invoiceDiscount' => $invoiceDiscount ?? 0,
        'additionalInfo' => $additionalInfo,
        'existingItems' => $items,
        'existingPayments' => $payments ?? [],
    ]), ENT_QUOTES, 'UTF-8') ?>)"
    x-init="initForm()"
    @beforeunload.window="handleBeforeUnload($event)"
    @keydown.window="handleKeyboardShortcut($event)"
    class="min-h-screen -m-6 bg-gray-100"
>
    <!-- Toast Notifications -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed bottom-4 right-4 z-50" x-cloak>
        <div :class="{
            'bg-green-500': toast.type === 'success',
            'bg-red-500': toast.type === 'error',
            'bg-yellow-500': toast.type === 'warning',
            'bg-blue-500': toast.type === 'info'
        }" class="px-4 py-3 rounded-lg shadow-lg text-white flex items-center gap-3 min-w-[280px]">
            <template x-if="toast.type === 'success'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </template>
            <template x-if="toast.type === 'error'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </template>
            <template x-if="toast.type === 'warning'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </template>
            <template x-if="toast.type === 'info'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </template>
            <span class="text-sm font-medium" x-text="toast.message"></span>
        </div>
    </div>
    <form action="<?= e($action) ?>" method="post" @submit="validateForm">
        <?= csrf_field() ?>
        <?php if (strtoupper($method) !== 'POST'): ?>
            <input type="hidden" name="_method" value="<?= e($method) ?>">
        <?php endif; ?>

        <!-- Top Bar -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-4">
                        <a href="/billing/invoices" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900">Nueva Factura</h1>
                            <p class="text-xs text-gray-500">Complete los datos para generar la factura</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Keyboard shortcuts help -->
                        <div class="hidden lg:flex items-center gap-1 text-xs text-gray-400 mr-2" title="Atajos de teclado">
                            <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-gray-500 font-mono">Ctrl+S</kbd>
                            <span>Borrador</span>
                            <span class="mx-1">|</span>
                            <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-gray-500 font-mono">Ctrl+L</kbd>
                            <span>Linea</span>
                        </div>
                        <button type="submit" name="action" value="draft" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Guardar Borrador
                        </button>
                        <button type="submit" name="action" value="emit" class="px-5 py-2 text-sm font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark transition-colors shadow-sm">
                            Emitir Factura
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Customer Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Cliente
                            </h2>
                        </div>
                        <div class="p-4">
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Cliente <span class="text-red-400">*</span></label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            type="text"
                                            x-model="patientSearch"
                                            @input="selectPatientFromSearch()"
                                            list="patientList"
                                            placeholder="Buscar por nombre, RUC o cédula..."
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary bg-white"
                                        >
                                        <datalist id="patientList">
                                            <?php foreach (($patients ?? []) as $patient): ?>
                                            <option value="<?= e($patient['last_name'] . ' ' . $patient['first_name']) ?> - <?= e($patient['id_number'] ?? '') ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <button
                                        type="button"
                                        @click="setConsumidorFinal()"
                                        class="px-3 py-2 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 hover:text-gray-800 transition-colors whitespace-nowrap"
                                        title="Llenar como Consumidor Final"
                                    >
                                        <span class="hidden sm:inline">Consumidor Final</span>
                                        <span class="sm:hidden">C.F.</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Nombre <span class="text-red-400">*</span></label>
                                    <input type="text" name="buyer_name" x-model="buyer.name" required placeholder="Nombre completo o razon social" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipo ID <span class="text-red-400">*</span></label>
                                    <select name="buyer_id_type" x-model="buyer.idType" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary bg-white">
                                        <option value="05">Cedula</option>
                                        <option value="04">RUC</option>
                                        <option value="06">Pasaporte</option>
                                        <option value="07">Consumidor Final</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Numero ID <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <input type="text" name="buyer_id_number" x-model="buyer.idNumber" @input="validateIdNumber()" required placeholder="Numero de identificacion" :class="{
                                            'border-green-400 focus:border-green-500 focus:ring-green-500/20': idValidation.valid === true,
                                            'border-red-400 focus:border-red-500 focus:ring-red-500/20': idValidation.valid === false,
                                            'border-gray-200 focus:border-shalom-primary focus:ring-shalom-primary/20': idValidation.valid === null
                                        }" class="w-full border rounded-lg px-3 py-2 text-sm pr-8 focus:ring-2">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                            <svg x-show="idValidation.valid === true" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <svg x-show="idValidation.valid === false" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </div>
                                    </div>
                                    <p x-show="idValidation.message" x-text="idValidation.message" :class="idValidation.valid ? 'text-green-600' : 'text-red-500'" class="text-xs mt-1"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                                    <input type="email" name="buyer_email" x-model="buyer.email" placeholder="correo@ejemplo.com" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Telefono</label>
                                    <input type="text" name="buyer_phone" x-model="buyer.phone" placeholder="0999999999" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Direccion</label>
                                    <input type="text" name="buyer_address" x-model="buyer.address" placeholder="Direccion del cliente" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                            </div>
                            <input type="hidden" name="patient_id" :value="selectedPatientId">
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Servicios
                            </h2>
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input
                                        type="text"
                                        x-model="serviceSearch"
                                        @input="filterServices()"
                                        @focus="showServiceDropdown = true"
                                        @keydown.escape="showServiceDropdown = false"
                                        placeholder="Buscar Servicio"
                                        class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary bg-white"
                                    >
                                    <div x-show="showServiceDropdown && filteredServices.length > 0" @click.away="showServiceDropdown = false" x-cloak class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-48 overflow-y-auto">
                                        <template x-for="service in filteredServices" :key="service.id">
                                            <button type="button" @click="quickAddService(service); showServiceDropdown = false; serviceSearch = ''" class="w-full px-4 py-2.5 text-left hover:bg-shalom-light/50 flex items-center justify-between border-b border-gray-50 last:border-0">
                                                <span class="text-sm text-gray-700" x-text="service.name"></span>
                                                <span class="text-sm font-medium text-shalom-primary" x-text="'$' + service.price.toFixed(2)"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" @click="addItem()" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Agregar Línea
                                </button>
                            </div>
                        </div>

                        <!-- Items Table - Desktop -->
                        <div class="overflow-x-auto hidden md:block">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wider">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Codigo</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Descripcion</th>
                                        <th class="px-3 py-2 text-center font-medium text-gray-500 w-20">Cant.</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 w-28">Precio</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 w-24">Desc.</th>
                                        <th class="px-3 py-2 text-center font-medium text-gray-500 w-20">IVA</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 w-28">Total</th>
                                        <th class="px-3 py-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2">
                                                <input type="text" :name="'items[' + index + '][main_code]'" x-model="item.code" placeholder="Codigo" class="w-full border-0 bg-transparent px-0 py-1 text-sm focus:ring-0 placeholder-gray-300">
                                                <input type="hidden" :name="'items[' + index + '][appointment_type_id]'" :value="item.appointmentTypeId || ''">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" placeholder="Descripcion del servicio" class="w-full border-0 bg-transparent px-0 py-1 text-sm focus:ring-0 placeholder-gray-300" required>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="calculateTotals()" min="0.0001" step="0.0001" class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-center focus:ring-1 focus:ring-shalom-primary/30 focus:border-shalom-primary">
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="relative">
                                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                    <input type="number" :name="'items[' + index + '][unit_price]'" x-model.number="item.price" @input="calculateTotals()" min="0" step="0.0001" class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm text-right focus:ring-1 focus:ring-shalom-primary/30 focus:border-shalom-primary">
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="relative">
                                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                    <input type="number" :name="'items[' + index + '][discount_amount]'" x-model.number="item.discount" @input="calculateTotals()" min="0" step="0.01" class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm text-right focus:ring-1 focus:ring-shalom-primary/30 focus:border-shalom-primary">
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <select :name="'items[' + index + '][tax_percentage]'" x-model.number="item.tax" @change="calculateTotals()" class="w-full border border-gray-200 rounded px-1 py-1 text-sm text-center focus:ring-1 focus:ring-shalom-primary/30 focus:border-shalom-primary bg-white">
                                                    <option value="0">0%</option>
                                                    <option value="12">12%</option>
                                                    <option value="15">15%</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-700">
                                                $<span x-text="getLineTotal(item).toFixed(2)"></span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Items Cards - Mobile -->
                        <div class="md:hidden p-4 space-y-3">
                            <template x-for="(item, index) in items" :key="'mobile-' + index">
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1 pr-2">
                                            <input type="text" x-model="item.description" placeholder="Descripcion del servicio" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary font-medium" required>
                                        </div>
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 mb-2">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Cant.</label>
                                            <input type="number" x-model.number="item.quantity" @input="calculateTotals()" min="0.0001" step="1" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Precio</label>
                                            <div class="relative">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                <input type="number" x-model.number="item.price" @input="calculateTotals()" min="0" step="0.01" class="w-full bg-white border border-gray-200 rounded-lg pl-5 pr-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">IVA</label>
                                            <select x-model.number="item.tax" @change="calculateTotals()" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                                <option value="0">0%</option>
                                                <option value="12">12%</option>
                                                <option value="15">15%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Desc:</span>
                                            <div class="relative w-20">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                <input type="number" x-model.number="item.discount" @input="calculateTotals()" min="0" step="0.01" class="w-full bg-white border border-gray-200 rounded px-1 pl-5 py-1 text-xs text-right focus:ring-1 focus:ring-shalom-primary/30">
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-500">Total:</span>
                                            <span class="text-sm font-bold text-shalom-primary ml-1">$<span x-text="getLineTotal(item).toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                    <!-- Hidden inputs for mobile -->
                                    <input type="hidden" :name="'items[' + index + '][main_code]'" x-model="item.code">
                                    <input type="hidden" :name="'items[' + index + '][description]'" x-model="item.description">
                                    <input type="hidden" :name="'items[' + index + '][quantity]'" x-model="item.quantity">
                                    <input type="hidden" :name="'items[' + index + '][unit_price]'" x-model="item.price">
                                    <input type="hidden" :name="'items[' + index + '][discount_amount]'" x-model="item.discount">
                                    <input type="hidden" :name="'items[' + index + '][tax_percentage]'" x-model="item.tax">
                                    <input type="hidden" :name="'items[' + index + '][appointment_type_id]'" :value="item.appointmentTypeId || ''">
                                </div>
                            </template>
                        </div>

                        <div class="px-4 py-3 border-t border-gray-100 text-xs text-gray-400">
                            Cantidad y precio con 4 decimales.
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a6 6 0 100-12 6 6 0 000 12z"/>
                                </svg>
                                Informacion Adicional
                            </h2>
                            <button type="button" @click="addAdditionalInfo()" class="inline-flex items-center justify-center w-9 h-9 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </button>
                        </div>
                        <div class="p-4 space-y-2">
                            <p class="text-xs text-gray-400">Agregue informacion adicional que aparecera en el comprobante (maximo 15 campos).</p>
                            <template x-for="(info, index) in additionalInfo" :key="index">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'additional_info[' + index + '][name]'" x-model="info.name" placeholder="Nombre" class="w-1/3 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                    <input type="text" :name="'additional_info[' + index + '][value]'" x-model="info.value" placeholder="Valor" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                    <button type="button" @click="removeAdditionalInfo(index)" class="p-2 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Forma de Pago
                            </h2>
                            <button
                                type="button"
                                @click="autoFillPayment()"
                                class="px-3 py-1.5 text-xs font-medium text-shalom-primary bg-shalom-light/50 border border-shalom-primary/20 rounded-lg hover:bg-shalom-light transition-colors"
                                title="Llenar con el total de la factura"
                            >
                                Llenar Total
                            </button>
                        </div>
                        <div class="p-4">
                            <!-- Quick payment method buttons -->
                            <div class="flex flex-wrap gap-2 mb-4 pb-4 border-b border-gray-100">
                                <span class="text-xs text-gray-500 mr-2 self-center">Metodo rapido:</span>
                                <button type="button" @click="setQuickPayment('01')" :class="payments[0]?.code === '01' ? 'bg-shalom-primary text-white border-shalom-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-shalom-primary hover:text-shalom-primary'" class="px-3 py-1.5 text-xs font-medium border rounded-full transition-colors flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Efectivo
                                </button>
                                <button type="button" @click="setQuickPayment('19')" :class="payments[0]?.code === '19' ? 'bg-shalom-primary text-white border-shalom-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-shalom-primary hover:text-shalom-primary'" class="px-3 py-1.5 text-xs font-medium border rounded-full transition-colors flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    T. Credito
                                </button>
                                <button type="button" @click="setQuickPayment('16')" :class="payments[0]?.code === '16' ? 'bg-shalom-primary text-white border-shalom-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-shalom-primary hover:text-shalom-primary'" class="px-3 py-1.5 text-xs font-medium border rounded-full transition-colors flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    T. Debito
                                </button>
                                <button type="button" @click="setQuickPayment('20')" :class="payments[0]?.code === '20' ? 'bg-shalom-primary text-white border-shalom-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-shalom-primary hover:text-shalom-primary'" class="px-3 py-1.5 text-xs font-medium border rounded-full transition-colors flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    Transferencia
                                </button>
                            </div>
                            <template x-for="(payment, index) in payments" :key="index">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Forma de Pago</label>
                                        <select :name="'payments[' + index + '][payment_method_code]'" x-model="payment.code" @change="setPaymentMethodByCode(payment, payment.code)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary bg-white">
                                            <template x-for="method in paymentMethods" :key="method.code">
                                                <option :value="method.code" x-text="method.name"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" :name="'payments[' + index + '][payment_method_name]'" :value="payment.name">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Monto</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                            <input type="number" :name="'payments[' + index + '][amount]'" x-model.number="payment.amount" @input="calculateBalance()" step="0.01" min="0" class="w-full pl-8 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary" placeholder="Monto">
                                        </div>
                                    </div>
                                    <div class="w-36">
                                        <label class="block text-xs text-gray-500 mb-1">Plazo (dias)</label>
                                        <input type="number" :name="'payments[' + index + '][term_days]'" x-model.number="payment.termDays" min="0" step="1" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary" placeholder="0">
                                        <input type="hidden" :name="'payments[' + index + '][time_unit]'" value="dias">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Referencia</label>
                                        <input type="text" :name="'payments[' + index + '][reference_number]'" x-model="payment.reference" placeholder="Referencia (opcional)" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                    </div>
                                    <button type="button" @click="removePayment(index)" x-show="payments.length > 1" class="p-2 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>

                            <button type="button" @click="addPayment()" class="text-sm text-shalom-primary hover:text-shalom-dark font-medium mt-2">
                                + Agregar otro metodo de pago
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Invoice Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-4">
                        <!-- Document Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Documento</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Establecimiento</label>
                                    <input type="text" value="<?= e(($currentLocation['sri_establishment_code'] ?? '001') . ' - Matriz') ?>" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Punto de Emision</label>
                                    <?php
                                    $establishmentCode = $currentLocation['sri_establishment_code'] ?? '001';
                                    $hasEmissionPoints = !empty($emissionPoints);
                                    $defaultEmissionPointId = $invoice['emission_point_id'] ?? ($hasEmissionPoints ? $emissionPoints[0]['id'] : 1);
                                    ?>
                                    <select name="emission_point_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary bg-white">
                                        <?php if ($hasEmissionPoints): ?>
                                            <?php foreach ($emissionPoints as $ep): ?>
                                            <option value="<?= e((string) $ep['id']) ?>" <?= (int)$defaultEmissionPointId === (int)$ep['id'] ? 'selected' : '' ?>><?= e($establishmentCode . '-' . $ep['code']) ?> - <?= e($ep['code'] === '001' ? 'Principal' : 'Secundario') ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="1" selected><?= e($establishmentCode) ?>-001 - Principal</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Fecha</label>
                                    <input type="date" name="issue_date" value="<?= e($invoice['issue_date'] ?? date('Y-m-d')) ?>" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Fecha de vencimiento</label>
                                    <input type="date" name="due_date" value="<?= e($invoice['due_date'] ?? '') ?>" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Guia de Remision</label>
                                    <input type="text" name="remission_guide" value="<?= e($additionalInfo['remission_guide'] ?? '') ?>" placeholder="000-000-000000000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Discounts & Tip -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Descuentos y Propina</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Descuento global (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" x-model.number="discountRate" @input="invoiceDiscount = (subtotalNoTax * (discountRate / 100)); calculateTotals()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Descuento global ($)</label>
                                    <input type="number" name="invoice_discount" step="0.01" min="0" x-model.number="invoiceDiscount" @input="discountRate = 0; calculateTotals()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Propina</label>
                                    <input type="number" name="tip" step="0.01" min="0" x-model.number="tip" @input="calculateTotals()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Total ICE</label>
                                    <input type="number" name="total_ice" step="0.01" min="0" x-model.number="totalIce" @input="calculateTotals()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-shalom-primary/20 focus:border-shalom-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Totals Summary -->
                        <div class="bg-gradient-to-br from-shalom-primary to-shalom-dark rounded-xl shadow-lg p-4 text-white">
                            <h3 class="text-sm font-medium text-white/80 mb-4">Resumen</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-white/70">
                                    <span>Subtotal sin impuestos</span>
                                    <span>$<span x-text="subtotalNoTax.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70">
                                    <span>Subtotal IVA 0%</span>
                                    <span>$<span x-text="subtotal0.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70" x-show="subtotal12 > 0">
                                    <span>Subtotal IVA 12%</span>
                                    <span>$<span x-text="subtotal12.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70" x-show="subtotal15 > 0">
                                    <span>Subtotal IVA 15%</span>
                                    <span>$<span x-text="subtotal15.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70">
                                    <span>Total descuento</span>
                                    <span>$<span x-text="totalDiscount.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70">
                                    <span>IVA</span>
                                    <span>$<span x-text="totalTax.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70" x-show="totalIce > 0">
                                    <span>ICE</span>
                                    <span>$<span x-text="totalIce.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-white/70" x-show="tip > 0">
                                    <span>Propina</span>
                                    <span>$<span x-text="tip.toFixed(2)"></span></span>
                                </div>
                                <div class="border-t border-white/20 pt-2 mt-2">
                                    <div class="flex justify-between text-sm text-white/80">
                                        <span>Total con impuestos</span>
                                        <span>$<span x-text="totalWithTax.toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total con impuestos + propina</span>
                                        <span>$<span x-text="total.toFixed(2)"></span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance indicator -->
                            <div class="mt-4 pt-4 border-t border-white/20">
                                <div class="flex justify-between text-sm" :class="balance === 0 ? 'text-green-300' : (balance > 0 ? 'text-yellow-300' : 'text-red-300')">
                                    <span x-text="balance > 0 ? 'Por cobrar:' : (balance < 0 ? 'Cambio:' : 'Pagado:')"></span>
                                    <span class="font-medium">$<span x-text="Math.abs(balance).toFixed(2)"></span></span>
                                </div>
                                <div x-show="balance === 0 && totalPayments > 0" class="mt-2 flex items-center gap-1 text-green-300 text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Pago completo
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden totals for form submission -->
        <input type="hidden" name="subtotal_no_tax" :value="subtotalNoTax.toFixed(2)">
        <input type="hidden" name="subtotal_0" :value="subtotal0.toFixed(2)">
        <input type="hidden" name="subtotal_12" :value="subtotal12.toFixed(2)">
        <input type="hidden" name="subtotal_15" :value="subtotal15.toFixed(2)">
        <input type="hidden" name="total_discount" :value="totalDiscount.toFixed(2)">
        <input type="hidden" name="total_tax" :value="totalTax.toFixed(2)">
        <input type="hidden" name="total_ice" :value="totalIce.toFixed(2)">
        <input type="hidden" name="total_with_tax" :value="totalWithTax.toFixed(2)">
        <input type="hidden" name="total" :value="total.toFixed(2)">
    </form>
</div>

<script>
function invoiceApp(config) {
    return {
        // Data
        patients: config.patients || {},
        services: config.services || [],
        paymentMethods: config.paymentMethods || [],
        filteredServices: [],
        serviceSearch: '',
        showServiceDropdown: false,

        // Form state
        selectedPatientId: config.invoice?.patient_id || '',
        patientSearch: '',
        buyer: {
            name: config.invoice?.buyer_name || '',
            idType: config.invoice?.buyer_id_type || '05',
            idNumber: config.invoice?.buyer_id_number || '',
            email: config.invoice?.buyer_email || '',
            phone: config.invoice?.buyer_phone || '',
            address: config.invoice?.buyer_address || ''
        },
        items: [],
        payments: [],
        additionalInfo: [],

        // Calculated
        subtotalNoTax: 0,
        subtotal0: 0,
        subtotal12: 0,
        subtotal15: 0,
        totalDiscount: 0,
        totalTax: 0,
        totalIce: parseFloat(config.invoice?.total_ice || 0) || 0,
        tip: parseFloat(config.invoice?.tip || 0) || 0,
        totalWithTax: 0,
        total: 0,
        totalPayments: 0,
        balance: 0,
        discountRate: 0,
        invoiceDiscount: parseFloat(config.invoiceDiscount || 0) || 0,

        // UX Enhancements
        toast: { show: false, message: '', type: 'success' },
        hasUnsavedChanges: false,
        isSubmitting: false,
        idValidation: { valid: null, message: '' },

        initForm() {
            this.init();
            this.$watch('buyer', () => { this.hasUnsavedChanges = true; this.validateIdNumber(); }, { deep: true });
            this.$watch('items', () => { this.hasUnsavedChanges = true; }, { deep: true });
            this.$watch('payments', () => { this.hasUnsavedChanges = true; }, { deep: true });
        },

        init() {
            // Initialize items
            if (config.existingItems && config.existingItems.length > 0) {
                this.items = config.existingItems.map(item => ({
                    description: item.description || '',
                    quantity: parseFloat(item.quantity) || 1,
                    price: parseFloat(item.unit_price) || 0,
                    discount: parseFloat(item.discount_amount) || 0,
                    tax: parseFloat(item.tax_percentage) || 15,
                    code: item.main_code || 'SERV',
                    appointmentTypeId: item.appointment_type_id || ''
                }));
            } else {
                this.items = [{ description: '', quantity: 1, price: 0, discount: 0, tax: 15, code: 'SERV', appointmentTypeId: '' }];
            }

            // Initialize payments
            if (config.existingPayments && config.existingPayments.length > 0) {
                this.payments = config.existingPayments.map(p => ({
                    code: p.payment_method_code || '01',
                    name: p.payment_method_name || 'Sin utilizacion del sistema financiero',
                    amount: parseFloat(p.amount) || 0,
                    termDays: parseInt(p.term_days, 10) || 0,
                    reference: p.reference_number || ''
                }));
            } else {
                this.payments = [{ code: '01', name: 'Sin utilizacion del sistema financiero', amount: 0, termDays: 0, reference: '' }];
            }

            if (config.additionalInfo && Array.isArray(config.additionalInfo)) {
                this.additionalInfo = config.additionalInfo.map(info => ({
                    name: info.name || '',
                    value: info.value || ''
                }));
            }

            this.filteredServices = this.services;
            if (this.selectedPatientId && this.patients[this.selectedPatientId]) {
                const p = this.patients[this.selectedPatientId];
                this.patientSearch = `${p.name} - ${p.id_number}`;
            }
            this.calculateTotals();
        },

        fillFromPatient() {
            if (this.selectedPatientId && this.patients[this.selectedPatientId]) {
                const p = this.patients[this.selectedPatientId];
                this.buyer.name = p.name;
                this.buyer.idType = p.id_type || '05';
                this.buyer.idNumber = p.id_number;
                this.buyer.email = p.email;
                this.buyer.phone = p.phone;
                this.buyer.address = p.address;
            }
        },

        setConsumidorFinal() {
            this.selectedPatientId = '';
            this.patientSearch = '';
            this.buyer.name = 'CONSUMIDOR FINAL';
            this.buyer.idType = '07';
            this.buyer.idNumber = '9999999999999';
            this.buyer.email = '';
            this.buyer.phone = '';
            this.buyer.address = '';
            this.showToast('Cliente configurado como Consumidor Final', 'success');
        },

        selectPatientFromSearch() {
            const query = this.patientSearch.toLowerCase();
            if (!query) {
                this.selectedPatientId = '';
                return;
            }
            const matchId = Object.keys(this.patients).find(id => {
                const p = this.patients[id];
                const needle = `${p.name} - ${p.id_number}`.toLowerCase();
                return needle === query;
            });
            if (matchId) {
                this.selectedPatientId = matchId;
                this.fillFromPatient();
            }
        },

        filterServices() {
            const query = this.serviceSearch.toLowerCase();
            this.filteredServices = this.services.filter(s =>
                s.name.toLowerCase().includes(query)
            );
            this.showServiceDropdown = true;
        },

        quickAddService(service) {
            // Check if empty item exists to fill
            const emptyIndex = this.items.findIndex(i => !i.description);
            if (emptyIndex >= 0) {
                this.items[emptyIndex] = {
                    description: service.name,
                    quantity: 1,
                    price: service.price,
                    discount: 0,
                    tax: service.tax ?? 15,
                    code: service.code || 'SERV',
                    appointmentTypeId: service.id || ''
                };
            } else {
                this.items.push({
                    description: service.name,
                    quantity: 1,
                    price: service.price,
                    discount: 0,
                    tax: service.tax ?? 15,
                    code: service.code || 'SERV',
                    appointmentTypeId: service.id || ''
                });
            }
            this.calculateTotals();
        },

        addItem() {
            this.items.push({ description: '', quantity: 1, price: 0, discount: 0, tax: 15, code: 'SERV', appointmentTypeId: '' });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },

        getLineTotal(item) {
            const subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
            const discount = Math.min(Math.max(parseFloat(item.discount) || 0, 0), subtotal);
            const net = subtotal - discount;
            const tax = net * ((parseFloat(item.tax) || 0) / 100);
            return net + tax;
        },

        calculateTotals() {
            this.subtotalNoTax = 0;
            this.subtotal0 = 0;
            this.subtotal12 = 0;
            this.subtotal15 = 0;
            this.totalDiscount = 0;
            this.totalTax = 0;
            this.totalIce = parseFloat(this.totalIce) || 0;

            const lineBases = this.items.map(item => {
                const subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
                const discount = Math.min(Math.max(parseFloat(item.discount) || 0, 0), subtotal);
                const net = subtotal - discount;
                return { item, subtotal, discount, net };
            });

            const totalNet = lineBases.reduce((sum, line) => sum + line.net, 0);
            const invoiceDiscount = Math.min(Math.max(parseFloat(this.invoiceDiscount) || 0, 0), totalNet);

            this.totalDiscount = lineBases.reduce((sum, line) => sum + line.discount, 0) + invoiceDiscount;

            lineBases.forEach(line => {
                const share = totalNet > 0 ? (line.net / totalNet) * invoiceDiscount : 0;
                const netAfter = Math.max(0, line.net - share);
                const taxPct = parseFloat(line.item.tax) || 0;
                const taxAmount = netAfter * (taxPct / 100);

                this.subtotalNoTax += netAfter;
                if (taxPct === 0) this.subtotal0 += netAfter;
                if (taxPct === 12) this.subtotal12 += netAfter;
                if (taxPct === 15) this.subtotal15 += netAfter;

                this.totalTax += taxAmount;
            });

            this.totalWithTax = this.subtotalNoTax + this.totalTax + this.totalIce;
            this.total = this.totalWithTax + (parseFloat(this.tip) || 0);
            this.calculateBalance();
        },

        setPaymentMethod(method) {
            if (this.payments.length > 0) {
                this.payments[0].code = method.code;
                this.payments[0].name = method.name;
            }
        },

        addPayment() {
            const remaining = Math.max(0, this.balance);
            this.payments.push({ code: '01', name: 'Sin utilizacion del sistema financiero', amount: remaining, termDays: 0, reference: '' });
            this.calculateBalance();
        },

        removePayment(index) {
            if (this.payments.length > 1) {
                this.payments.splice(index, 1);
                this.calculateBalance();
            }
        },

        calculateBalance() {
            this.totalPayments = this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
            this.balance = this.total - this.totalPayments;
        },

        autoFillPayment() {
            if (this.payments.length > 0 && this.total > 0) {
                this.payments[0].amount = this.total;
                this.calculateBalance();
                this.showToast('Monto actualizado a $' + this.total.toFixed(2), 'success');
            }
        },

        setQuickPayment(code) {
            const method = this.paymentMethods.find(m => m.code === code);
            if (method && this.payments.length > 0) {
                this.payments[0].code = code;
                this.payments[0].name = method.name;
                if (this.total > 0 && this.payments[0].amount === 0) {
                    this.payments[0].amount = this.total;
                }
                this.calculateBalance();
            }
        },

        clearForm() {
            this.selectedPatientId = '';
            this.buyer = { name: '', idType: '05', idNumber: '', email: '', phone: '', address: '' };
            this.items = [{ description: '', quantity: 1, price: 0, discount: 0, tax: 15, code: 'SERV', appointmentTypeId: '' }];
            this.payments = [{ code: '01', name: 'Sin utilizacion del sistema financiero', amount: 0, termDays: 0, reference: '' }];
            this.tip = 0;
            this.invoiceDiscount = 0;
            this.discountRate = 0;
            this.additionalInfo = [];
            this.calculateTotals();
        },

        setPaymentMethodByCode(payment, code) {
            const method = this.paymentMethods.find(m => m.code === code);
            if (method) {
                payment.name = method.name;
            }
        },

        addAdditionalInfo() {
            if (this.additionalInfo.length >= 15) return;
            this.additionalInfo.push({ name: '', value: '' });
        },

        removeAdditionalInfo(index) {
            this.additionalInfo.splice(index, 1);
        },

        validateForm(e) {
            // Check buyer info first
            if (!this.buyer.name.trim()) {
                this.showToast('Ingrese el nombre del cliente', 'error');
                e.preventDefault();
                return false;
            }

            if (!this.buyer.idNumber.trim()) {
                this.showToast('Ingrese el numero de identificacion', 'error');
                e.preventDefault();
                return false;
            }

            // Validate ID number format
            if (!this.isValidIdNumber()) {
                this.showToast('Numero de identificacion invalido', 'error');
                e.preventDefault();
                return false;
            }

            // Check at least one valid item
            const validItems = this.items.filter(i => i.description && i.quantity > 0 && i.price >= 0);
            if (validItems.length === 0) {
                this.showToast('Agregue al menos un servicio valido', 'error');
                e.preventDefault();
                return false;
            }

            // Check payment
            if (this.balance > 0.01) {
                this.showToast('El monto de pago es menor al total. Falta: $' + this.balance.toFixed(2), 'warning');
            }

            this.isSubmitting = true;
            this.hasUnsavedChanges = false;
            return true;
        },

        // Toast notification system
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        // Before unload handler
        handleBeforeUnload(e) {
            if (this.hasUnsavedChanges && !this.isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Tiene cambios sin guardar. ¿Esta seguro que desea salir?';
                return e.returnValue;
            }
        },

        // ID validation methods
        validateIdNumber() {
            const id = this.buyer.idNumber.trim();
            const type = this.buyer.idType;

            if (!id) {
                this.idValidation = { valid: null, message: '' };
                return;
            }

            if (type === '07') { // Consumidor Final
                this.idValidation = { valid: true, message: '' };
                return;
            }

            if (type === '05') { // Cedula
                if (id.length !== 10) {
                    this.idValidation = { valid: false, message: 'Cedula debe tener 10 digitos' };
                    return;
                }
                if (!/^\d{10}$/.test(id)) {
                    this.idValidation = { valid: false, message: 'Cedula solo debe contener numeros' };
                    return;
                }
                if (this.validateCedulaEcuador(id)) {
                    this.idValidation = { valid: true, message: 'Cedula valida' };
                } else {
                    this.idValidation = { valid: false, message: 'Cedula invalida' };
                }
                return;
            }

            if (type === '04') { // RUC
                if (id.length !== 13) {
                    this.idValidation = { valid: false, message: 'RUC debe tener 13 digitos' };
                    return;
                }
                if (!/^\d{13}$/.test(id)) {
                    this.idValidation = { valid: false, message: 'RUC solo debe contener numeros' };
                    return;
                }
                if (id.endsWith('001')) {
                    this.idValidation = { valid: true, message: 'RUC valido' };
                } else {
                    this.idValidation = { valid: false, message: 'RUC debe terminar en 001' };
                }
                return;
            }

            if (type === '06') { // Pasaporte
                if (id.length >= 3) {
                    this.idValidation = { valid: true, message: '' };
                } else {
                    this.idValidation = { valid: false, message: 'Pasaporte muy corto' };
                }
                return;
            }

            this.idValidation = { valid: null, message: '' };
        },

        validateCedulaEcuador(cedula) {
            const digits = cedula.split('').map(Number);
            const province = parseInt(cedula.substring(0, 2));
            if (province < 1 || province > 24) return false;

            const thirdDigit = digits[2];
            if (thirdDigit > 5) return false;

            const coefficients = [2, 1, 2, 1, 2, 1, 2, 1, 2];
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                let val = digits[i] * coefficients[i];
                if (val >= 10) val -= 9;
                sum += val;
            }
            const checkDigit = (10 - (sum % 10)) % 10;
            return checkDigit === digits[9];
        },

        isValidIdNumber() {
            const id = this.buyer.idNumber.trim();
            const type = this.buyer.idType;

            if (type === '07') return id.length > 0;
            if (type === '05') return /^\d{10}$/.test(id);
            if (type === '04') return /^\d{13}$/.test(id) && id.endsWith('001');
            if (type === '06') return id.length >= 3;
            return id.length > 0;
        },

        // Keyboard shortcuts handler
        handleKeyboardShortcut(e) {
            // Ctrl/Cmd + S = Save (Guardar Borrador)
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const draftBtn = document.querySelector('button[value="draft"]');
                if (draftBtn) {
                    this.showToast('Guardando borrador...', 'info');
                    draftBtn.click();
                }
                return;
            }

            // Ctrl/Cmd + Enter = Emitir Factura
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                const emitBtn = document.querySelector('button[value="emit"]');
                if (emitBtn) {
                    emitBtn.click();
                }
                return;
            }

            // Ctrl/Cmd + L = Agregar linea
            if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
                e.preventDefault();
                this.addItem();
                this.showToast('Linea agregada', 'info');
                // Focus on the last item description
                this.$nextTick(() => {
                    const inputs = document.querySelectorAll('input[name*="[description]"]');
                    if (inputs.length > 0) {
                        inputs[inputs.length - 1].focus();
                    }
                });
                return;
            }

            // Ctrl/Cmd + P = Llenar pago total
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                this.autoFillPayment();
                return;
            }

            // Ctrl/Cmd + F = Consumidor Final
            if ((e.ctrlKey || e.metaKey) && e.key === 'f' && e.shiftKey) {
                e.preventDefault();
                this.setConsumidorFinal();
                return;
            }
        }
    }
}
</script>
