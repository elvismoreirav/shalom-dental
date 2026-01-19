<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Sedes',
    'message' => 'Gestion de sedes habilitada.',
]); ?>

<?php $this->endSection(); ?>
