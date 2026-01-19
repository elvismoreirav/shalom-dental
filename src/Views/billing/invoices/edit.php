<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?= $this->include('billing.invoices._form', [
    'patients' => $patients ?? [],
    'appointmentTypes' => $appointmentTypes ?? [],
    'emissionPoints' => $emissionPoints ?? [],
    'invoice' => $invoice ?? [],
    'items' => $items ?? [],
    'payments' => $payments ?? [],
    'invoiceDiscount' => 0,
    'action' => '/billing/invoices/' . ($invoice['id'] ?? 0),
    'method' => 'PUT',
]); ?>

<?php $this->endSection(); ?>
