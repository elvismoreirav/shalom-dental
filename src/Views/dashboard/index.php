<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Bienvenido, <?= e($user['first_name'] ?? 'Doctor') ?> 👋
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Aquí tienes el resumen de tu actividad hoy.
        </p>
    </div>
    <div class="flex">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            📅 <?= date('d/m/Y') ?>
        </span>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <a href="/agenda" class="block bg-white overflow-hidden shadow rounded-lg border border-gray-100 hover:border-gray-200 transition">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Citas para Hoy</dt>
                        <dd class="text-2xl font-bold text-gray-900"><?= $stats['appointments_today'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </a>

    <a href="/agenda/waiting-room" class="block bg-white overflow-hidden shadow rounded-lg border border-gray-100 hover:border-gray-200 transition">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">En Sala de Espera</dt>
                        <dd class="text-2xl font-bold text-gray-900"><?= $stats['waiting_room'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </a>

    <a href="/patients" class="block bg-white overflow-hidden shadow rounded-lg border border-gray-100 hover:border-gray-200 transition">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-6 0 3 3 0 016 0zm-1 5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pacientes Activos</dt>
                        <dd class="text-2xl font-bold text-gray-900"><?= $stats['total_patients'] ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </a>

    <a href="/billing" class="block bg-white overflow-hidden shadow rounded-lg border border-gray-100 hover:border-gray-200 transition">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Facturación Mes</dt>
                        <dd class="text-2xl font-bold text-gray-900">$<?= number_format($stats['billing_month'], 2) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </a>
</div>

<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Citas ultimos 7 dias</h3>
        <?php
            $maxWeek = 0;
            foreach ($weeklyAppointments ?? [] as $row) {
                $maxWeek = max($maxWeek, (int) $row['total']);
            }
            $maxWeek = $maxWeek > 0 ? $maxWeek : 1;
        ?>
        <div class="flex items-end gap-2 h-32">
            <?php foreach (($weeklyAppointments ?? []) as $row): ?>
                <?php $height = (int) round(($row['total'] / $maxWeek) * 100); ?>
                <div class="flex-1 text-center">
                    <div class="bg-shalom-secondary/60 rounded-md mx-auto" style="height: <?= $height ?>%;"></div>
                    <div class="mt-2 text-xs text-gray-500"><?= e(substr($row['day'], 5)) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Facturacion ultimos 6 meses</h3>
        <?php
            $maxMonth = 0.0;
            foreach ($monthlyBilling ?? [] as $row) {
                $maxMonth = max($maxMonth, (float) $row['total']);
            }
            $maxMonth = $maxMonth > 0 ? $maxMonth : 1;
        ?>
        <div class="flex items-end gap-2 h-32">
            <?php foreach (($monthlyBilling ?? []) as $row): ?>
                <?php $height = (int) round(($row['total'] / $maxMonth) * 100); ?>
                <div class="flex-1 text-center">
                    <div class="bg-shalom-accent/70 rounded-md mx-auto" style="height: <?= $height ?>%;"></div>
                    <div class="mt-2 text-xs text-gray-500"><?= e(substr($row['month'], 5)) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
