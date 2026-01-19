<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Patient File Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Patients\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class PatientFileController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function store(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $patient = $this->db->selectOne(
            "SELECT id FROM patients WHERE id = ? AND organization_id = ?",
            [$patientId, $organizationId]
        );

        if (!$patient) {
            return Response::notFound('Paciente no encontrado');
        }

        $file = $request->file('file');
        if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            session()->setFlash('error', 'Debe seleccionar un archivo valido.');
            return Response::redirect('/patients/' . $patientId);
        }

        $maxSize = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxSize) {
            session()->setFlash('error', 'El archivo supera el limite de 5MB.');
            return Response::redirect('/patients/' . $patientId);
        }

        $allowed = [
            'image/jpeg',
            'image/png',
            'application/pdf',
        ];
        $mime = $file['type'] ?? 'application/octet-stream';
        if (!in_array($mime, $allowed, true)) {
            session()->setFlash('error', 'Tipo de archivo no permitido.');
            return Response::redirect('/patients/' . $patientId);
        }

        $originalName = $file['name'] ?? 'archivo';
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(8)) . ($extension ? ('.' . $extension) : '');

        $uploadDir = app()->getBasePath() . '/storage/uploads/patients/' . $patientId;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            session()->setFlash('error', 'No se pudo guardar el archivo.');
            return Response::redirect('/patients/' . $patientId);
        }

        $this->db->insert('patient_files', [
            'patient_id' => $patientId,
            'uploaded_by_user_id' => $userId ?: 1,
            'file_name' => $safeName,
            'original_name' => $originalName,
            'file_path' => 'storage/uploads/patients/' . $patientId . '/' . $safeName,
            'file_size' => (int) ($file['size'] ?? 0),
            'mime_type' => $mime,
            'category' => $request->input('category', 'document'),
            'description' => trim((string) $request->input('description')) ?: null,
        ]);

        session()->setFlash('success', 'Archivo subido correctamente.');
        return Response::redirect('/patients/' . $patientId);
    }

    public function delete(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $fileId = (int) $request->param('fileId');
        $organizationId = (int) session('organization_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $file = $this->db->selectOne(
            "SELECT pf.* FROM patient_files pf
             JOIN patients p ON p.id = pf.patient_id
             WHERE pf.id = ? AND pf.patient_id = ? AND p.organization_id = ? AND pf.is_deleted = 0",
            [$fileId, $patientId, $organizationId]
        );

        if (!$file) {
            return Response::notFound('Archivo no encontrado');
        }

        $canDeleteAll = can('patients.files.delete_all');
        $canDeleteOwn = can('patients.files.delete_own');
        if (!$canDeleteAll) {
            if (!$canDeleteOwn || (int) ($file['uploaded_by_user_id'] ?? 0) !== $userId) {
                return Response::forbidden('Sin permisos');
            }
        }

        $this->db->update('patient_files', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by_user_id' => $userId ?: null,
        ], 'id = ?', [$fileId]);

        session()->setFlash('success', 'Archivo eliminado.');
        return Response::redirect('/patients/' . $patientId);
    }
}
