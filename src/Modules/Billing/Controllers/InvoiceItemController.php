<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Invoice Items Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Billing\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class InvoiceItemController
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

        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            session()->setFlash('error', 'Agregue al menos un item.');
            return Response::redirect('/billing/invoices');
        }

        $subtotal = 0.0;
        $totalTax = 0.0;
        $total = 0.0;

        $this->db->delete('invoice_items', 'invoice_id = ?', [$invoiceId]);

        $sequence = 1;
        foreach ($items as $item) {
            $desc = trim((string) ($item['description'] ?? ''));
            $qty = (float) ($item['quantity'] ?? 0);
            $unit = (float) ($item['unit_price'] ?? 0);
            if ($desc === '' || $qty <= 0 || $unit < 0) {
                continue;
            }
            $lineSubtotal = $qty * $unit;
            $taxPct = (float) ($item['tax_percentage'] ?? 0);
            $taxAmount = $lineSubtotal * ($taxPct / 100);
            $lineTotal = $lineSubtotal + $taxAmount;

            $this->db->insert('invoice_items', [
                'invoice_id' => $invoiceId,
                'sequence' => $sequence++,
                'main_code' => $item['main_code'] ?? 'SERV',
                'description' => $desc,
                'quantity' => $qty,
                'unit_price' => $unit,
                'subtotal' => $lineSubtotal,
                'tax_percentage' => $taxPct,
                'tax_amount' => $taxAmount,
                'total' => $lineTotal,
            ]);

            $subtotal += $lineSubtotal;
            $totalTax += $taxAmount;
            $total += $lineTotal;
        }

        $this->db->update('invoices', [
            'subtotal' => $subtotal,
            'total_tax' => $totalTax,
            'total' => $total,
        ], 'id = ?', [$invoiceId]);

        session()->setFlash('success', 'Items guardados.');
        return Response::redirect('/billing/invoices');
    }
}
