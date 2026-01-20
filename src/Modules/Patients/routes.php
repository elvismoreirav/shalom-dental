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
use App\Modules\Clinical\Controllers\ClinicalRecordController;
use App\Modules\Clinical\Controllers\OdontogramController;
use App\Modules\Clinical\Controllers\TreatmentPlanController;

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

    $router->get('/patients/{id}/clinical-record', [ClinicalRecordController::class, 'edit'])
    ->name('patients.clinical-record')
      ->middleware(PermissionMiddleware::class)
      ->permission('clinical.records.view');

    $router->post('/patients/{id}/clinical-record', [ClinicalRecordController::class, 'update'])
    ->name('patients.clinical-record.update')
      ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
      ->permission('clinical.records.edit');

    $router->get('/patients/{id}/odontogram', [OdontogramController::class, 'index'])
    ->name('patients.odontogram')
      ->middleware(PermissionMiddleware::class)
      ->permission('clinical.odontogram.view');

    $router->post('/patients/{id}/odontogram/{tooth}', [OdontogramController::class, 'updateTooth'])
    ->name('patients.odontogram.update')
      ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
      ->permission('clinical.odontogram.edit');

    $router->get('/patients/{id}/treatment-plans', [TreatmentPlanController::class, 'index'])
    ->name('patients.treatment-plans')
      ->middleware(PermissionMiddleware::class)
      ->permission('clinical.treatment_plans.view');

    $router->get('/patients/{id}/treatment-plans/create', [TreatmentPlanController::class, 'create'])
    ->name('patients.treatment-plans.create')
      ->middleware(PermissionMiddleware::class)
      ->permission('clinical.treatment_plans.create');

    $router->post('/patients/{id}/treatment-plans', [TreatmentPlanController::class, 'store'])
    ->name('patients.treatment-plans.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('clinical.treatment_plans.create');

    $router->get('/patients/{id}/treatment-plans/{planId}', [TreatmentPlanController::class, 'show'])
    ->name('patients.treatment-plans.show')
      ->middleware(PermissionMiddleware::class)
      ->permission('clinical.treatment_plans.view');

    $router->get('/patients/{id}/treatment-plans/{planId}/edit', [TreatmentPlanController::class, 'edit'])
    ->name('patients.treatment-plans.edit')
      ->middleware(PermissionMiddleware::class)
      ->permission('clinical.treatment_plans.edit');

    $router->put('/patients/{id}/treatment-plans/{planId}', [TreatmentPlanController::class, 'update'])
    ->name('patients.treatment-plans.update')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('clinical.treatment_plans.edit');

    $router->delete('/patients/{id}/treatment-plans/{planId}', [TreatmentPlanController::class, 'delete'])
    ->name('patients.treatment-plans.delete')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('clinical.treatment_plans.edit');

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
