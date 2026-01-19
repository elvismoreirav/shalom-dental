<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Credit Note Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Billing\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class CreditNoteController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $locationId = (int) session('current_location_id', 0);
        $sql = "SELECT cn.id, cn.issue_date, cn.amount, cn.status, i.id as invoice_id, i.buyer_name
                FROM credit_notes cn
                JOIN invoices i ON i.id = cn.invoice_id
                WHERE 1=1";
        $params = [];

        if ($locationId > 0) {
            $sql .= " AND cn.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY cn.issue_date DESC, cn.id DESC";
        $notes = $this->db->select($sql, $params);

        return Response::view('billing.credit-notes.index', [
            'title' => 'Notas de Credito',
            'notes' => $notes,
        ]);
    }

    public function create(Request $request): Response
    {
        $locationId = (int) session('current_location_id', 0);
        $invoices = $this->db->select(
            "SELECT id, buyer_name, total FROM invoices WHERE location_id = ? ORDER BY issue_date DESC LIMIT 200",
            [$locationId]
        );

        return Response::view('billing.credit-notes.create', [
            'title' => 'Crear Nota de Credito',
            'invoices' => $invoices,
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);
        $userId = (int) (user()['id'] ?? 0);

        $invoiceId = (int) $request->input('invoice_id');
        $reason = trim((string) $request->input('reason'));
        $amount = (float) $request->input('amount', 0);

        if ($organizationId <= 0 || $locationId <= 0 || $invoiceId <= 0 || $reason === '' || $amount <= 0) {
            session()->setFlash('error', 'Complete los campos requeridos.');
            return Response::redirect('/billing/credit-notes/create');
        }

        $this->db->insert('credit_notes', [
            'organization_id' => $organizationId,
            'location_id' => $locationId,
            'invoice_id' => $invoiceId,
            'issue_date' => date('Y-m-d'),
            'reason' => $reason,
            'amount' => $amount,
            'status' => 'issued',
            'created_by_user_id' => $userId ?: 1,
        ]);

        session()->setFlash('success', 'Nota de credito creada.');
        return Response::redirect('/billing/credit-notes');
    }
}
