<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Configuracion
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Modules\Config\Controllers\AppointmentTypeController;
use App\Modules\Config\Controllers\MaterialController;
use App\Modules\Config\Controllers\ServiceCategoryController;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/config', function () {
        return Response::view('config.index', ['title' => 'Configuracion']);
    })->name('config.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.organization.view');

    $router->get('/config/organization', function () {
        return Response::view('config.organization.edit', ['title' => 'Organizacion']);
    })->name('config.organization.edit')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.organization.view');

    $router->get('/config/locations', function () {
        return Response::view('config.locations.index', ['title' => 'Sedes']);
    })->name('config.locations.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.locations.view');

    $router->get('/config/resources', function () {
        return Response::view('config.resources.index', ['title' => 'Recursos']);
    })->name('config.resources.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.resources.view');

    $router->get('/config/users', function () {
        return Response::view('config.users.index', ['title' => 'Usuarios']);
    })->name('config.users.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.users.view');

    $router->get('/config/users/create', function () {
        return Response::view('config.users.create', ['title' => 'Crear Usuario']);
    })->name('config.users.create')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.users.create');

    $router->get('/config/users/{id}/edit', function () {
        return Response::view('config.users.edit', ['title' => 'Editar Usuario']);
    })->name('config.users.edit')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.users.edit');

    $router->get('/config/appointment-types', [AppointmentTypeController::class, 'index'])
        ->name('config.appointment-types.index')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.appointment_types.view');

    $router->get('/config/appointment-types/create', [AppointmentTypeController::class, 'create'])
        ->name('config.appointment-types.create')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.appointment_types.manage');

    $router->post('/config/appointment-types', [AppointmentTypeController::class, 'store'])
        ->name('config.appointment-types.store')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('config.appointment_types.manage');

    $router->get('/config/appointment-types/{id}/edit', [AppointmentTypeController::class, 'edit'])
        ->name('config.appointment-types.edit')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.appointment_types.manage');

    $router->put('/config/appointment-types/{id}', [AppointmentTypeController::class, 'update'])
        ->name('config.appointment-types.update')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('config.appointment_types.manage');

    $router->get('/config/service-categories', [ServiceCategoryController::class, 'index'])
        ->name('config.service-categories.index')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.service_categories.view');

    $router->get('/config/service-categories/create', [ServiceCategoryController::class, 'create'])
        ->name('config.service-categories.create')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.service_categories.manage');

    $router->post('/config/service-categories', [ServiceCategoryController::class, 'store'])
        ->name('config.service-categories.store')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('config.service_categories.manage');

    $router->get('/config/service-categories/{id}/edit', [ServiceCategoryController::class, 'edit'])
        ->name('config.service-categories.edit')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.service_categories.manage');

    $router->put('/config/service-categories/{id}', [ServiceCategoryController::class, 'update'])
        ->name('config.service-categories.update')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('config.service_categories.manage');

    $router->get('/config/materials', [MaterialController::class, 'index'])
        ->name('config.materials.index')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.materials.view');

    $router->get('/config/materials/create', [MaterialController::class, 'create'])
        ->name('config.materials.create')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.materials.manage');

    $router->post('/config/materials', [MaterialController::class, 'store'])
        ->name('config.materials.store')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('config.materials.manage');

    $router->get('/config/materials/{id}/edit', [MaterialController::class, 'edit'])
        ->name('config.materials.edit')
        ->middleware(PermissionMiddleware::class)
        ->permission('config.materials.manage');

    $router->put('/config/materials/{id}', [MaterialController::class, 'update'])
        ->name('config.materials.update')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('config.materials.manage');

    $router->get('/config/schedules', function () {
        return Response::view('config.schedules.index', ['title' => 'Horarios']);
    })->name('config.schedules.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.schedules.view');

    $router->get('/config/holidays', function () {
        return Response::view('config.holidays.index', ['title' => 'Feriados']);
    })->name('config.holidays.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.holidays.manage');

    $router->get('/config/sri', function () {
        return Response::view('config.sri.index', ['title' => 'SRI']);
    })->name('config.sri.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.sri.view');
});
