<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Productividad',
    'message' => 'Reporte de productividad habilitado.',
]); ?>

<?php $this->endSection(); ?>
