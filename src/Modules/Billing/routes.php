<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Modulo Facturacion
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Modules\Billing\Controllers\InvoiceController;
use App\Modules\Billing\Controllers\InvoiceItemController;
use App\Modules\Billing\Controllers\InvoicePaymentController;
use App\Modules\Billing\Controllers\SriMonitorController;
use App\Modules\Billing\Controllers\CreditNoteController;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {
    $router->get('/billing', function () {
        return Response::view('billing.index', ['title' => 'Facturacion']);
    })->name('billing.index')
      ->middleware(PermissionMiddleware::class)
      ->permission(['billing.invoices.view_all', 'billing.invoices.view_own']);

    $router->get('/billing/invoices', [InvoiceController::class, 'index'])
    ->name('billing.invoices.index')
      ->middleware(PermissionMiddleware::class)
      ->permission(['billing.invoices.view_all', 'billing.invoices.view_own']);

    $router->get('/billing/invoices/create', [InvoiceController::class, 'create'])
    ->name('billing.invoices.create')
      ->middleware(PermissionMiddleware::class)
      ->permission('billing.invoices.create');

    $router->post('/billing/invoices', [InvoiceController::class, 'store'])
    ->name('billing.invoices.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.invoices.create');

    $router->post('/billing/invoices/{id}/items', [InvoiceItemController::class, 'store'])
    ->name('billing.invoices.items.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.invoices.create');

    $router->post('/billing/invoices/{id}/payments', [InvoicePaymentController::class, 'store'])
    ->name('billing.invoices.payments.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.invoices.create');

    $router->get('/billing/invoices/{id}', [InvoiceController::class, 'show'])
    ->name('billing.invoices.show')
      ->middleware(PermissionMiddleware::class)
      ->permission(['billing.invoices.view_all', 'billing.invoices.view_own']);

    $router->get('/billing/invoices/{id}/edit', [InvoiceController::class, 'edit'])
    ->name('billing.invoices.edit')
    ->middleware(PermissionMiddleware::class)
    ->permission('billing.invoices.create');

    $router->put('/billing/invoices/{id}', [InvoiceController::class, 'update'])
    ->name('billing.invoices.update')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.invoices.create');

    $router->post('/billing/invoices/{id}/void', [InvoiceController::class, 'void'])
    ->name('billing.invoices.void')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.invoices.void');

    $router->get('/billing/invoices/{id}/pdf', [InvoiceController::class, 'exportPdf'])
    ->name('billing.invoices.pdf')
    ->middleware(PermissionMiddleware::class)
    ->permission(['billing.invoices.view_all', 'billing.invoices.view_own']);

    $router->get('/billing/credit-notes', [CreditNoteController::class, 'index'])
    ->name('billing.credit-notes.index')
      ->middleware(PermissionMiddleware::class)
      ->permission('billing.credit_notes.create');

    $router->get('/billing/credit-notes/create', [CreditNoteController::class, 'create'])
    ->name('billing.credit-notes.create')
    ->middleware(PermissionMiddleware::class)
    ->permission('billing.credit_notes.create');

    $router->post('/billing/credit-notes', [CreditNoteController::class, 'store'])
    ->name('billing.credit-notes.store')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.credit_notes.create');

    $router->get('/billing/monitor', [SriMonitorController::class, 'index'])
    ->name('billing.monitor.index')
    ->middleware(PermissionMiddleware::class)
    ->permission('billing.sri_monitor.view');

    $router->post('/billing/monitor/{id}/retry', [SriMonitorController::class, 'retry'])
    ->name('billing.monitor.retry')
    ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
    ->permission('billing.sri_monitor.retry');
});
