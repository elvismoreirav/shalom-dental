<?php $this->extend('layouts.app'); ?>

<?php $this->section('content'); ?>

<?php $this->component('empty-state', [
    'title' => 'Auditoria',
    'message' => 'Vista de auditoria habilitada.',
]); ?>

<?php $this->endSection(); ?>
