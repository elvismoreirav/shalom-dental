<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Reportes
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/reports', function () {
        return Response::view('reports.index', ['title' => 'Reportes']);
    })->name('reports.index')
      ->middleware(PermissionMiddleware::class)
      ->permission(['reports.dashboard.view_all', 'reports.dashboard.view_own']);

    $router->get('/reports/productivity', function () {
        return Response::view('reports.productivity', ['title' => 'Productividad']);
    })->name('reports.productivity')
      ->middleware(PermissionMiddleware::class)
      ->permission(['reports.productivity.view_all', 'reports.productivity.view_own']);

    $router->get('/reports/financial', function () {
        return Response::view('reports.financial', ['title' => 'Financiero']);
    })->name('reports.financial')
      ->middleware(PermissionMiddleware::class)
      ->permission('reports.financial.view');

    $router->get('/reports/appointments', function () {
        return Response::view('reports.appointments', ['title' => 'Reporte de Citas']);
    })->name('reports.appointments')
      ->middleware(PermissionMiddleware::class)
      ->permission(['reports.dashboard.view_all', 'reports.dashboard.view_own']);

    $router->get('/reports/no-shows', function () {
        return Response::view('reports.no-shows', ['title' => 'No Shows']);
    })->name('reports.no-shows')
      ->middleware(PermissionMiddleware::class)
      ->permission(['reports.dashboard.view_all', 'reports.dashboard.view_own']);
});
