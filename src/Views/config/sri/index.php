<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'SRI',
    'message' => 'Configuracion SRI habilitada.',
]); ?>

<?php $this->endSection(); ?>
