<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Agenda - Lista de Espera',
    'message' => 'Lista de espera habilitada.',
]); ?>

<?php $this->endSection(); ?>
