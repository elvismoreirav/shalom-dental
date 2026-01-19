<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Bootstrap
 * ============================================================================
 * Archivo: src/bootstrap.php
 * Descripción: Inicialización de la aplicación y registro de servicios
 * ============================================================================
 */

use Shalom\Core\Application;
use Shalom\Core\Router;
use Shalom\Modules\Auth\Services\AuthService;

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar helpers globales
require_once __DIR__ . '/Core/Helpers/helpers.php';

// Inicializar aplicación
$app = Application::getInstance(dirname(__DIR__));

// Registrar servicio de autenticación
$app->singleton('auth', function ($app) {
    return new AuthService(
        $app->resolve('db'),
        $app->resolve('session'),
        $app->config('app.auth', [])
    );
});

// Iniciar aplicación
$app->boot();

// Compartir datos globales con las vistas
$view = $app->resolve('view');
$view->share('appName', $app->config('app.name', 'Shalom Dental'));
$view->share('csrfToken', csrf_token());

// Crear router
$router = new Router();

// Registrar middleware global
$router->globalMiddleware([
    // Aquí se pueden agregar middleware que apliquen a todas las rutas
]);

// Cargar rutas de módulos
$moduleRoutes = [
    'Auth' => __DIR__ . '/Modules/Auth/routes.php',
    // Agregar más módulos aquí
];

foreach ($moduleRoutes as $module => $routeFile) {
    if (file_exists($routeFile)) {
        $registerRoutes = require $routeFile;
        $registerRoutes($router);
    }
}

// Registrar router en el contenedor
$app->instance('router', $router);

return $app;
