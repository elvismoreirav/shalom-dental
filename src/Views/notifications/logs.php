<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Notificaciones - Logs',
    'message' => 'Logs de notificaciones habilitados.',
]); ?>

<?php $this->endSection(); ?>
