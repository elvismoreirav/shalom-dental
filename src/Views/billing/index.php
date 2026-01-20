<?php 
$this->extend('layouts.app');

// Set breadcrumbs for enhanced navigation
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Facturación', 'url' => '/billing']
];

// Header actions
$headerActions = [
    [
        'type' => 'link',
        'url' => '/billing/invoices/create',
        'text' => 'Nueva Factura',
        'class' => 'bg-shalom-primary text-white hover:bg-shalom-dark',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
    ]
];
?>

<?php $this->section('content'); ?>

<!-- Enhanced Billing Dashboard -->
<div class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Billing Stats -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900">$<?= number_format((float) ($stats['billing_month'] ?? 0), 0) ?></div>
                    <div class="text-sm text-green-600">Mes actual</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                Facturación del mes actual
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl border border-blue-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?= number_format((int) ($stats['invoices_week'] ?? 0)) ?></div>
                    <div class="text-sm text-blue-600">Nuevas facturas</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                Emitidas esta semana
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-xl border border-yellow-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-yellow-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?= number_format((int) ($stats['invoices_pending'] ?? 0)) ?></div>
                    <div class="text-sm text-yellow-600">Pendientes</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                Facturas por pagar
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="/billing/invoices" class="group hover-card bg-white rounded-lg border border-gray-200 p-6 hover:border-shalom-primary transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="bg-shalom-light rounded-lg p-3 group-hover:bg-shalom-primary/10 transition-colors">
                    <svg class="w-6 h-6 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 group-hover:text-shalom-primary transition-colors">Ver Facturas</h3>
                    <p class="text-sm text-gray-600 mt-1">Gestiona todas las facturas emitidas</p>
                    <div class="flex items-center mt-2 text-shalom-primary text-sm font-medium">
                        Ir a facturas →
                    </div>
                </div>
            </div>
        </a>
        
        <a href="/billing/invoices/create" class="group hover-card bg-white rounded-lg border border-gray-200 p-6 hover:border-shalom-primary transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition-colors">Nueva Factura</h3>
                    <p class="text-sm text-gray-600 mt-1">Crea una nueva factura para un paciente</p>
                    <div class="flex items-center mt-2 text-green-600 text-sm font-medium">
                        Crear ahora →
                    </div>
                </div>
            </div>
        </a>
        
        <a href="/billing/monitor" class="group hover-card bg-white rounded-lg border border-gray-200 p-6 hover:border-shalom-primary transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="bg-orange-100 rounded-lg p-3 group-hover:bg-orange-200 transition-colors">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 group-hover:text-orange-600 transition-colors">Monitor SRI</h3>
                    <p class="text-sm text-gray-600 mt-1">Verifica el estado con el SRI</p>
                    <div class="flex items-center mt-2 text-orange-600 text-sm font-medium">
                        Abrir monitor →
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php $this->endSection(); ?>
