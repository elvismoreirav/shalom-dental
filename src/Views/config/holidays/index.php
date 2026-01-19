<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Feriados',
    'message' => 'Gestion de feriados habilitada.',
]); ?>

<?php $this->endSection(); ?>
