<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Agenda
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Modules\Agenda\Controllers\AgendaController;
use App\Modules\Agenda\Controllers\AppointmentController;
use App\Modules\Agenda\Controllers\AppointmentDetailController;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/agenda', [AgendaController::class, 'index'])
    ->name('agenda.index')
      ->middleware(PermissionMiddleware::class)
      ->permission(['agenda.appointments.view_all', 'agenda.appointments.view_own']);

    $router->get('/agenda/{id}', [AppointmentDetailController::class, 'show'])
    ->name('agenda.show')
    ->middleware(PermissionMiddleware::class)
    ->permission(['agenda.appointments.view_all', 'agenda.appointments.view_own']);

    $router->get('/agenda/create', [AppointmentController::class, 'create'])
    ->name('agenda.create')
    ->middleware(PermissionMiddleware::class)
    ->permission('agenda.appointments.create');

    $router->get('/agenda/{id}/edit', [AppointmentController::class, 'edit'])
    ->name('agenda.edit')
    ->middleware(PermissionMiddleware::class)
    ->permission(['agenda.appointments.edit_all', 'agenda.appointments.edit_own']);

    $router->post('/agenda', [AppointmentController::class, 'store'])
    ->name('agenda.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('agenda.appointments.create');

    $router->put('/agenda/{id}', [AppointmentController::class, 'update'])
    ->name('agenda.update')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission(['agenda.appointments.edit_all', 'agenda.appointments.edit_own']);

    $router->post('/agenda/{id}/cancel', [AppointmentController::class, 'cancel'])
    ->name('agenda.cancel')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission(['agenda.appointments.cancel_all', 'agenda.appointments.cancel_own']);

    $router->post('/agenda/{id}/no-show', [AppointmentController::class, 'noShow'])
    ->name('agenda.no-show')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('agenda.appointments.no_show');

    $router->get('/agenda/calendar', function () {
        return Response::view('agenda.calendar', ['title' => 'Agenda - Calendario']);
    })->name('agenda.calendar')
      ->middleware(PermissionMiddleware::class)
      ->permission(['agenda.appointments.view_all', 'agenda.appointments.view_own']);

    $router->get('/agenda/waiting-room', function () {
        return Response::view('agenda.waiting-room', ['title' => 'Sala de Espera']);
    })->name('agenda.waiting-room')
      ->middleware(PermissionMiddleware::class)
      ->permission(['agenda.appointments.view_all', 'agenda.appointments.view_own']);

    $router->get('/agenda/waiting-list', function () {
        return Response::view('agenda.waiting-list', ['title' => 'Lista de Espera']);
    })->name('agenda.waiting-list')
      ->middleware(PermissionMiddleware::class)
      ->permission('agenda.waiting_list.view');
});
