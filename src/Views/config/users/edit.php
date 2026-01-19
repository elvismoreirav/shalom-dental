<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Editar Usuario',
    'message' => 'Edicion de usuarios habilitada.',
]); ?>

<?php $this->endSection(); ?>
