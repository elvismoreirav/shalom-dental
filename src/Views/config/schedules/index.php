<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Horarios',
    'message' => 'Gestion de horarios habilitada.',
]); ?>

<?php $this->endSection(); ?>
