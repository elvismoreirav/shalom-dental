<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Rutas del Módulo Auth
 * ============================================================================
 * Archivo: src/Modules/Auth/routes.php
 */

use App\Core\Router;
use App\Modules\Auth\Controllers\AuthController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;

/** @var Router $router */

// =========================================================================
// RUTAS PÚBLICAS (sin autenticación)
// =========================================================================

// Formulario de login
$router->get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

// Procesar login (con protección CSRF)
$router->post('/login', [AuthController::class, 'login'])
    ->name('login.submit')
    ->middleware(CsrfMiddleware::class);

// =========================================================================
// API PÚBLICA
// =========================================================================

$router->group(['prefix' => '/api/auth'], function (Router $router) {
    
    // Login API
    $router->post('/login', [AuthController::class, 'apiLogin'])
        ->name('api.auth.login');
    
    // Obtener token CSRF
    $router->get('/csrf-token', [AuthController::class, 'csrfToken'])
        ->name('api.auth.csrf');
    
    // Verificar sesión
    $router->get('/check', [AuthController::class, 'check'])
        ->name('api.auth.check');
});

// =========================================================================
// RUTAS PROTEGIDAS (requieren autenticación)
// =========================================================================

$router->group(['middleware' => [AuthMiddleware::class]], function (Router $router) {
    
    // Logout
    $router->post('/logout', [AuthController::class, 'logout'])
        ->name('logout')
        ->middleware(CsrfMiddleware::class);
    
    // API protegida
    $router->group(['prefix' => '/api/auth'], function (Router $router) {
        
        // Obtener usuario actual
        $router->get('/me', [AuthController::class, 'me'])
            ->name('api.auth.me');
        
        // Cambiar sede
        $router->post('/switch-location', [AuthController::class, 'switchLocation'])
            ->name('api.auth.switch-location');
    });
});