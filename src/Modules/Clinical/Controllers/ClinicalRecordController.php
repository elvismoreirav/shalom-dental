<?php

namespace App\Modules\Clinical\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Modules\Clinical\Repositories\PatientClinicalRecordRepository;

class ClinicalRecordController
{
    private PatientClinicalRecordRepository $records;

    public function __construct()
    {
        $this->records = new PatientClinicalRecordRepository(Database::getInstance());
    }

    public function edit(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $record = $this->records->findByPatientId($patientId) ?? [];

        return Response::view('clinical.records.edit', [
            'title' => 'Historial Clínico',
            'patientId' => $patientId,
            'record' => $record,
        ]);
    }

    public function update(Request $request): Response
    {
        $patientId = (int) $request->param('id');
        $userId = (int) (user()['id'] ?? 0);

        $payload = [
            'medical_history' => $request->input('medical_history'),
            'surgical_history' => $request->input('surgical_history'),
            'family_history' => $request->input('family_history'),
            'habits' => $request->input('habits'),
            'dental_history' => $request->input('dental_history'),
            'last_dental_visit' => $request->input('last_dental_visit'),
            'oral_hygiene_frequency' => $request->input('oral_hygiene_frequency'),
            'extraoral_exam' => $request->input('extraoral_exam'),
            'intraoral_exam' => $request->input('intraoral_exam'),
            'occlusion_type' => $request->input('occlusion_type'),
            'occlusion_notes' => $request->input('occlusion_notes'),
            'general_diagnosis' => $request->input('general_diagnosis'),
        ];

        $this->records->upsert($patientId, $payload, $userId);

        session()->setFlash('success', 'Historial clínico actualizado.');
        return Response::redirect('/patients/' . $patientId . '/clinical-record');
    }
}
