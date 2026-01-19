<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Auditoria
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/audit', function () {
        return Response::view('audit.index', ['title' => 'Auditoria']);
    })->name('audit.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('audit.logs.view');
});
