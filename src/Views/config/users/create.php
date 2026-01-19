<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Crear Usuario',
    'message' => 'Creacion de usuarios habilitada.',
]); ?>

<?php $this->endSection(); ?>
