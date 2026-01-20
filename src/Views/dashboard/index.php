<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<!-- Enhanced Header with Personalization -->
<div class="mb-8" x-data="dashboardData()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-bold text-gray-900">
                    Bienvenido de vuelta, <span class="text-shalom-primary"><?= e($user['first_name'] ?? 'Doctor') ?></span> 👋
                </h1>
                <button @click="refreshDashboard()" class="p-2 text-gray-500 hover:text-shalom-primary hover:bg-shalom-light rounded-lg transition-all" title="Actualizar dashboard">
                    <svg class="w-5 h-5" :class="{'animate-spin': refreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
            <p class="mt-2 text-gray-600">
                <span x-show="!refreshing">Aquí está tu panorama general para hoy</span>
                <span x-show="refreshing" class="text-shalom-primary">Actualizando datos...</span>
                <span class="ml-2 text-shalom-primary">• <?= date('l, j F Y') ?></span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Time Display -->
            <div class="text-right">
                <div class="text-2xl font-bold text-gray-900" x-text="currentTime"></div>
                <div class="text-sm text-gray-500" x-text="currentDate"></div>
            </div>
            <!-- Location Status -->
            <?php if (isset($currentLocation)): ?>
            <div class="bg-shalom-light px-4 py-2 rounded-lg border border-shalom-secondary/30">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-shalom-primary"><?= e($currentLocation['name']) ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Acciones Rápidas:</span>
                <div class="flex items-center gap-2">
                    <?php if (can('agenda.appointments.create')): ?>
                    <button @click="openQuickActionModal('appointment')" class="px-3 py-1.5 bg-shalom-primary text-white text-sm rounded-lg hover:bg-shalom-dark transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nueva Cita
                    </button>
                    <?php endif; ?>
                    <?php if (can('patients.patients.create')): ?>
                    <button @click="openQuickActionModal('patient')" class="px-3 py-1.5 bg-shalom-secondary text-white text-sm rounded-lg hover:bg-shalom-secondary/80 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Nuevo Paciente
                    </button>
                    <?php endif; ?>
                    <button onclick="openQuickSearch()" class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar (Ctrl+K)
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>Próxima cita:</span>
                <span class="font-medium text-shalom-primary" x-text="nextAppointment"></span>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Stats Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8" x-data="statsWidget()">
    
    <!-- Today's Appointments Card -->
    <div class="hover-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?= $stats['appointments_today'] ?></div>
                    <div class="text-xs text-gray-500">hoy</div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Citas Hoy</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">↑ 12%</span> vs ayer
                    </p>
                </div>
                <a href="/agenda" class="text-shalom-primary hover:text-shalom-dark text-sm font-medium">
                    Ver →
                </a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-blue-100 to-blue-200"></div>
    </div>

    <!-- Waiting Room Card -->
    <div class="hover-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-yellow-100 rounded-lg p-3 group-hover:bg-yellow-200 transition-colors">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?= $stats['waiting_room'] ?></div>
                    <div class="text-xs text-gray-500">pacientes</div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Sala de Espera</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Tiempo prom: <span class="text-yellow-600">15 min</span>
                    </p>
                </div>
                <a href="/agenda/waiting-room" class="text-shalom-primary hover:text-shalom-dark text-sm font-medium">
                    Atender →
                </a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-yellow-100 to-yellow-200"></div>
    </div>

    <!-- Active Patients Card -->
    <div class="hover-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-100 rounded-lg p-3 group-hover:bg-purple-200 transition-colors">
                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-6 0 3 3 0 016 0zm-1 5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900"><?= $stats['total_patients'] ?></div>
                    <div class="text-xs text-gray-500">activos</div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Pacientes</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">↑ 8%</span> este mes
                    </p>
                </div>
                <a href="/patients" class="text-shalom-primary hover:text-shalom-dark text-sm font-medium">
                    Gestionar →
                </a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-purple-100 to-purple-200"></div>
    </div>

    <!-- Monthly Billing Card -->
    <div class="hover-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900">$<?= number_format($stats['billing_month'], 0) ?></div>
                    <div class="text-xs text-gray-500">mes actual</div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Facturación</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">↑ 15%</span> vs mes pasado
                    </p>
                </div>
                <a href="/billing" class="text-shalom-primary hover:text-shalom-dark text-sm font-medium">
                    Detalles →
                </a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-green-100 to-green-200"></div>
    </div>
</div>

