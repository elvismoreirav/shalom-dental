<?php $this->extend('layouts.auth'); ?>

<?php $this->section('content'); ?>

<div x-data="loginHandler()" class="flex flex-col items-center">
    
    <div class="mb-8 p-4 bg-white rounded-full shadow-xl shadow-shalom-dark/20">
        <svg class="w-10 h-10 text-shalom-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/> 
            </svg>
    </div>

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white tracking-tight">Shalom Dental</h1>
        <p class="text-shalom-accent mt-2 text-sm">Sistema de Gestión Odontológica</p>
    </div>

    <div class="w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Iniciar Sesión</h2>

            <?php if (hasFlash('error')): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= e(getFlash('error')) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= url('login') ?>" method="POST" @submit="submitForm">
                <?= csrf_field() ?>

                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-shalom-gold focus:border-transparent transition-shadow sm:text-sm"
                            placeholder="nombre@ejemplo.com">
                    </div>
                </div>

                <div class="mb-6" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                            class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-shalom-gold focus:border-transparent transition-shadow sm:text-sm"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-shalom-primary focus:outline-none">
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 013.999-5.332M6.338 6.338a9.953 9.953 0 011.535-1.098A10.002 10.002 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.056 10.056 0 01-2.428 4.418M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-shalom-primary focus:ring-shalom-gold border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm text-gray-600 cursor-pointer">Recordarme</label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-shalom-primary hover:text-shalom-dark transition-colors">¿Contraseña olvidada?</a>
                    </div>
                </div>

                <button type="submit" :disabled="loading" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-shalom-primary hover:bg-shalom-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-shalom-gold disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Validando...' : 'Ingresar al Sistema'"></span>
                </button>
            </form>
        </div>
        
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-500">Acceso exclusivo para personal autorizado</p>
        </div>
    </div>
</div>

<script>
function loginHandler() {
    return {
        loading: false,
        submitForm() {
            this.loading = true;
            // El formulario se enviará y la página recargará
        }
    }
}
</script>

<?php $this->endSection(); ?>