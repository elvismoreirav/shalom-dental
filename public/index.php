<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Front Controller
 * ============================================================================
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Response;

// DEBUG: Mostrar URI recibida (ELIMINAR DESPUÉS)
if (isset($_GET['debug_uri'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'N/A',
        'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'N/A',
    ]);
    exit;
}

// 1. Inicializar la Aplicación
$app = Application::getInstance(dirname(__DIR__));

// 2. Obtener el Router
$router = $app->getRouter();

// 3. Cargar Rutas

// A. Rutas de Autenticación (Login, Logout)
require_once $app->getBasePath() . '/src/Modules/Auth/routes.php';

// B. Rutas del Dashboard
require_once $app->getBasePath() . '/src/Modules/Dashboard/routes.php';

// C. Rutas de módulos principales
require_once $app->getBasePath() . '/src/Modules/Agenda/routes.php';
require_once $app->getBasePath() . '/src/Modules/Patients/routes.php';
require_once $app->getBasePath() . '/src/Modules/Billing/routes.php';
require_once $app->getBasePath() . '/src/Modules/Reports/routes.php';
require_once $app->getBasePath() . '/src/Modules/Config/routes.php';
require_once $app->getBasePath() . '/src/Modules/Notifications/routes.php';
require_once $app->getBasePath() . '/src/Modules/Audit/routes.php';
require_once $app->getBasePath() . '/src/Modules/Files/routes.php';
require_once $app->getBasePath() . '/src/Modules/Clinical/routes.php';

// D. Ruta Raíz
$router->get('/', function() {
    return Response::redirect('/login');
});

// 4. Ejecutar
$app->run();
