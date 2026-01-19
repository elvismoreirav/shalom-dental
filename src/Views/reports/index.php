<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Reportes',
    'message' => 'Panel de reportes habilitado.',
]); ?>

<?php $this->endSection(); ?>
