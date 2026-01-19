<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Recursos',
    'message' => 'Gestion de recursos habilitada.',
]); ?>

<?php $this->endSection(); ?>
