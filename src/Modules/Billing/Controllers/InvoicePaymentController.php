<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Invoice Payment Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Billing\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class InvoicePaymentController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function store(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $invoice = $this->db->selectOne("SELECT id FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) {
            return Response::notFound('Factura no encontrada');
        }

        $payments = $request->input('payments', []);
        if (!is_array($payments) || empty($payments)) {
            session()->setFlash('error', 'Agregue al menos un pago.');
            return Response::redirect('/billing/invoices/' . $invoiceId);
        }

        $this->db->delete('invoice_payments', 'invoice_id = ?', [$invoiceId]);

        $invoiceRow = $this->db->selectOne(\"SELECT total FROM invoices WHERE id = ?\", [$invoiceId]);
        $invoiceTotal = (float) ($invoiceRow['total'] ?? 0);
        $sum = 0.0;

        foreach ($payments as $payment) {
            $amount = (float) ($payment['amount'] ?? 0);
            $method = trim((string) ($payment['payment_method_code'] ?? ''));
            if ($amount <= 0 || $method === '') {
                continue;
            }

            $sum += $amount;
            $this->db->insert('invoice_payments', [
                'invoice_id' => $invoiceId,
                'payment_method_code' => $method,
                'payment_method_name' => trim((string) ($payment['payment_method_name'] ?? '')) ?: null,
                'amount' => $amount,
                'term_days' => (int) ($payment['term_days'] ?? 0),
                'time_unit' => trim((string) ($payment['time_unit'] ?? 'dias')) ?: 'dias',
                'card_brand' => trim((string) ($payment['card_brand'] ?? '')) ?: null,
                'card_last_four' => trim((string) ($payment['card_last_four'] ?? '')) ?: null,
                'authorization_code' => trim((string) ($payment['authorization_code'] ?? '')) ?: null,
                'reference_number' => trim((string) ($payment['reference_number'] ?? '')) ?: null,
            ]);
        }

        if ($sum > 0 && $invoiceTotal > 0 && $sum >= $invoiceTotal) {
            $this->db->update('invoices', [
                'status' => 'sent',
            ], 'id = ?', [$invoiceId]);
        }

        session()->setFlash('success', 'Pagos guardados.');
        return Response::redirect('/billing/invoices/' . $invoiceId);
    }
}
