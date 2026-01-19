<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Pacientes
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Modules\Patients\Controllers\PatientController;
use App\Modules\Patients\Controllers\PatientFileController;
use App\Modules\Patients\Controllers\PatientExportController;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/patients', [PatientController::class, 'index'])
    ->name('patients.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('patients.patients.view');

    $router->get('/patients/create', [PatientController::class, 'create'])
    ->name('patients.create')
      ->middleware(PermissionMiddleware::class)
      ->permission('patients.patients.create');

    $router->get('/patients/{id}', [PatientController::class, 'show'])
    ->name('patients.show')
      ->middleware(PermissionMiddleware::class)
      ->permission('patients.patients.view');

    $router->get('/patients/{id}/edit', [PatientController::class, 'edit'])
    ->name('patients.edit')
      ->middleware(PermissionMiddleware::class)
      ->permission('patients.patients.edit');

    $router->post('/patients', [PatientController::class, 'store'])
    ->name('patients.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('patients.patients.create');

    $router->put('/patients/{id}', [PatientController::class, 'update'])
    ->name('patients.update')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('patients.patients.edit');

    $router->post('/patients/{id}/files', [PatientFileController::class, 'store'])
    ->name('patients.files.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('patients.files.upload');

    $router->delete('/patients/{id}/files/{fileId}', [PatientFileController::class, 'delete'])
    ->name('patients.files.delete')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission(['patients.files.delete_all', 'patients.files.delete_own']);

    $router->get('/patients/export/csv', [PatientExportController::class, 'export'])
    ->name('patients.export.csv')
    ->middleware(PermissionMiddleware::class)
    ->permission('reports.export.excel');
});
