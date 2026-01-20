<?php

namespace App\Modules\Clinical\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Modules\Clinical\Repositories\PatientOdontogramRepository;

class OdontogramController
{
    private PatientOdontogramRepository $odontogram;

    public function __construct()
    {
        $this->odontogram = new PatientOdontogramRepository(Database::getInstance());
    }

    public function index(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $rows = $this->odontogram->getByPatientId($patientId);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['tooth_number']] = $row;
        }
        $history = $this->odontogram->getHistoryByPatientId($patientId, 50);

        return Response::view('clinical.odontogram.index', [
            'title' => 'Odontograma',
            'patientId' => $patientId,
            'odontogram' => $map,
            'history' => $history,
        ]);
    }

    public function updateTooth(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $toothNumber = (string) $request->param('tooth');
        $userId = (int) (user()['id'] ?? 0);

        $payload = [
            'tooth_status' => $request->input('tooth_status', 'healthy'),
            'surfaces' => $this->normalizeSurfaces($request->input('surfaces')),
            'mobility' => $request->input('mobility', '0'),
            'periodontal_status' => $request->input('periodontal_status', 'healthy'),
            'notes' => $request->input('notes'),
        ];

        $result = $this->odontogram->upsertTooth($patientId, $toothNumber, $payload, $userId);
        $previous = $result['previous'];
        $current = $result['current'];

        if ($current && ($previous === null || $previous['tooth_status'] !== $current['tooth_status'] || $previous['surfaces'] !== $current['surfaces'])) {
            $this->odontogram->addHistory([
                'patient_id' => $patientId,
                'tooth_number' => $toothNumber,
                'appointment_id' => (int) $request->input('appointment_id') ?: null,
                'previous_status' => $previous['tooth_status'] ?? null,
                'new_status' => $current['tooth_status'],
                'previous_surfaces' => $previous['surfaces'] ?? null,
                'new_surfaces' => $current['surfaces'] ?? null,
                'procedure_description' => $request->input('procedure_description'),
                'changed_by_user_id' => $userId ?: 1,
            ]);
        }

        return Response::success([
            'tooth_number' => $toothNumber,
            'tooth_status' => $current['tooth_status'] ?? 'healthy',
        ], 'Actualizado');
    }

    private function normalizeSurfaces(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
            $parts = array_filter(array_map('trim', preg_split('/[,\s]+/', $value)));
            return $parts ? json_encode($parts, JSON_UNESCAPED_UNICODE) : json_encode(['raw' => $value], JSON_UNESCAPED_UNICODE);
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return null;
    }
}
