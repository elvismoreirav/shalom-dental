<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Configuracion',
    'message' => 'Panel de configuracion habilitado.',
]); ?>

<?php $this->endSection(); ?>
