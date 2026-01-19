<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Invoice PDF Service
 * =========================================================================
 */

namespace App\Modules\Billing\Services;

use TCPDF;

class InvoicePdfService
{
    public function render(array $invoice, array $items, array $payments): string
    {
        $pdf = new TCPDF();
        $pdf->SetCreator('Shalom Dental');
        $pdf->SetAuthor('Shalom Dental');
        $pdf->SetTitle('Factura #' . ($invoice['id'] ?? ''));
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $html = '<h1>Factura #' . htmlspecialchars((string) ($invoice['id'] ?? ''), ENT_QUOTES, 'UTF-8') . '</h1>';
        $html .= '<p><strong>Comprador:</strong> ' . htmlspecialchars((string) ($invoice['buyer_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<p><strong>Fecha emision:</strong> ' . htmlspecialchars((string) ($invoice['issue_date'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<p><strong>Fecha vencimiento:</strong> ' . htmlspecialchars((string) ($invoice['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<hr>';

        $html .= '<h3>Items</h3>';
        $html .= '<table border="1" cellpadding="4">
            <thead><tr><th>Descripcion</th><th>Cantidad</th><th>Precio</th><th>Descuento</th><th>IVA%</th><th>Total</th></tr></thead><tbody>';
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($item['quantity'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . number_format((float) ($item['unit_price'] ?? 0), 2) . '</td>';
            $html .= '<td>' . number_format((float) ($item['discount_amount'] ?? 0), 2) . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($item['tax_percentage'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . number_format((float) ($item['total'] ?? 0), 2) . '</td>';
            $html .= '</tr>';
        }
        if (empty($items)) {
            $html .= '<tr><td colspan="6">Sin items</td></tr>';
        }
        $html .= '</tbody></table>';

        $html .= '<h3>Pagos</h3>';
        $html .= '<table border="1" cellpadding="4">
            <thead><tr><th>Metodo</th><th>Monto</th><th>Referencia</th></tr></thead><tbody>';
        foreach ($payments as $payment) {
            $method = $payment['payment_method_name'] ?? $payment['payment_method_code'] ?? '';
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars((string) $method, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . number_format((float) ($payment['amount'] ?? 0), 2) . '</td>';
            $html .= '<td>' . htmlspecialchars((string) ($payment['reference_number'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        if (empty($payments)) {
            $html .= '<tr><td colspan="3">Sin pagos</td></tr>';
        }
        $html .= '</tbody></table>';

        $subtotalNoTax = (float) ($invoice['subtotal_no_tax'] ?? $invoice['subtotal'] ?? 0);
        $totalTax = (float) ($invoice['total_tax'] ?? 0);
        $totalIce = (float) ($invoice['total_ice'] ?? 0);
        $tip = (float) ($invoice['tip'] ?? 0);
        $totalWithTax = $subtotalNoTax + $totalTax + $totalIce;
        $total = (float) ($invoice['total'] ?? 0);

        $html .= '<h3>Resumen</h3>';
        $html .= '<table border="1" cellpadding="4"><tbody>';
        $html .= '<tr><td>Subtotal sin impuestos</td><td>' . number_format($subtotalNoTax, 2) . '</td></tr>';
        $html .= '<tr><td>Subtotal IVA 0%</td><td>' . number_format((float) ($invoice['subtotal_0'] ?? 0), 2) . '</td></tr>';
        $html .= '<tr><td>Subtotal IVA 12%</td><td>' . number_format((float) ($invoice['subtotal_12'] ?? 0), 2) . '</td></tr>';
        $html .= '<tr><td>Subtotal IVA 15%</td><td>' . number_format((float) ($invoice['subtotal_15'] ?? 0), 2) . '</td></tr>';
        $html .= '<tr><td>Total descuento</td><td>' . number_format((float) ($invoice['total_discount'] ?? 0), 2) . '</td></tr>';
        $html .= '<tr><td>Total IVA</td><td>' . number_format($totalTax, 2) . '</td></tr>';
        $html .= '<tr><td>Total ICE</td><td>' . number_format($totalIce, 2) . '</td></tr>';
        $html .= '<tr><td>Propina</td><td>' . number_format($tip, 2) . '</td></tr>';
        $html .= '<tr><td>Total con impuestos</td><td>' . number_format($totalWithTax, 2) . '</td></tr>';
        $html .= '<tr><td>Total con impuestos + propina</td><td>' . number_format($total, 2) . '</td></tr>';
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }
}
