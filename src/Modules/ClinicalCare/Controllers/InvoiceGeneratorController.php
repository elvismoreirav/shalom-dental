<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Invoice Generator Controller
 * =========================================================================
 * Controller for generating invoices from clinical procedures
 */

namespace App\Modules\ClinicalCare\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Modules\ClinicalCare\Services\InvoiceGeneratorService;

class InvoiceGeneratorController
{
    private Database $db;
    private InvoiceGeneratorService $invoiceService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->invoiceService = new InvoiceGeneratorService();
    }

    /**
     * Preview invoice data without generating
     */
    public function preview(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $procedureIds = $request->query('procedures', []);

        if (!is_array($procedureIds)) {
            $procedureIds = explode(',', $procedureIds);
        }
        $procedureIds = array_filter(array_map('intval', $procedureIds));

        $result = $this->invoiceService->previewFromAppointment($appointmentId, $procedureIds);

        if (!$result['success']) {
            return Response::json($result, 400);
        }

        // Get available emission points
        $locationId = (int) session('current_location_id', 0);
        $emissionPoints = $this->db->select(
            "SELECT id, code, description FROM emission_points WHERE location_id = ? AND is_active = 1",
            [$locationId]
        );

        $result['data']['emission_points'] = $emissionPoints;

        return Response::json($result);
    }

    /**
     * Generate invoice from appointment procedures
     */
    public function generate(Request $request): Response
    {
        $appointmentId = (int) $request->param('appointmentId');
        $userId = (int) (user()['id'] ?? 0);

        $procedureIds = $request->input('procedure_ids', []);
        if (!is_array($procedureIds)) {
            $procedureIds = [];
        }
        $procedureIds = array_filter(array_map('intval', $procedureIds));

        $emissionPointId = (int) $request->input('emission_point_id');
        if ($emissionPointId <= 0) {
            return Response::json(['success' => false, 'message' => 'Debe seleccionar un punto de emisión'], 400);
        }

        $result = $this->invoiceService->generateFromAppointment(
            $appointmentId,
            $procedureIds,
            $emissionPointId,
            $userId
        );

        if (!$result['success']) {
            return Response::json($result, 400);
        }

        return Response::json($result);
    }
}
