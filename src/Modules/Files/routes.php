<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Archivos
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/files', function () {
        return Response::view('files.index', ['title' => 'Archivos']);
    })->name('files.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('patients.files.view');
});
