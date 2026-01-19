<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Reporte de Citas',
    'message' => 'Reporte de citas habilitado.',
]); ?>

<?php $this->endSection(); ?>
