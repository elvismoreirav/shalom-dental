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
        
        /* Enhanced Sidebar Styles */
        .sidebar-link {
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .sidebar-link:hover::before {
            left: 100%;
        }
        
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(166, 183, 165, 0.3) 0%, rgba(166, 183, 165, 0.2) 100%);
            border-left: 3px solid #A3B7A5;
            font-weight: 600;
        }
        
        .sidebar-link.active::after {
            content: '';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background: #A3B7A5;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(163, 183, 165, 0.8);
        }
        
        /* Notification badges animations */
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }
        
        .absolute.w-4.h-4 {
            animation: pulse-badge 2s infinite;
        }
        
        /* Quick actions hover effects */
        .grid button:hover svg {
            transform: translateY(-2px);
        }
        
        .grid button {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Mobile sidebar improvements */
        @media (max-width: 640px) {
            .sidebar-link {
                padding: 1rem;
            }
            
            .grid button {
                font-size: 0.7rem;
                padding: 0.5rem;
            }
            
            /* Mobile navigation enhancements */
            .sidebar-link span:not([x-show]) {
                display: none;
            }
            
            .sidebar-link.active::after {
                display: none;
            }
            
            /* Mobile header improvements */
            .h-16 {
                height: 3.5rem;
            }
            
            /* Mobile touch targets */
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
            
            /* Mobile form improvements */
            .form-input {
                font-size: 16px; /* Prevent zoom on iOS */
                padding: 12px;
            }
            
            /* Mobile grid adjustments */
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
            
            .lg\\:grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .lg\\:grid-cols-2 {
                grid-template-columns: 1fr;
            }
            
            /* Mobile card improvements */
            .hover-card {
                margin-bottom: 1rem;
            }
            
            /* Mobile text sizes */
            h1 {
                font-size: 1.5rem;
            }
            
            h2 {
                font-size: 1.25rem;
            }
            
            h3 {
                font-size: 1.125rem;
            }
            
            /* Mobile table improvements */
            table {
                font-size: 0.875rem;
            }
            
            .min-w-full {
                min-width: 600px; /* Allow horizontal scroll */
            }
            
            /* Mobile button improvements */
            button {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .px-4 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            .py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
        }
        
        /* Tablet improvements */
        @media (min-width: 641px) and (max-width: 1024px) {
            .lg\\:grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .lg\\:grid-cols-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Touch device improvements */
        @media (hover: none) {
            .sidebar-link:hover {
                background-color: inherit;
            }
            
            .sidebar-link:active {
                background-color: rgba(166, 183, 165, 0.2);
            }
            
            .hover-card:hover {
                transform: none;
                box-shadow: inherit;
            }
            
            .hover-card:active {
                transform: scale(0.98);
            }
        }
        
        /* Enhanced Loading States */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .skeleton-text {
            height: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.25rem;
        }
        
        .skeleton-text.title {
            height: 1.5rem;
            width: 60%;
        }
        
        .skeleton-text.subtitle {
            height: 1rem;
            width: 40%;
        }
        
        .skeleton-card {
            height: 100px;
            border-radius: 0.5rem;
        }
        
        .skeleton-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        /* Enhanced Micro-interactions */
        .btn-interactive {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-interactive::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-interactive:active::before {
            width: 300px;
            height: 300px;
        }
        
        /* Smooth focus transitions */
        .focus-ring {
            transition: box-shadow 0.2s ease-in-out;
        }
        
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 77, 58, 0.2);
        }
        
        /* Enhanced table row interactions */
        .table-row-interactive {
            transition: all 0.2s ease-in-out;
        }
        
        .table-row-interactive:hover {
            background-color: rgba(30, 77, 58, 0.02);
            transform: translateX(2px);
        }
        
        .table-row-interactive:active {
            background-color: rgba(30, 77, 58, 0.05);
        }
        
        /* Loading spinner enhancements */
        .spinner-enhanced {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(30, 77, 58, 0.1);
            border-top: 2px solid #1E4D3A;
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }
        
        /* Pulse animation for notifications */
        @keyframes pulse-dot {
            0%, 60%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            30% {
                opacity: 0.3;
                transform: scale(1.3);
            }
        }
        
        .pulse-dot {
            animation: pulse-dot 2s infinite;
        }
        
        /* Smooth number transitions */
        .number-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Enhanced checkbox and radio buttons */
        .custom-checkbox {
            position: relative;
            cursor: pointer;
        }
        
        .custom-checkbox input[type="checkbox"]:checked + .checkmark {
            background-color: #1E4D3A;
            border-color: #1E4D3A;
        }
        
        .custom-checkbox input[type="checkbox"]:checked + .checkmark::after {
            display: block;
        }
        
        .custom-checkbox .checkmark::after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        /* Smooth modal transitions */
        .modal-backdrop {
            animation: fadeIn 0.2s ease-out;
        }
        
        .modal-content {
            animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <?= $this->yield('styles') ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <div x-data="{ 
        sidebarOpen: window.innerWidth >= 768, 
        userMenuOpen: false, 
        locationMenuOpen: false,
        isMobile: window.innerWidth < 768
    }" 
    x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 768; if (!isMobile && !sidebarOpen) sidebarOpen = true; })"
    class="flex h-screen overflow-hidden">
        
        <!-- Mobile Overlay -->
        <div 
            x-show="isMobile && sidebarOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-20 md:hidden"
            aria-hidden="true"
        ></div>
        
        <!-- Sidebar -->
        <aside 
            :class="[
                sidebarOpen ? 'w-64' : 'w-20',
                isMobile && sidebarOpen ? 'fixed inset-y-0 left-0 z-30' : '',
                isMobile && !sidebarOpen ? 'hidden' : 'relative'
            ]" 
            class="bg-shalom-primary text-white transition-all duration-300 flex flex-col md:relative"
            x-transition:enter="transition-transform ease-in-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-shalom-dark">
                <span x-show="sidebarOpen" class="text-xl font-bold">Shalom Dental</span>
                <span x-show="!sidebarOpen" class="text-xl font-bold">SD</span>
            </div>
            
            <!-- Navegación -->
            <nav class="flex-1 overflow-y-auto py-4">
                <!-- Quick Actions -->
                <div x-show="sidebarOpen" class="px-4 mb-4">
                    <div class="bg-shalom-dark/20 rounded-lg p-3 border border-shalom-dark/30">
                        <h3 class="text-xs font-semibold text-shalom-secondary mb-2">ACCESOS RÁPIDOS</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <?php if (can('agenda.appointments.create')): ?>
                            <button onclick="window.location.href='/agenda/create'" class="bg-white/10 hover:bg-white/20 rounded p-2 text-xs text-center transition-all duration-200 hover:scale-105">
                                <svg class="w-4 h-4 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Nueva Cita
                            </button>
                            <?php endif; ?>
                            <?php if (can('patients.patients.create')): ?>
                            <button onclick="window.location.href='/patients/create'" class="bg-white/10 hover:bg-white/20 rounded p-2 text-xs text-center transition-all duration-200 hover:scale-105">
                                <svg class="w-4 h-4 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Nuevo Paciente
                            </button>
                            <?php endif; ?>
                            <?php if (can('billing.invoices.create')): ?>
                            <button onclick="window.location.href='/billing/invoices/create'" class="bg-white/10 hover:bg-white/20 rounded p-2 text-xs text-center transition-all duration-200 hover:scale-105">
                                <svg class="w-4 h-4 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Nueva Factura
                            </button>
                            <?php endif; ?>
                            <?php if (can('clinical.records.view')): ?>
                            <button onclick="openQuickSearch()" class="bg-white/10 hover:bg-white/20 rounded p-2 text-xs text-center transition-all duration-200 hover:scale-105">
                                <svg class="w-4 h-4 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Búsqueda
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Main Navigation -->
                <ul class="space-y-1 px-2">
                    <!-- Dashboard -->
                    <?php if (can('reports.dashboard.view_all') || can('reports.dashboard.view_own')): ?>
                    <li>
                        <a href="/dashboard" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= $this->active('/dashboard') ?>">
                            <div class="relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <div class="absolute -top-1 -right-1 w-2 h-2 bg-green-400 rounded-full opacity-75"></div>
                            </div>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Dashboard</span>
                            <span x-show="sidebarOpen" class="ml-auto bg-shalom-secondary/30 text-shalom-secondary px-2 py-0.5 rounded text-xs">Ctrl+D</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Agenda -->
                    <?php if (can('agenda.appointments.view_all') || can('agenda.appointments.view_own')): ?>
                    <li>
                        <a href="/agenda" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/agenda') ?>">
                            <div class="relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <?php if (isset($todaysAppointments) && $todaysAppointments > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $todaysAppointments ?></span>
                                <?php endif; ?>
                            </div>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Agenda</span>
                            <span x-show="sidebarOpen" class="ml-auto bg-shalom-secondary/30 text-shalom-secondary px-2 py-0.5 rounded text-xs">Ctrl+A</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Pacientes -->
                    <?php if (can('patients.patients.view')): ?>
                    <li>
                        <a href="/patients" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/patients') ?>">
                            <div class="relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Pacientes</span>
                            <span x-show="sidebarOpen" class="ml-auto bg-shalom-secondary/30 text-shalom-secondary px-2 py-0.5 rounded text-xs">Ctrl+P</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Atención Clínica -->
                    <?php if (can('clinical.records.view') || can('clinical.odontogram.view') || can('clinical.notes.view')): ?>
                    <li>
                        <a href="/clinical" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/clinical') ?>">
                            <div class="relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <?php if (isset($pendingClinicalNotes) && $pendingClinicalNotes > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $pendingClinicalNotes ?></span>
                                <?php endif; ?>
                            </div>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Atención Clínica</span>
                            <span x-show="sidebarOpen" class="ml-auto bg-shalom-secondary/30 text-shalom-secondary px-2 py-0.5 rounded text-xs">Ctrl+C</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Facturación -->
                    <?php if (can('billing.invoices.view_all') || can('billing.invoices.view_own')): ?>
                    <li>
                        <a href="/billing" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/billing') ?>">
                            <div class="relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <?php if (isset($pendingInvoices) && $pendingInvoices > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-yellow-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $pendingInvoices ?></span>
                                <?php endif; ?>
                            </div>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Facturación</span>
                            <span x-show="sidebarOpen" class="ml-auto bg-shalom-secondary/30 text-shalom-secondary px-2 py-0.5 rounded text-xs">Ctrl+F</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Divider -->
                    <li class="border-t border-shalom-dark/20 my-2"></li>
                    
                    <!-- Reportes -->
                    <?php if (can('reports.dashboard.view_all') || can('reports.dashboard.view_own')): ?>
                    <li>
                        <a href="/reports" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/reports') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Reportes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Configuración -->
                    <?php if (can('config.organization.view')): ?>
                    <li>
                        <a href="/config" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/config') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Configuración</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Notificaciones -->
                    <?php if (can('notifications.logs.view') || can('notifications.templates.view') || can('notifications.config.manage')): ?>
                    <li>
                        <a href="/notifications/logs" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/notifications') ?>">
                            <div class="relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <?php if (isset($unreadNotifications) && $unreadNotifications > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center animate-pulse"><?= $unreadNotifications ?></span>
                                <?php endif; ?>
                            </div>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Notificaciones</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Auditoría -->
                    <?php if (can('audit.logs.view')): ?>
                    <li>
                        <a href="/audit" class="sidebar-link group relative flex items-center px-3 py-2.5 rounded-lg hover:bg-shalom-dark/30 transition-all duration-200 <?= activeStartsWith('/audit') ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v2a2 2 0 002 2h2a2 2 0 002-2v-2m-6 0a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v10a2 2 0 01-2 2m-6 0h6"/>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3 font-medium">Auditoría</span>
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
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-4 md:px-6 relative z-20">
                <!-- Mobile Menu Toggle -->
                <button 
                    @click="sidebarOpen = !sidebarOpen" 
                    class="md:hidden p-2 text-gray-500 hover:text-shalom-primary hover:bg-gray-100 rounded-lg transition-colors"
                    aria-label="Toggle menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <!-- Left: Enhanced Title/Breadcrumb -->
                <div class="flex-1">
                    <!-- Enhanced Breadcrumb Navigation -->
                    <?php if (isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0): ?>
                    <nav class="flex items-center space-x-1 text-sm mb-1" aria-label="Breadcrumb">
                        <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <span class="text-shalom-primary font-medium"><?= e($breadcrumb['title']) ?></span>
                            <?php else: ?>
                                <a href="<?= e($breadcrumb['url']) ?>" class="text-gray-500 hover:text-shalom-primary transition-colors flex items-center">
                                    <?= e($breadcrumb['title']) ?>
                                </a>
                                <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                    
                    <!-- Page Title with Context -->
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-semibold text-gray-800"><?= e($title ?? 'Dashboard') ?></h1>
                        
                        <!-- Page Context Badges -->
                        <?php if (isset($pageContext) && is_array($pageContext)): ?>
                            <?php if (!empty($pageContext['status'])): ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= e($pageContext['status']['class'] ?? 'bg-gray-100 text-gray-800') ?>">
                                <?= e($pageContext['status']['text']) ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($pageContext['count'])): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-shalom-light text-shalom-primary">
                                <?= e($pageContext['count']) ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($pageContext['action'])): ?>
                            <button onclick="<?= e($pageContext['action']['onclick']) ?>" class="px-3 py-1 text-xs rounded-lg bg-shalom-primary text-white hover:bg-shalom-dark transition-colors">
                                <?= e($pageContext['action']['text']) ?>
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions Header (if applicable) -->
                    <?php if (isset($headerActions) && is_array($headerActions) && count($headerActions) > 0): ?>
                    <div class="flex items-center gap-2 mt-2">
                        <?php foreach ($headerActions as $action): ?>
                            <?php if ($action['type'] === 'button'): ?>
                            <button 
                                onclick="<?= e($action['onclick'] ?? '') ?>" 
                                class="px-3 py-1.5 text-sm rounded-lg <?= e($action['class'] ?? 'bg-gray-100 text-gray-700 hover:bg-gray-200') ?> transition-colors flex items-center gap-2"
                            >
                                <?php if (!empty($action['icon'])): ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $action['icon'] ?>
                                </svg>
                                <?php endif; ?>
                                <?= e($action['text']) ?>
                            </button>
                            <?php elseif ($action['type'] === 'link'): ?>
                            <a 
                                href="<?= e($action['url'] ?? '#') ?>" 
                                class="px-3 py-1.5 text-sm rounded-lg <?= e($action['class'] ?? 'bg-gray-100 text-gray-700 hover:bg-gray-200') ?> transition-colors flex items-center gap-2"
                            >
                                <?php if (!empty($action['icon'])): ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $action['icon'] ?>
                                </svg>
                                <?php endif; ?>
                                <?= e($action['text']) ?>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
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
                const action = form.getAttribute('action') || '';
                if (action.includes('/patients')) {
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
            
            // Initialize keyboard shortcuts
            initKeyboardShortcuts();
            
            // Initialize enhanced search
            initEnhancedSearch();
        });
        
        // Keyboard Shortcuts
        function initKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Only trigger shortcuts when not typing in inputs
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                    return;
                }
                
                // Ctrl/Cmd + key combinations
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key.toLowerCase()) {
                        case 'd':
                            e.preventDefault();
                            window.location.href = '/dashboard';
                            break;
                        case 'a':
                            e.preventDefault();
                            window.location.href = '/agenda';
                            break;
                        case 'p':
                            e.preventDefault();
                            window.location.href = '/patients';
                            break;
                        case 'c':
                            e.preventDefault();
                            window.location.href = '/clinical';
                            break;
                        case 'f':
                            e.preventDefault();
                            window.location.href = '/billing';
                            break;
                        case '/':
                            e.preventDefault();
                            openQuickSearch();
                            break;
                        case 'k':
                            e.preventDefault();
                            openQuickSearch();
                            break;
                    }
                }
                
                // Escape to close modals
                if (e.key === 'Escape') {
                    closeQuickSearch();
                }
            });
        }
        
        // Enhanced Quick Search
        function initEnhancedSearch() {
            // Create search modal if not exists
            if (!document.getElementById('quick-search-modal')) {
                const searchModal = document.createElement('div');
                searchModal.id = 'quick-search-modal';
                searchModal.className = 'fixed inset-0 bg-black/50 z-50 hidden flex items-start justify-center pt-20';
                searchModal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4" onclick="event.stopPropagation()">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Búsqueda Rápida</h3>
                                <button onclick="closeQuickSearch()" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="relative mb-4">
                                <input 
                                    type="text" 
                                    id="quick-search-input"
                                    placeholder="Buscar pacientes, citas, facturas..." 
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-shalom-primary focus:border-shalom-primary"
                                    autocomplete="off"
                                >
                                <div class="absolute right-3 top-3.5">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div id="quick-search-results" class="max-h-96 overflow-y-auto">
                                <!-- Results will be populated here -->
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <div>
                                        <span class="font-semibold">Tips:</span> Use Ctrl+K o Cmd+K para abrir búsqueda rápidamente
                                    </div>
                                    <div>
                                        Presione <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">ESC</kbd> para cerrar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(searchModal);
            }
            
            // Setup search functionality
            const searchInput = document.getElementById('quick-search-input');
            const searchResults = document.getElementById('quick-search-results');
            
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.innerHTML = '<div class="text-center text-gray-500 py-8">Ingrese al menos 2 caracteres para buscar</div>';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    performQuickSearch(query);
                }, 300);
            });
        }
        
        function openQuickSearch() {
            const modal = document.getElementById('quick-search-modal');
            const input = document.getElementById('quick-search-input');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                input.focus();
                input.select();
            }, 100);
        }
        
        function closeQuickSearch() {
            const modal = document.getElementById('quick-search-modal');
            const input = document.getElementById('quick-search-input');
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            input.value = '';
            document.getElementById('quick-search-results').innerHTML = '';
        }
        
        async function performQuickSearch(query) {
            const resultsDiv = document.getElementById('quick-search-results');
            
            // Show loading state
            resultsDiv.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="spinner mr-3"></div>
                    <span class="text-gray-500">Buscando...</span>
                </div>
            `;
            
            try {
                // Perform multi-module search
                const [patients, appointments, invoices] = await Promise.all([
                    searchPatients(query),
                    searchAppointments(query),
                    searchInvoices(query)
                ]);
                
                displaySearchResults({ patients, appointments, invoices }, query);
            } catch (error) {
                console.error('Search error:', error);
                resultsDiv.innerHTML = `
                    <div class="text-center text-red-500 py-8">
                        Error al realizar la búsqueda. Intente nuevamente.
                    </div>
                `;
            }
        }
        
        async function searchPatients(query) {
            const response = await api(`/api/patients/search?q=${encodeURIComponent(query)}`);
            return response.data || [];
        }
        
        async function searchAppointments(query) {
            const response = await api(`/api/agenda/search?q=${encodeURIComponent(query)}`);
            return response.data || [];
        }
        
        async function searchInvoices(query) {
            const response = await api(`/api/billing/search?q=${encodeURIComponent(query)}`);
            return response.data || [];
        }
        
        function displaySearchResults(results, query) {
            const resultsDiv = document.getElementById('quick-search-results');
            
            if (!results.patients.length && !results.appointments.length && !results.invoices.length) {
                resultsDiv.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        No se encontraron resultados para "<strong>${query}</strong>"
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            // Patients results
            if (results.patients.length > 0) {
                html += `
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Pacientes (${results.patients.length})
                        </h4>
                        <div class="space-y-2">
                `;
                
                results.patients.slice(0, 5).forEach(patient => {
                    html += `
                        <a href="/patients/${patient.id}" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors" onclick="closeQuickSearch()">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">${patient.first_name} ${patient.last_name}</div>
                                    <div class="text-sm text-gray-500">${patient.email || 'No email'} • ${patient.phone || 'No teléfono'}</div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    `;
                });
                
                html += '</div></div>';
            }
            
            // Appointments results
            if (results.appointments.length > 0) {
                html += `
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Citas (${results.appointments.length})
                        </h4>
                        <div class="space-y-2">
                `;
                
                results.appointments.slice(0, 5).forEach(appointment => {
                    html += `
                        <a href="/agenda/${appointment.id}" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors" onclick="closeQuickSearch()">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">${appointment.patient_name}</div>
                                    <div class="text-sm text-gray-500">${appointment.scheduled_start_time} • ${appointment.appointment_type_name}</div>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(appointment.status)}">${appointment.status}</span>
                            </div>
                        </a>
                    `;
                });
                
                html += '</div></div>';
            }
            
            // Invoices results
            if (results.invoices.length > 0) {
                html += `
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Facturas (${results.invoices.length})
                        </h4>
                        <div class="space-y-2">
                `;
                
                results.invoices.slice(0, 5).forEach(invoice => {
                    html += `
                        <a href="/billing/invoices/${invoice.id}" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors" onclick="closeQuickSearch()">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">#${invoice.invoice_number}</div>
                                    <div class="text-sm text-gray-500">${invoice.patient_name} • $${invoice.total}</div>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full ${getInvoiceStatusColor(invoice.status)}">${invoice.status}</span>
                            </div>
                        </a>
                    `;
                });
                
                html += '</div></div>';
            }
            
            resultsDiv.innerHTML = html;
        }
        
        function getStatusColor(status) {
            const colors = {
                'scheduled': 'bg-blue-100 text-blue-800',
                'confirmed': 'bg-green-100 text-green-800',
                'in_progress': 'bg-yellow-100 text-yellow-800',
                'completed': 'bg-gray-100 text-gray-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
        
        function getInvoiceStatusColor(status) {
            const colors = {
                'draft': 'bg-gray-100 text-gray-800',
                'sent': 'bg-blue-100 text-blue-800',
                'paid': 'bg-green-100 text-green-800',
                'overdue': 'bg-red-100 text-red-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
        
        // Close search on outside click
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('quick-search-modal');
            if (modal && !modal.classList.contains('hidden') && !modal.contains(e.target)) {
                closeQuickSearch();
            }
        });
        
        // Enhanced Loading States and Micro-interactions
        class LoadingManager {
            constructor() {
                this.loadingElements = new Map();
                this.init();
            }
            
            init() {
                // Add loading states to buttons
                document.addEventListener('click', this.handleButtonClick.bind(this));
                
                // Add smooth number transitions
                this.initNumberTransitions();
                
                // Add ripple effects
                this.initRippleEffects();
                
                // Add table row interactions
                this.initTableInteractions();
            }
            
            handleButtonClick(e) {
                const button = e.target.closest('button, .btn');
                if (!button) return;
                
                // Skip if button has no action or is disabled
                if (button.disabled || button.dataset.noLoading === 'true') return;

                const buttonType = (button.getAttribute('type') || 'submit').toLowerCase();
                if (buttonType !== 'submit') return;

                const form = button.closest('form');
                if (!form) return;

                if (!form.checkValidity()) {
                    return;
                }

                if (button.form !== form) {
                    return;
                }

                if (e.defaultPrevented) {
                    return;
                }

                this.setButtonLoading(button, true);
                setTimeout(() => this.setButtonLoading(button, false), 3000);
            }
            
            setButtonLoading(button, loading) {
                if (loading) {
                    const originalContent = button.innerHTML;
                    this.loadingElements.set(button, originalContent);
                    
                    button.disabled = true;
                    button.innerHTML = `
                        <span class="flex items-center justify-center">
                            <span class="spinner-enhanced mr-2"></span>
                            <span>Procesando...</span>
                        </span>
                    `;
                    button.classList.add('opacity-75', 'cursor-not-allowed');
                } else {
                    const originalContent = this.loadingElements.get(button);
                    if (originalContent) {
                        button.innerHTML = originalContent;
                        button.disabled = false;
                        button.classList.remove('opacity-75', 'cursor-not-allowed');
                        this.loadingElements.delete(button);
                    }
                }
            }
            
            initNumberTransitions() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateNumber(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                document.querySelectorAll('[data-animate-number]').forEach(el => {
                    observer.observe(el);
                });
            }
            
            animateNumber(element) {
                const target = parseFloat(element.dataset.animateNumber);
                const duration = parseInt(element.dataset.duration) || 1000;
                const start = 0;
                const increment = target / (duration / 16);
                let current = start;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (element.dataset.format === 'currency') {
                        element.textContent = '$' + current.toLocaleString('en-US', {maximumFractionDigits: 0});
                    } else {
                        element.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            }
            
            initRippleEffects() {
                document.addEventListener('click', (e) => {
                    const button = e.target.closest('.btn-interactive');
                    if (!button) return;
                    
                    const ripple = document.createElement('span');
                    const rect = button.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    button.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            }
            
            initTableInteractions() {
                document.querySelectorAll('tbody tr').forEach(row => {
                    row.classList.add('table-row-interactive', 'cursor-pointer');
                    
                    row.addEventListener('click', (e) => {
                        // Skip if clicking on a link or button
                        if (e.target.closest('a, button, input, select')) return;
                        
                        const link = row.querySelector('a[href]');
                        if (link) {
                            link.click();
                        }
                    });
                });
            }
        }
        
        // Initialize loading manager
        const loadingManager = new LoadingManager();
        
        // Enhanced form validation with real-time feedback
        class FormEnhancer {
            constructor() {
                this.init();
            }
            
            init() {
                document.querySelectorAll('form').forEach(form => {
                    this.enhanceForm(form);
                });
            }
            
            enhanceForm(form) {
                // Skip forms that have Alpine.js submit handlers
                if (form.hasAttribute('@submit') || form.hasAttribute('@submit.prevent') ||
                    form.getAttribute('x-on:submit') || form.getAttribute('x-on:submit.prevent')) {
                    return;
                }

                const inputs = form.querySelectorAll('input, textarea, select');

                inputs.forEach(input => {
                    // Real-time validation feedback
                    input.addEventListener('blur', () => this.validateField(input));
                    input.addEventListener('input', () => {
                        if (input.classList.contains('border-red-500')) {
                            this.validateField(input);
                        }
                    });

                    // Add focus ring effect
                    input.classList.add('focus-ring');
                });
                
                // Enhanced form submission
                form.addEventListener('submit', (e) => {
                    if (!this.validateForm(form)) {
                        e.preventDefault();
                        this.showFirstError(form);
                        return false;
                    }
                });
            }
            
            validateField(field) {
                const isValid = this.checkFieldValidity(field);
                
                if (isValid) {
                    field.classList.remove('border-red-500', 'ring-red-500');
                    field.classList.add('border-green-500', 'ring-green-500');
                    
                    // Remove error message if exists
                    const errorMsg = field.parentNode.querySelector('.field-error');
                    if (errorMsg) errorMsg.remove();
                } else {
                    field.classList.add('border-red-500', 'ring-red-500');
                    field.classList.remove('border-green-500', 'ring-green-500');
                }
                
                return isValid;
            }
            
            checkFieldValidity(field) {
                // Required field validation
                if (field.required && !field.value.trim()) {
                    return false;
                }
                
                // Email validation
                if (field.type === 'email' && field.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(field.value);
                }
                
                // Phone validation
                if (field.type === 'tel' && field.value) {
                    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
                    return phoneRegex.test(field.value) && field.value.replace(/\D/g, '').length >= 7;
                }
                
                // Pattern validation
                if (field.pattern && field.value) {
                    const regex = new RegExp(field.pattern);
                    return regex.test(field.value);
                }
                
                return true;
            }
            
            validateForm(form) {
                const inputs = form.querySelectorAll('input, textarea, select');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!this.validateField(input)) {
                        isValid = false;
                    }
                });
                
                return isValid;
            }
            
            showFirstError(form) {
                const firstError = form.querySelector('.border-red-500');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Shake animation
                    firstError.classList.add('animate-pulse');
                    setTimeout(() => {
                        firstError.classList.remove('animate-pulse');
                    }, 500);
                }
            }
        }
        
        // Initialize form enhancer
        const formEnhancer = new FormEnhancer();
        
        // Global notification system
        window.showNotification = function(message, type = 'success', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            notification.classList.add(...colors[type].split(' '));
            notification.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        ${type === 'success' ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' :
                          type === 'error' ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>' :
                          '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>'}
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
                notification.classList.add('translate-x-0');
            }, 100);
            
            // Remove after duration
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                notification.classList.remove('translate-x-0');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, duration);
        };
        
        // Utility functions for common operations
        window.utils = {
            debounce: function(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },
            
            throttle: function(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            },
            
            formatCurrency: function(amount) {
                return new Intl.NumberFormat('es-ES', {
                    style: 'currency',
                    currency: 'USD'
                }).format(amount);
            },
            
            formatDate: function(date, options = {}) {
                const defaults = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                };
                return new Intl.DateTimeFormat('es-ES', { ...defaults, ...options }).format(new Date(date));
            }
        };
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
