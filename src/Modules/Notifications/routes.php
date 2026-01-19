<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Notificaciones
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Modules\Notifications\Controllers\TemplateController;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/notifications/logs', function () {
        return Response::view('notifications.logs', ['title' => 'Notificaciones - Logs']);
    })->name('notifications.logs')
      ->middleware(PermissionMiddleware::class)
      ->permission('notifications.logs.view');

    $router->get('/notifications/templates', [TemplateController::class, 'index'])
    ->name('notifications.templates')
      ->middleware(PermissionMiddleware::class)
      ->permission('notifications.templates.view');

    $router->get('/notifications/templates/create', [TemplateController::class, 'create'])
    ->name('notifications.templates.create')
    ->middleware(PermissionMiddleware::class)
    ->permission('notifications.templates.manage');

    $router->post('/notifications/templates', [TemplateController::class, 'store'])
    ->name('notifications.templates.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('notifications.templates.manage');

    $router->get('/notifications/templates/{id}/edit', [TemplateController::class, 'edit'])
    ->name('notifications.templates.edit')
    ->middleware(PermissionMiddleware::class)
    ->permission('notifications.templates.manage');

    $router->post('/notifications/templates/{id}/update', [TemplateController::class, 'update'])
    ->name('notifications.templates.update')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('notifications.templates.manage');

    $router->delete('/notifications/templates/{id}', [TemplateController::class, 'delete'])
    ->name('notifications.templates.delete')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('notifications.templates.manage');
});
