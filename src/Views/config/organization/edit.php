<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Organizacion',
    'message' => 'Configuracion de organizacion habilitada.',
]); ?>

<?php $this->endSection(); ?>
