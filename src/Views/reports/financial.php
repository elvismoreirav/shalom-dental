<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Financiero',
    'message' => 'Reporte financiero habilitado.',
]); ?>

<?php $this->endSection(); ?>
