<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'No Shows',
    'message' => 'Reporte de no shows habilitado.',
]); ?>

<?php $this->endSection(); ?>
