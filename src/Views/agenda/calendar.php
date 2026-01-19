<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Agenda - Calendario',
    'message' => 'Calendario habilitado.',
]); ?>

<?php $this->endSection(); ?>
