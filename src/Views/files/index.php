<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Archivos',
    'message' => 'Modulo de archivos habilitado.',
]); ?>

<?php $this->endSection(); ?>
