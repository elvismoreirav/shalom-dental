<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Agenda - Sala de Espera',
    'message' => 'Sala de espera habilitada.',
]); ?>

<?php $this->endSection(); ?>
