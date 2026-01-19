<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Layout Principal
 * ============================================================================
 * Archivo: src/Views/layouts/app.php
 * Descripción: Layout principal para páginas autenticadas
 * ============================================================================
 */

/** @var \Shalom\Core\View $this */
/** @var string $title */
/** @var array $currentLocation */
/** @var array $userLocations */
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e($csrfToken ?? '') ?>">
    
    <title><?= e($title ?? 'Dashboard') ?> - <?= e($appName ?? 'Shalom Dental') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'shalom': {
                            'primary': '#1E4D3A',
                            'secondary': '#A3B7A5', 
                            'accent': '#D6C29A',
                            'light': '#F5F5F0',
                            'dark': '#1A3D2E',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Estilos personalizados -->
    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-link.active {
            background-color: rgba(166, 183, 165, 0.2);
            border-left: 3px solid #1E4D3A;
        }
        
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
        }
        
        /* Enhanced Form Styles */
        .form-input {
            transition: all 0.2s ease-in-out;
        }
        
        .form-input:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .form-input.error {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Progress Indicator Styles */
        .step-indicator {
            transition: all 0.3s ease-in-out;
        }
        
        .step-indicator.active {
            transform: scale(1.1);
        }
        
        /* Card Hover Effects */
        .hover-card {
            transition: all 0.3s ease-in-out;
        }
        
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #1E4D3A 0%, #2A5F4A 100%);
            transition: all 0.3s ease-in-out;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2A5F4A 0%, #1E4D3A 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(30, 77, 58, 0.3);
        }
        
        /* Input Group Styles */
        .input-group {
            position: relative;
        }
        
        .input-group input:focus + .input-icon,
        .input-group input:not(:placeholder-shown) + .input-icon {
            color: #1E4D3A;
        }
        
        /* Success Animation */
        @keyframes success-pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        
        .success-animation {
            animation: success-pulse 1s ease-in-out;
        }
        
        /* Loading Spinner */
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #1E4D3A;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .step-indicator {
                font-size: 0.75rem;
            }
            
            .form-input {
                font-size: 16px; /* Prevent zoom on iOS */
            }
        }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <?= $this->yield('styles') ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <div x-data="{ sidebarOpen: true, userMenuOpen: false, locationMenuOpen: false }" class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20'" 
            class="bg-shalom-primary text-white transition-all duration-300 flex flex-col"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-shalom-dark">
                <span x-show="sidebarOpen" class="text-xl font-bold">Shalom Dental</span>
                <span x-show="!sidebarOpen" class="text-xl font-bold">SD</span>
            </div>
            
            <!-- Navegación -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1">
                    <!-- Dashboard -->
                    <?php if (can('reports.dashboard.view_all') || can('reports.dashboard.view_own')): ?>
                    <li>
                        <a href="/dashboard" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= $this->active('/dashboard') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Agenda -->
                    <?php if (can('agenda.appointments.view_all') || can('agenda.appointments.view_own')): ?>
                    <li>
                        <a href="/agenda" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/agenda') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Agenda</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Pacientes -->
                    <?php if (can('patients.patients.view')): ?>
                    <li>
                        <a href="/patients" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/patients') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Pacientes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Facturación -->
                    <?php if (can('billing.invoices.view_all') || can('billing.invoices.view_own')): ?>
                    <li>
                        <a href="/billing" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/billing') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Facturación</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Reportes -->
                    <?php if (can('reports.dashboard.view_all') || can('reports.dashboard.view_own')): ?>
                    <li>
                        <a href="/reports" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/reports') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Reportes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Configuración -->
                    <?php if (can('config.organization.view')): ?>
                    <li>
                        <a href="/config" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/config') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Configuración</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Notificaciones -->
                    <?php if (can('notifications.logs.view') || can('notifications.templates.view') || can('notifications.config.manage')): ?>
                    <li>
                        <a href="/notifications/logs" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/notifications') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Notificaciones</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Auditoría -->
                    <?php if (can('audit.logs.view')): ?>
                    <li>
                        <a href="/audit" class="sidebar-link flex items-center px-4 py-3 hover:bg-shalom-dark/30 transition <?= activeStartsWith('/audit') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v2a2 2 0 002 2h2a2 2 0 002-2v-2m-6 0a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v10a2 2 0 01-2 2m-6 0h6"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Auditoría</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <!-- Toggle sidebar -->
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="p-4 border-t border-shalom-dark hover:bg-shalom-dark/30 transition"
            >
                <svg x-show="sidebarOpen" class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <svg x-show="!sidebarOpen" class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6">
                <!-- Left: Title/Breadcrumb -->
                <div>
                    <h1 class="text-xl font-semibold text-gray-800"><?= e($title ?? 'Dashboard') ?></h1>
                </div>
                
                <!-- Right: Location & User -->
                <div class="flex items-center space-x-4">
                    <!-- Selector de Sede -->
                    <?php if (isset($userLocations) && count($userLocations) > 1): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            class="flex items-center px-3 py-2 bg-shalom-light rounded-lg hover:bg-shalom-secondary/30 transition"
                        >
                            <svg class="w-4 h-4 mr-2 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">
                                <?= e($currentLocation['name'] ?? 'Seleccionar sede') ?>
                            </span>
                            <svg class="w-4 h-4 ml-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-cloak
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border z-50"
                        >
                            <div class="py-1">
                                <?php foreach ($userLocations as $location): ?>
                                <button 
                                    onclick="switchLocation(<?= $location['id'] ?>)"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center <?= ($currentLocation['id'] ?? 0) == $location['id'] ? 'bg-shalom-light' : '' ?>"
                                >
                                    <?php if (($currentLocation['id'] ?? 0) == $location['id']): ?>
                                    <svg class="w-4 h-4 mr-2 text-shalom-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <?php else: ?>
                                    <span class="w-4 h-4 mr-2"></span>
                                    <?php endif; ?>
                                    <?= e($location['name']) ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Menú de Usuario -->
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            class="flex items-center space-x-3 hover:bg-gray-100 rounded-lg p-2 transition"
                        >
                            <div class="w-8 h-8 rounded-full bg-shalom-primary flex items-center justify-center text-white font-medium">
                                <?= strtoupper(substr(user()['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-700">
                                    <?= e((user()['first_name'] ?? '') . ' ' . (user()['last_name'] ?? '')) ?>
                                </p>
                                <p class="text-xs text-gray-500"><?= e(user()['role_name'] ?? '') ?></p>
                            </div>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50"
                        >
                            <div class="py-1">
                                <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mi Perfil
                                </a>
                                <a href="/profile/password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Cambiar Contraseña
                                </a>
                                <hr class="my-1">
                                <form action="/logout" method="POST" class="block">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                <?php if (hasFlash('success')): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <?= e(getFlash('success')) ?>
                    </div>
                    <button @click="show = false" class="text-green-700 hover:text-green-900">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if (hasFlash('error')): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center justify-between" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <?= e(getFlash('error')) ?>
                    </div>
                    <button @click="show = false" class="text-red-700 hover:text-red-900">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Contenido de la página -->
                <?= $this->yield('content') ?>
            </main>
        </div>
    </div>
    
    <!-- Scripts globales -->
    <script>
        // Token CSRF para peticiones AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        // Función para cambiar de sede
        async function switchLocation(locationId) {
            try {
                const response = await fetch('/api/auth/switch-location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken,
                    },
                    body: JSON.stringify({ location_id: locationId }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al cambiar de sede');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cambiar de sede');
            }
        }
        
        // Helper para peticiones AJAX
        async function api(url, options = {}) {
            const defaults = {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
            };
            
            const config = { ...defaults, ...options };
            if (options.headers) {
                config.headers = { ...defaults.headers, ...options.headers };
            }
            
            const response = await fetch(url, config);
            return response.json();
        }
    </script>
    
    <!-- Enhanced Form Validation -->
    <script>
        // Form validation helper
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500', 'ring-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500', 'ring-red-500');
                }
            });
            
            return isValid;
        }
        
        // Phone number formatting
        function formatPhoneNumber(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + ' ' + value.slice(3);
                } else {
                    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 10);
                }
            }
            input.value = value;
        }
        
        // Auto-save draft
        let autoSaveTimer;
        function autoSaveForm(form) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const formData = new FormData(form);
                localStorage.setItem('patient_draft', JSON.stringify(Object.fromEntries(formData)));
                console.log('Form auto-saved');
            }, 2000);
        }
        
        // Initialize form enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Phone formatting
            document.querySelectorAll('input[type="tel"]').forEach(input => {
                input.addEventListener('input', () => formatPhoneNumber(input));
            });
            
            // Auto-save for patient forms
            document.querySelectorAll('form').forEach(form => {
                if (form.action.includes('/patients')) {
                    form.addEventListener('input', () => autoSaveForm(form));
                    
                    // Restore draft if exists
                    const draft = localStorage.getItem('patient_draft');
                    if (draft) {
                        try {
                            const draftData = JSON.parse(draft);
                            Object.keys(draftData).forEach(key => {
                                const field = form.querySelector(`[name="${key}"]`);
                                if (field && !field.value) {
                                    field.value = draftData[key];
                                }
                            });
                        } catch (e) {
                            console.error('Failed to restore draft:', e);
                        }
                    }
                }
            });
        });
    </script>
    
    <?= $this->yield('scripts') ?>
</body>
<div class="flex-shrink-0 flex flex-col bg-primaryHover border-t border-primary/20">
    
    <div class="p-4 flex items-center w-full">
        </div>

    <div class="px-4 pb-2 text-center">
        <a href="https://tu-agencia.com" target="_blank" class="text-[10px] text-green-200/50 hover:text-green-100 transition-colors block py-1 border-t border-white/10">
            Powered by <strong>Shalom</strong>
        </a>
    </div>
</div>
</html>
