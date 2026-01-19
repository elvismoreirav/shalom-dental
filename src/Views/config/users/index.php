<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Usuarios',
    'message' => 'Gestion de usuarios habilitada.',
]); ?>

<?php $this->endSection(); ?>
