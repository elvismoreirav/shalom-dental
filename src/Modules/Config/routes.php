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

    $router->get('/config/appointment-types', function () {
        return Response::view('config.appointment-types.index', ['title' => 'Tipos de Cita']);
    })->name('config.appointment-types.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('config.appointment_types.view');

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
