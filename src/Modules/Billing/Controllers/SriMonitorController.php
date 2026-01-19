<?php
/**
 * =========================================================================
 * SHALOM DENTAL - SRI Monitor Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Billing\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class SriMonitorController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $locationId = (int) session('current_location_id', 0);

        $sql = "SELECT id, issue_date, buyer_name, total, status, sri_error_messages
                FROM invoices
                WHERE status IN ('rejected','contingency','sent','pending')";
        $params = [];

        if ($locationId > 0) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY issue_date DESC, id DESC";

        $invoices = $this->db->select($sql, $params);

        return Response::view('billing.monitor.index', [
            'title' => 'Monitor SRI',
            'invoices' => $invoices,
        ]);
    }

    public function retry(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $invoice = $this->db->selectOne("SELECT id FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) {
            return Response::notFound('Factura no encontrada');
        }

        $this->db->update('invoices', [
            'status' => 'pending',
        ], 'id = ?', [$invoiceId]);

        session()->setFlash('success', 'Reintento enviado.');
        return Response::redirect('/billing/monitor');
    }
}
