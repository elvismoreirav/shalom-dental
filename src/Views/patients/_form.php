<?php
/** @var array $patient */
/** @var string $action */
/** @var string $method */
$patient = $patient ?? [];
$isEdit = !empty($patient['id']);
?>
<?php $errors = getFlash('errors', []); ?>

<!-- Enhanced Patient Form with Modern UX/UI -->
<form action="<?= e($action ?? '/patients') ?>" method="post" class="space-y-8" x-data="patientForm(<?= htmlspecialchars(json_encode([
    'patient' => $patient,
    'isEdit' => $isEdit,
    'action' => $action ?? '/patients',
    'method' => $method ?? 'POST',
]), ENT_QUOTES, 'UTF-8') ?>)" @submit.prevent="submitForm()" x-init="console.log('Form initialized!', $data)">
    <?= csrf_field() ?>
    <?php if (!empty($method) && strtoupper($method) !== 'POST'): ?>
        <input type="hidden" name="_method" value="<?= e($method) ?>">
    <?php endif; ?>



    
    

    
    
    <div x-data="{ show: false }" class="mb-4 p-3 bg-yellow-100 border border-yellow-400 rounded">
        <div class="flex items-center justify-between">
            <span class="text-sm text-yellow-800">
                <strong>Debug:</strong> Alpine.js Status:
                <span x-text="typeof Alpine !== 'undefined' ? '✓ Loaded' : '✗ Not Loaded'"></span>
                | Current Step: <span x-text="currentStep"></span>
                | Is Submitting: <span x-text="isSubmitting"></span>
            </span>
            <button type="button" @click="show = !show" class="text-yellow-800 text-sm underline">
                <span x-show="!show">Show Test</span>
                <span x-show="show">Hide Test</span>
            </button>
        </div>
        <div x-show="show" class="mt-2 text-xs text-yellow-700">
            <p>Test Form Submission:</p>
            <button type="button" @click="console.log('Test button clicked'); $el.querySelector('button[type=submit]').click()" class="px-2 py-1 bg-yellow-200 rounded text-xs">
                Click to Trigger Submit
            </button>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="relative">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-colors duration-200"
                         :class="currentStep >= 1 ? 'bg-shalom-primary text-white' : 'bg-gray-200 text-gray-500'">
                        1
                    </div>
                    <span class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-shalom-primary' : 'text-gray-500'">
                        Información Básica
                    </span>
                </div>
                <div class="w-16 h-0.5 bg-gray-200 mx-4" :class="currentStep > 1 ? 'bg-shalom-primary' : ''"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-colors duration-200"
                         :class="currentStep >= 2 ? 'bg-shalom-primary text-white' : 'bg-gray-200 text-gray-500'">
                        2
                    </div>
                    <span class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-shalom-primary' : 'text-gray-500'">
                        Contacto
                    </span>
                </div>
                <div class="w-16 h-0.5 bg-gray-200 mx-4" :class="currentStep > 2 ? 'bg-shalom-primary' : ''"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-colors duration-200"
                         :class="currentStep >= 3 ? 'bg-shalom-primary text-white' : 'bg-gray-200 text-gray-500'">
                        3
                    </div>
                    <span class="ml-2 text-sm font-medium" :class="currentStep >= 3 ? 'text-shalom-primary' : 'text-gray-500'">
                        Adicional
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 1: Basic Information -->
    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Información Personal
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div class="relative">
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" id="first_name" name="first_name" 
                               x-model="formData.first_name"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 <?= isset($errors['first_name']) ? 'border-red-500 ring-red-500' : '' ?>"
                               placeholder="Ingrese el nombre" required>
                    </div>
                    <?php if (!empty($errors['first_name'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <?= e($errors['first_name']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Last Name -->
                <div class="relative">
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Apellido <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" id="last_name" name="last_name" 
                               x-model="formData.last_name"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 <?= isset($errors['last_name']) ? 'border-red-500 ring-red-500' : '' ?>"
                               placeholder="Ingrese el apellido" required>
                    </div>
                    <?php if (!empty($errors['last_name'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <?= e($errors['last_name']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- ID Type -->
                <div class="relative">
                    <label for="id_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Identificación
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <select id="id_type" name="id_type" 
                                x-model="formData.id_type"
                                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 appearance-none bg-white">
                            <option value="cedula">Cédula</option>
                            <option value="ruc">RUC</option>
                            <option value="pasaporte">Pasaporte</option>
                            <option value="otro">Otro</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ID Number -->
                <div class="relative">
                    <label for="id_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Número de Identificación <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </div>
                        <input type="text" id="id_number" name="id_number" 
                               x-model="formData.id_number"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 <?= isset($errors['id_number']) ? 'border-red-500 ring-red-500' : '' ?>"
                               placeholder="Ingrese el número" required>
                    </div>
                    <?php if (!empty($errors['id_number'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <?= e($errors['id_number']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Birth Date -->
                <div class="relative">
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha de Nacimiento
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="date" id="birth_date" name="birth_date" 
                               x-model="formData.birth_date"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200">
                    </div>
                </div>

                <!-- Gender -->
                <div class="relative">
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                        Género
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <select id="gender" name="gender" 
                                x-model="formData.gender"
                                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 appearance-none bg-white">
                            <option value="">Seleccionar</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Contact Information -->
    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Información de Contacto
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Email -->
                <div class="relative">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" 
                               x-model="formData.email"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200"
                               placeholder="correo@ejemplo.com">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Opcional - Para enviar notificaciones</p>
                </div>

                <!-- Phone -->
                <div class="relative">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <input type="tel" id="phone" name="phone" 
                               x-model="formData.phone"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 <?= isset($errors['phone']) ? 'border-red-500 ring-red-500' : '' ?>"
                               placeholder="+593 999 999 999" required>
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <?= e($errors['phone']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Additional Information -->
    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Información Adicional
                </h3>
                <button type="button" @click="showOptional = !showOptional" 
                        class="text-sm text-shalom-primary hover:text-shalom-dark transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="showOptional ? 'Ocultar campos opcionales' : 'Mostrar campos opcionales'"></span>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Address -->
                <div class="relative md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-start pt-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="address" name="address" 
                               x-model="formData.address"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200"
                               placeholder="Calle Principal #123">
                    </div>
                </div>

                <!-- City -->
                <div class="relative">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                        Ciudad
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <input type="text" id="city" name="city" 
                               x-model="formData.city"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200"
                               placeholder="Quito">
                    </div>
                </div>

                <!-- Province -->
                <div class="relative">
                    <label for="province" class="block text-sm font-medium text-gray-700 mb-2">
                        Provincia
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="province" name="province" 
                               x-model="formData.province"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200"
                               placeholder="Pichincha">
                    </div>
                </div>

                <!-- Notes -->
                <div class="relative md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notas Adicionales
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <textarea id="notes" name="notes" rows="4" 
                                  x-model="formData.notes"
                                  class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 resize-none"
                                  placeholder="Alergias, condiciones médicas, preferencias, etc."></textarea>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Información relevante para el tratamiento dental</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
        <div class="flex items-center space-x-3">
            <button type="button"
                    x-show="currentStep > 1"
                    @click="currentStep--"
                    :disabled="isSubmitting"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 flex items-center disabled:opacity-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Anterior
            </button>

            <a href="/patients" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary transition-colors duration-200 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancelar
            </a>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button"
                    x-show="currentStep < 3"
                    @click="goNext()"
                    :disabled="isSubmitting"
                    class="px-6 py-2 text-sm font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark focus:outline-none focus:ring-2 focus:ring-shalom-primary focus:ring-offset-2 transition-colors duration-200 flex items-center disabled:opacity-50">
                Siguiente
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>


            <!-- Save button - visible on ALL steps so users can save at any time -->
            <button type="submit"
                    :disabled="isSubmitting"
                    class="px-6 py-2 text-sm font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark focus:outline-none focus:ring-2 focus:ring-shalom-primary focus:ring-offset-2 transition-colors duration-200 flex items-center disabled:opacity-50">
                <span x-show="isSubmitting" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando...
                </span>
                <span x-show="!isSubmitting" class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar Paciente
                </span>
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccessModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showSuccessModal = false"></div>

            <div x-show="showSuccessModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom bg-white rounded-xl shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                <!-- Success Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div class="mt-4 text-center">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="successTitle"></h3>
                    <p class="mt-2 text-sm text-gray-500" x-text="successMessage"></p>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a :href="'/patients/' + createdPatientId"
                       class="flex-1 inline-flex justify-center items-center px-4 py-3 text-sm font-medium text-white bg-shalom-primary rounded-lg hover:bg-shalom-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-shalom-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver Paciente
                    </a>
                    <button type="button"
                            @click="createAnother()"
                            x-show="!isEdit"
                            class="flex-1 inline-flex justify-center items-center px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-shalom-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Crear Otro
                    </button>
                    <a href="/patients"
                       class="flex-1 inline-flex justify-center items-center px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-shalom-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Ver Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-4 right-4 z-50"
         x-cloak>
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
            <span class="text-sm font-medium" x-text="toast.message"></span>
        </div>
    </div>
</form>

<script>
console.log('patientForm script loaded');

function patientForm(config) {
    console.log('patientForm called with config:', config);

    return {
        currentStep: 1,
        totalSteps: 3,
        showOptional: false,
        isSubmitting: false,
        isEdit: config.isEdit || false,
        action: config.action || '/patients',
        method: config.method || 'POST',

        formData: {
            first_name: config.patient?.first_name || '',
            last_name: config.patient?.last_name || '',
            id_type: config.patient?.id_type || 'cedula',
            id_number: config.patient?.id_number || '',
            email: config.patient?.email || '',
            phone: config.patient?.phone || '',
            birth_date: config.patient?.birth_date || '',
            gender: config.patient?.gender || '',
            address: config.patient?.address || '',
            city: config.patient?.city || '',
            province: config.patient?.province || '',
            notes: config.patient?.notes || ''
        },

        // Success modal
        showSuccessModal: false,
        successTitle: '',
        successMessage: '',
        createdPatientId: null,

        // Toast notification
        toast: { show: false, message: '', type: 'success' },

        goNext: function() {
            console.log('goNext called, currentStep:', this.currentStep);
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
            }
        },

        showToast: function(message, type) {
            type = type || 'success';
            console.log('showToast called:', message, type);
            this.toast = { show: true, message: message, type: type };
            setTimeout(function() {
                this.toast.show = false;
            }.bind(this), 4000);
        },

        validateForm: function() {
            if (!this.formData.first_name.trim()) {
                this.showToast('El nombre es requerido', 'error');
                this.currentStep = 1;
                return false;
            }
            if (!this.formData.last_name.trim()) {
                this.showToast('El apellido es requerido', 'error');
                this.currentStep = 1;
                return false;
            }
            if (!this.formData.id_number.trim()) {
                this.showToast('El número de identificación es requerido', 'error');
                this.currentStep = 1;
                return false;
            }
            if (!this.formData.phone.trim()) {
                this.showToast('El teléfono es requerido', 'error');
                this.currentStep = 2;
                return false;
            }
            return true;
        },

        submitForm: async function(event) {
            console.log('=== SUBMIT START ===');
            console.log('submitForm called!');
            console.log('Event:', event);
            console.log('currentStep:', this.currentStep);
            console.log('isSubmitting:', this.isSubmitting);
            console.log('formData:', this.formData);
            console.log('action:', this.action);

            if (!this.validateForm()) {
                console.log('=== VALIDATION FAILED ===');
                return;
            }

            console.log('=== VALIDATION PASSED ===');
            console.log('Setting isSubmitting to true');
            this.isSubmitting = true;

            var self = this;
            try {
                // Create FormData and add all form fields
                const formData = new FormData();

                // Add CSRF token from the form
                const csrfInput = this.$el.querySelector('input[name="_csrf_token"]');
                if (csrfInput) {
                    formData.set('_csrf_token', csrfInput.value);
                } else {
                    console.error('CSRF token input not found in form');
                    this.isSubmitting = false;
                    this.showToast('Error de seguridad. Recargue la página e intente nuevamente.', 'error');
                    return;
                }

                // Add all form data from Alpine state
                Object.keys(this.formData).forEach(function(key) {
                    formData.set(key, self.formData[key] || '');
                });

                // Add method override if needed
                if (this.method.toUpperCase() !== 'POST') {
                    formData.set('_method', this.method);
                }

                console.log('Submitting form to:', this.action, 'with data:', Object.fromEntries(formData));

                var response;
                var timeout = false;

                var controller = new AbortController();
                var timeoutId = setTimeout(function() {
                    controller.abort();
                    timeout = true;
                }, 30000); // 30 second timeout

                try {
                    response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);
                    console.log('Response status:', response.status, 'OK:', response.ok);

                } catch (fetchError) {
                    if (timeout) {
                        console.error('Request timeout:', fetchError);
                        this.isSubmitting = false;
                        this.showToast('La solicitud tardó demasiado. Intente nuevamente.', 'error');
                        return;
                    }
                    throw fetchError;
                }

                // Check if response is JSON
                var contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                if (!contentType || !contentType.includes('application/json')) {
                    // Server returned non-JSON (probably an error page)
                    var text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    console.error('Content-Type:', contentType);
                    console.error('Full response:', text);
                    this.isSubmitting = false;
                    this.showToast('Error del servidor. Intente nuevamente.', 'error');
                    return;
                }

                var data;
                try {
                    data = await response.json();
                    console.log('Response data:', data);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    console.error('Response text:', await response.text());
                    this.isSubmitting = false;
                    this.showToast('Error al procesar la respuesta del servidor.', 'error');
                    return;
                }

                if (data.success) {
                    this.createdPatientId = data.patient_id;
                    this.successTitle = this.isEdit ? 'Paciente Actualizado' : 'Paciente Creado';
                    this.successMessage = data.message || (this.isEdit
                        ? 'Los datos del paciente han sido actualizados correctamente.'
                        : 'El paciente ha sido registrado exitosamente en el sistema.');
                    this.showSuccessModal = true;
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        var firstError = Object.values(data.errors)[0];
                        this.showToast(firstError, 'error');
                    } else {
                        this.showToast(data.message || 'Error al guardar el paciente', 'error');
                    }
                }
            } catch (error) {
                console.error('Submit error:', error);
                console.error('Error name:', error.name);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                this.isSubmitting = false;
                this.showToast('Error de conexión. Intente nuevamente. (' + error.message + ')', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        createAnother() {
            // Reset form
            this.formData = {
                first_name: '',
                last_name: '',
                id_type: 'cedula',
                id_number: '',
                email: '',
                phone: '',
                birth_date: '',
                gender: '',
                address: '',
                city: '',
                province: '',
                notes: ''
            };
            this.currentStep = 1;
            this.showSuccessModal = false;
            this.createdPatientId = null;

            // Clear local storage draft
            localStorage.removeItem('patient_draft');

            // Focus on first field
            this.$nextTick(() => {
                const firstInput = document.getElementById('first_name');
                if (firstInput) firstInput.focus();
            });
        }
    };
}
</script>
