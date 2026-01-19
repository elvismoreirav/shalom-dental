<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Rutas del Dashboard
 * ============================================================================
 * Archivo: src/Modules/Dashboard/routes.php
 * ============================================================================
 */

use App\Core\Router;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;

$router->group([
    'middleware' => [AuthMiddleware::class, LocationMiddleware::class]
], function (Router $router) {
    // Dashboard principal
    $router->get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware(PermissionMiddleware::class)
        ->permission(['reports.dashboard.view_all', 'reports.dashboard.view_own']);

    // Redirigir raiz a dashboard
    $router->get('/', function () {
        return \App\Core\Response::redirect('/dashboard');
    });

    // API Dashboard (pendiente de implementar)
});
