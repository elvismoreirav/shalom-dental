<?php
/** @var array $files */
/** @var int $patientId */
$files = $files ?? [];
?>
<?php
    $canUpload = can('patients.files.upload');
    $canDeleteAll = can('patients.files.delete_all');
    $canDeleteOwn = can('patients.files.delete_own');
    $canViewAll = can('patients.files.view_all');
    $canViewOwn = can('patients.files.view_own');
    $canShowSection = $canUpload || $canDeleteAll || $canDeleteOwn || $canViewAll || $canViewOwn;
?>
<?php if ($canShowSection): ?>
<div class="mt-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Archivos</h3>

    <?php if ($canUpload): ?>
        <form action="/patients/<?= e((string) $patientId) ?>/files" method="post" enctype="multipart/form-data" class="mb-4 space-y-2">
            <?= csrf_field() ?>
            <div class="flex flex-wrap items-center gap-2">
                <input type="file" name="file" class="border rounded-lg px-3 py-2 text-sm" required>
                <select name="category" class="border rounded-lg px-3 py-2 text-sm">
                    <?php foreach (['photo','xray','document','consent','lab_result','other'] as $cat): ?>
                        <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="description" placeholder="Descripcion" class="border rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="px-4 py-2 rounded-lg bg-shalom-primary text-white text-sm">Subir</button>
            </div>
        </form>
    <?php endif; ?>

    <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600">Archivo</th>
                    <th class="px-4 py-2 text-left text-gray-600">Categoria</th>
                    <th class="px-4 py-2 text-left text-gray-600">Fecha</th>
                    <th class="px-4 py-2 text-right text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($files)): ?>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td class="px-4 py-2 text-gray-800"><?= e($file['original_name'] ?? '-') ?></td>
                            <td class="px-4 py-2 text-gray-600"><?= e($file['category'] ?? '-') ?></td>
                            <td class="px-4 py-2 text-gray-600"><?= e($file['created_at'] ?? '-') ?></td>
                            <td class="px-4 py-2 text-right">
                                <a class="text-shalom-primary hover:underline" href="/<?= e($file['file_path'] ?? '') ?>" target="_blank">Abrir</a>
                                <?php $isOwner = (int) ($file['uploaded_by_user_id'] ?? 0) === (int) (user()['id'] ?? 0); ?>
                                <?php if ($canDeleteAll || ($canDeleteOwn && $isOwner)): ?>
                                    <form action="/patients/<?= e((string) $patientId) ?>/files/<?= e((string) $file['id']) ?>" method="post" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="ml-3 text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">Sin archivos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