<!-- Enhanced Charts Section -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
    
    <!-- Weekly Appointments Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-gray-900">Citas - Últimos 7 días</h3>
                <button onclick="exportChart('weekly')" class="text-shalom-primary hover:text-shalom-dark text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
            </div>
            <div x-data="weeklyChart()">
                <div class="flex items-end gap-3 h-40 mb-2">
                    <?php
                        $maxWeek = 0;
                        foreach ($weeklyAppointments ?? [] as $row) {
                            $maxWeek = max($maxWeek, (int) $row['total']);
                        }
                        $maxWeek = $maxWeek > 0 ? $maxWeek : 1;
                    ?>
                    <?php foreach (($weeklyAppointments ?? []) as $row): ?>
                        <?php 
                            $height = (int) round(($row['total'] / $maxWeek) * 100);
                            $dayName = date('D', strtotime($row['day']));
                            $isToday = date('Y-m-d') === $row['day'];
                        ?>
                        <div class="flex-1 flex flex-col items-center group cursor-pointer">
                            <div class="relative w-full">
                                <div 
                                    class="bg-shalom-secondary hover:bg-shalom-primary rounded-t-lg transition-all duration-300 hover:scale-105"
                                    style="height: <?= $height ?>%;"
                                    :class="{'ring-2 ring-shalom-primary': hoverIndex === <?= $dayNum ?? 0 ?>}"
                                    @mouseenter="hoverIndex = <?= $dayNum ?? 0 ?>"
                                    @mouseleave="hoverIndex = null"
                                >
                                    <?php if ($row['total'] > 0): ?>
                                    <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        <?= $row['total'] ?> citas
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isToday): ?>
                                <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php $dayNum++; ?>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                    <?php foreach (($weeklyAppointments ?? []) as $row): ?>
                        <div class="text-center flex-1">
                            <div><?= e(substr($row['day'], 5)) ?></div>
                            <div class="text-gray-400"><?= date('D', strtotime($row['day'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Billing Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-gray-900">Facturación - Últimos 6 meses</h3>
                <button onclick="exportChart('monthly')" class="text-shalom-primary hover:text-shalom-dark text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
            </div>
            <div x-data="monthlyChart()">
                <div class="flex items-end gap-3 h-40 mb-2">
                    <?php
                        $maxMonth = 0.0;
                        foreach ($monthlyBilling ?? [] as $row) {
                            $maxMonth = max($maxMonth, (float) $row['total']);
                        }
                        $maxMonth = $maxMonth > 0 ? $maxMonth : 1;
                    ?>
                    <?php foreach (($monthlyBilling ?? []) as $row): ?>
                        <?php 
                            $height = (int) round(($row['total'] / $maxMonth) * 100);
                            $monthName = date('M', strtotime($row['month'] . '-01'));
                        ?>
                        <div class="flex-1 flex flex-col items-center group cursor-pointer">
                            <div class="relative w-full">
                                <div 
                                    class="bg-gradient-to-t from-shalom-accent/80 to-shalom-accent/60 hover:from-shalom-accent hover:to-shalom-accent/80 rounded-t-lg transition-all duration-300 hover:scale-105"
                                    style="height: <?= $height ?>%;"
                                    @mouseenter="showTooltip($event, '<?= number_format($row['total'], 2) ?>')"
                                    @mouseleave="hideTooltip()"
                                >
                                    <div x-show="show" x-text="tooltip" class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                    <?php foreach (($monthlyBilling ?? []) as $row): ?>
                        <div class="text-center flex-1">
                            <div><?= date('M', strtotime($row['month'] . '-01')) ?></div>
                            <div class="text-gray-400"><?= '$' . number_format($row['total'], 0) . 'k' ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Actividad Reciente</h3>
            <a href="/audit" class="text-shalom-primary hover:text-shalom-dark text-sm">Ver todo →</a>
        </div>
        <div class="space-y-4">
            <?php if (!empty($recentActivity)): ?>
                <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                <div class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-shalom-light flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">
                            <?= e($activity['description']) ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?= e($activity['user_name']) ?> •                             hace <?= e($activity['time_ago'] ?? 'poco tiempo') ?>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        <?= date('H:i', strtotime($activity['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    No hay actividad reciente para mostrar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
// Dashboard JavaScript
function dashboardData() {
    return {
        refreshing: false,
        currentTime: '',
        currentDate: '',
        nextAppointment: 'Próxima: 14:30 - Juan Pérez',
        
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            
            // Load next appointment
            this.loadNextAppointment();
        },
        
        async refreshDashboard() {
            this.refreshing = true;
            try {
                await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API call
                window.location.reload();
            } catch (error) {
                console.error('Error refreshing dashboard:', error);
            } finally {
                this.refreshing = false;
            }
        },
        
        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            this.currentDate = now.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        },
        
        async loadNextAppointment() {
            try {
                // This would be an API call to get next appointment
                const response = await api('/api/agenda/next');
                if (response.data && response.data.length > 0) {
                    const appointment = response.data[0];
                    this.nextAppointment = `${appointment.time} - ${appointment.patient_name}`;
                }
            } catch (error) {
                // Keep default value
            }
        },
        
        openQuickActionModal(type) {
            if (type === 'appointment') {
                window.location.href = '/agenda/create';
            } else if (type === 'patient') {
                window.location.href = '/patients/create';
            }
        }
    }
}

function statsWidget() {
    return {
        // Stats widget specific functionality
    }
}

function weeklyChart() {
    return {
        hoverIndex: null
    }
}

function monthlyChart() {
    return {
        show: false,
        tooltip: '',
        
        showTooltip(event, value) {
            this.tooltip = '$' + value;
            this.show = true;
        },
        
        hideTooltip() {
            this.show = false;
        }
    }
}

// Utility functions
function exportChart(type) {
    // Implementation for chart export
    console.log('Exporting chart:', type);
}

function getActivityIcon(action) {
    const icons = {
        'create': '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>',
        'update': '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
        'delete': '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
        'login': '<svg class="w-5 h-5 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>'
    };
    return icons[action] || icons['create'];
}

function time_ago(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    const intervals = {
        año: 31536000,
        mes: 2592000,
        semana: 604800,
        día: 86400,
        hora: 3600,
        minuto: 60
    };
    
    for (const [name, secondsInInterval] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInInterval);
        if (interval > 1) return `hace ${interval} ${name}s`;
        if (interval === 1) return `hace ${interval} ${name}`;
    }
    return 'hace unos segundos';
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script>
<?php $this->endSection(); ?>
