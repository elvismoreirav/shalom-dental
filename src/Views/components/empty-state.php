<?php
/** @var string $title */
/** @var string $message */
/** @var string|null $hint */
?>
<div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-2"><?= e($title ?? 'Modulo') ?></h2>
    <p class="text-sm text-gray-600 mb-4">
        <?= e($message ?? 'Vista habilitada. Falta implementar la logica y los datos reales.') ?>
    </p>
    <?php if (!empty($hint)): ?>
        <p class="text-xs text-gray-500"><?= e($hint) ?></p>
    <?php endif; ?>
</div>
