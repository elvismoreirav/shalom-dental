<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Rutas del Módulo de Atención Clínica
 * =========================================================================
 */

use App\Core\Router;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\LocationMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Modules\ClinicalCare\Controllers\ClinicalCareController;
use App\Modules\ClinicalCare\Controllers\OdontogramController;
use App\Modules\ClinicalCare\Controllers\ClinicalNoteController;
use App\Modules\ClinicalCare\Controllers\ProcedureController;
use App\Modules\ClinicalCare\Controllers\TreatmentPlanController;
use App\Modules\ClinicalCare\Controllers\InvoiceGeneratorController;

/** @var Router $router */

$router->group(['middleware' => [AuthMiddleware::class, LocationMiddleware::class]], function (Router $router) {

    // =========================================================================
    // CLINICAL CARE - Main Attendance View
    // =========================================================================

    // Main attendance view for an appointment
    $router->get('/clinical/attend/{appointmentId}', [ClinicalCareController::class, 'attend'])
        ->name('clinical.attend')
        ->middleware(PermissionMiddleware::class)
        ->permission(['clinical.records.view', 'agenda.appointments.view_own', 'agenda.appointments.view_all']);

    // Start appointment (AJAX)
    $router->post('/clinical/attend/{appointmentId}/start', [ClinicalCareController::class, 'startAppointment'])
        ->name('clinical.attend.start')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('agenda.appointments.start');

    // Complete appointment (AJAX)
    $router->post('/clinical/attend/{appointmentId}/complete', [ClinicalCareController::class, 'completeAppointment'])
        ->name('clinical.attend.complete')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('agenda.appointments.finish');

    // Patient clinical history
    $router->get('/clinical/patients/{patientId}/history', [ClinicalCareController::class, 'patientHistory'])
        ->name('clinical.patient.history')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.records.view');

    // =========================================================================
    // ODONTOGRAM API
    // =========================================================================

    // Get patient odontogram
    $router->get('/api/clinical/patients/{patientId}/odontogram', [OdontogramController::class, 'show'])
        ->name('api.clinical.odontogram.show')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.odontogram.view');

    // Update single tooth
    $router->put('/api/clinical/patients/{patientId}/odontogram/{toothNumber}', [OdontogramController::class, 'updateTooth'])
        ->name('api.clinical.odontogram.update')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.odontogram.edit');

    // Bulk update teeth
    $router->post('/api/clinical/patients/{patientId}/odontogram/bulk', [OdontogramController::class, 'bulkUpdate'])
        ->name('api.clinical.odontogram.bulk')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.odontogram.edit');

    // Get tooth history
    $router->get('/api/clinical/patients/{patientId}/odontogram/{toothNumber}/history', [OdontogramController::class, 'toothHistory'])
        ->name('api.clinical.odontogram.history')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.odontogram.view');

    // =========================================================================
    // CLINICAL NOTES API
    // =========================================================================

    // Get clinical note for appointment
    $router->get('/api/clinical/appointments/{appointmentId}/note', [ClinicalNoteController::class, 'show'])
        ->name('api.clinical.note.show')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.notes.view');

    // Save clinical note
    $router->post('/api/clinical/appointments/{appointmentId}/note', [ClinicalNoteController::class, 'save'])
        ->name('api.clinical.note.save')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.notes.create');

    // Sign clinical note
    $router->post('/api/clinical/appointments/{appointmentId}/note/sign', [ClinicalNoteController::class, 'sign'])
        ->name('api.clinical.note.sign')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.notes.sign');

    // Amend clinical note
    $router->post('/api/clinical/appointments/{appointmentId}/note/amend', [ClinicalNoteController::class, 'amend'])
        ->name('api.clinical.note.amend')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.notes.amend');

    // Get all notes for a patient
    $router->get('/api/clinical/patients/{patientId}/notes', [ClinicalNoteController::class, 'patientNotes'])
        ->name('api.clinical.patient.notes')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.notes.view');

    // =========================================================================
    // PROCEDURES API
    // =========================================================================

    // List procedures for appointment
    $router->get('/api/clinical/appointments/{appointmentId}/procedures', [ProcedureController::class, 'index'])
        ->name('api.clinical.procedures.index')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.procedures.view');

    // Add procedure to appointment
    $router->post('/api/clinical/appointments/{appointmentId}/procedures', [ProcedureController::class, 'store'])
        ->name('api.clinical.procedures.store')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.procedures.create');

    // Update procedure
    $router->put('/api/clinical/procedures/{procedureId}', [ProcedureController::class, 'update'])
        ->name('api.clinical.procedures.update')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.procedures.create');

    // Delete procedure
    $router->delete('/api/clinical/procedures/{procedureId}', [ProcedureController::class, 'delete'])
        ->name('api.clinical.procedures.delete')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.procedures.create');

    // Get pending procedures for invoicing
    $router->get('/api/clinical/appointments/{appointmentId}/procedures/pending-invoice', [ProcedureController::class, 'pendingInvoice'])
        ->name('api.clinical.procedures.pending')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.procedures.invoice');

    // =========================================================================
    // TREATMENT PLANS
    // =========================================================================

    // List treatment plans for patient
    $router->get('/clinical/patients/{patientId}/treatment-plans', [TreatmentPlanController::class, 'index'])
        ->name('clinical.treatment-plans.index')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.treatment_plans.view');

    // Create treatment plan form
    $router->get('/clinical/treatment-plans/create', [TreatmentPlanController::class, 'create'])
        ->name('clinical.treatment-plans.create')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.treatment_plans.create');

    // Store treatment plan
    $router->post('/clinical/treatment-plans', [TreatmentPlanController::class, 'store'])
        ->name('clinical.treatment-plans.store')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.treatment_plans.create');

    // Show treatment plan
    $router->get('/clinical/treatment-plans/{id}', [TreatmentPlanController::class, 'show'])
        ->name('clinical.treatment-plans.show')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.treatment_plans.view');

    // Update treatment plan status (AJAX)
    $router->put('/api/clinical/treatment-plans/{id}/status', [TreatmentPlanController::class, 'updateStatus'])
        ->name('api.clinical.treatment-plans.status')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.treatment_plans.edit');

    // Add item to plan (AJAX)
    $router->post('/api/clinical/treatment-plans/{id}/items', [TreatmentPlanController::class, 'addItem'])
        ->name('api.clinical.treatment-plans.add-item')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.treatment_plans.edit');

    // Remove item from plan (AJAX)
    $router->delete('/api/clinical/treatment-plans/{id}/items/{itemId}', [TreatmentPlanController::class, 'removeItem'])
        ->name('api.clinical.treatment-plans.remove-item')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.treatment_plans.edit');

    // Get plan items (AJAX)
    $router->get('/api/clinical/treatment-plans/{id}/items', [TreatmentPlanController::class, 'getItems'])
        ->name('api.clinical.treatment-plans.items')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.treatment_plans.view');

    // =========================================================================
    // INVOICE GENERATION FROM CLINICAL
    // =========================================================================

    // Preview invoice
    $router->get('/api/clinical/appointments/{appointmentId}/invoice/preview', [InvoiceGeneratorController::class, 'preview'])
        ->name('api.clinical.invoice.preview')
        ->middleware(PermissionMiddleware::class)
        ->permission('clinical.procedures.invoice');

    // Generate invoice
    $router->post('/api/clinical/appointments/{appointmentId}/invoice/generate', [InvoiceGeneratorController::class, 'generate'])
        ->name('api.clinical.invoice.generate')
        ->middleware([PermissionMiddleware::class, CsrfMiddleware::class])
        ->permission('clinical.procedures.invoice');
});
