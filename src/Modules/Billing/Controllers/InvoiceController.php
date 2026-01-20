<?php
/**
 * =========================================================================
 * SHALOM DENTAL - Invoice Controller (MVP)
 * =========================================================================
 */

namespace App\Modules\Billing\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Modules\Billing\Services\InvoicePdfService;

class InvoiceController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $locationId = (int) session('current_location_id', 0);
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $sqlBase = "FROM invoices i
                LEFT JOIN patients p ON p.id = i.patient_id
                WHERE 1=1";
        $params = [];

        if ($locationId > 0) {
            $sqlBase .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        if ($query !== '') {
            $sqlBase .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR i.buyer_name LIKE ?)";
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like);
        }

        if ($status !== '') {
            $sqlBase .= " AND i.status = ?";
            $params[] = $status;
        }

        if ($dateFrom !== '') {
            $sqlBase .= " AND i.issue_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $sqlBase .= " AND i.issue_date <= ?";
            $params[] = $dateTo;
        }

        $count = $this->db->selectOne("SELECT COUNT(*) as total " . $sqlBase, $params);
        $total = (int) ($count['total'] ?? 0);
        $sumRow = $this->db->selectOne("SELECT SUM(i.total) as total_amount " . $sqlBase, $params);
        $totalAmount = (float) ($sumRow['total_amount'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT i.id, i.issue_date, i.total, i.status, p.first_name, p.last_name
                " . $sqlBase .
                " ORDER BY i.issue_date DESC, i.id DESC LIMIT {$perPage} OFFSET {$offset}";

        $invoices = $this->db->select($sql, $params);

        return Response::view('billing.invoices.index', [
            'title' => 'Facturas',
            'invoices' => $invoices,
            'query' => $query,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function create(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);

        $patients = $this->db->select(
            "SELECT id, first_name, last_name, id_number, email FROM patients" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY last_name, first_name LIMIT 200",
            $organizationId > 0 ? [$organizationId] : []
        );

        $appointmentTypes = $this->db->select(
            "SELECT id, name, price_default, code, tax_percentage FROM appointment_types" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY name",
            $organizationId > 0 ? [$organizationId] : []
        );

        $emissionPoints = $this->db->select(
            "SELECT id, code FROM emission_points WHERE location_id = ? ORDER BY code",
            [$locationId]
        );

        return Response::view('billing.invoices.create', [
            'title' => 'Crear Factura',
            'patients' => $patients,
            'appointmentTypes' => $appointmentTypes,
            'emissionPoints' => $emissionPoints,
        ]);
    }

    public function store(Request $request): Response
    {
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);
        $userId = (int) (user()['id'] ?? 0);
        $isAjax = $request->isAjax();

        $patientId = (int) $request->input('patient_id');
        $emissionPointId = (int) $request->input('emission_point_id');
        $issueDate = $request->input('issue_date', date('Y-m-d'));
        $buyerName = trim((string) $request->input('buyer_name'));
        $buyerIdType = $request->input('buyer_id_type', '05');
        $buyerIdNumber = trim((string) $request->input('buyer_id_number'));

        if ($organizationId <= 0 || $locationId <= 0 || $emissionPointId <= 0 || $buyerName === '' || $buyerIdNumber === '') {
            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'message' => 'Complete los campos requeridos.',
                    'errors' => ['general' => 'Complete los campos requeridos.'],
                ], 422);
            }
            session()->setFlash('error', 'Complete los campos requeridos.');
            return Response::redirect('/billing/invoices/create');
        }

        $emissionPoint = $this->db->selectOne(
            "SELECT ep.code, l.sri_establishment_code FROM emission_points ep JOIN locations l ON l.id = ep.location_id WHERE ep.id = ?",
            [$emissionPointId]
        );

        if (!$emissionPoint) {
            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'message' => 'Punto de emision invalido.',
                ], 422);
            }
            session()->setFlash('error', 'Punto de emision invalido.');
            return Response::redirect('/billing/invoices/create');
        }

        $seqRow = $this->db->selectOne(
            "SELECT current_sequential FROM invoice_sequentials WHERE emission_point_id = ? AND document_type = '01'",
            [$emissionPointId]
        );
        $sequential = (int) (($seqRow['current_sequential'] ?? 0) + 1);

        $items = $request->input('items', []);
        $additionalInfo = $this->normalizeAdditionalInfo(
            $request->input('additional_info', []),
            trim((string) $request->input('remission_guide', ''))
        );
        $invoiceDiscount = (float) $request->input('invoice_discount', 0);
        $tip = (float) $request->input('tip', 0);
        $totalIce = (float) $request->input('total_ice', 0);
        [$totals, $computedItems, $validItems] = $this->calculateTotals($items, $invoiceDiscount, $tip, $totalIce);

        if ($validItems === 0) {
            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'message' => 'Agregue al menos un item valido.',
                ], 422);
            }
            session()->setFlash('error', 'Agregue al menos un item valido.');
            return Response::redirect('/billing/invoices/create');
        }

        $action = $request->input('action', 'draft');

        $invoiceId = $this->db->insert('invoices', [
            'organization_id' => $organizationId,
            'location_id' => $locationId,
            'emission_point_id' => $emissionPointId,
            'patient_id' => $patientId ?: null,
            'document_type' => '01',
            'establishment_code' => $emissionPoint['sri_establishment_code'],
            'emission_point_code' => $emissionPoint['code'],
            'sequential' => $sequential,
            'issue_date' => $issueDate,
            'issue_time' => date('H:i:s'),
            'due_date' => $request->input('due_date') ?: null,
            'buyer_id_type' => $buyerIdType,
            'buyer_id_number' => $buyerIdNumber,
            'buyer_name' => $buyerName,
            'buyer_address' => trim((string) $request->input('buyer_address')) ?: null,
            'buyer_email' => trim((string) $request->input('buyer_email')) ?: null,
            'buyer_phone' => trim((string) $request->input('buyer_phone')) ?: null,
            'subtotal_no_tax' => $totals['subtotal_no_tax'],
            'subtotal_0' => $totals['subtotal_0'],
            'subtotal_12' => $totals['subtotal_12'],
            'subtotal_15' => $totals['subtotal_15'],
            'total_discount' => $totals['total_discount'],
            'subtotal' => $totals['subtotal_no_tax'],
            'total_tax' => $totals['total_tax'],
            'tip' => $totals['tip'],
            'total_ice' => $totals['total_ice'],
            'total' => $totals['total'],
            'status' => 'draft',
            'additional_info' => $additionalInfo ? json_encode($additionalInfo, JSON_UNESCAPED_UNICODE) : null,
            'created_by_user_id' => $userId ?: 1,
        ]);

        $this->db->update('invoice_sequentials', [
            'current_sequential' => $sequential,
            'last_used_at' => date('Y-m-d H:i:s'),
        ], 'emission_point_id = ? AND document_type = ?', [$emissionPointId, '01']);

        $this->persistItems($invoiceId, $computedItems);
        $this->persistPayments($invoiceId, $request->input('payments', []));

        $message = $action === 'emit' ? 'Factura emitida correctamente.' : 'Borrador guardado correctamente.';

        if ($isAjax) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'invoice_id' => $invoiceId,
                'action' => $action,
            ]);
        }

        session()->setFlash('success', $message);
        return Response::redirect('/billing/invoices');
    }

    public function show(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $invoice = $this->db->selectOne(
            "SELECT * FROM invoices WHERE id = ?",
            [$invoiceId]
        );

        if (!$invoice) {
            return Response::notFound('Factura no encontrada');
        }

        $items = $this->db->select(
            "SELECT description, quantity, unit_price, discount_amount, tax_percentage, subtotal, total
             FROM invoice_items WHERE invoice_id = ? ORDER BY sequence ASC",
            [$invoiceId]
        );
        $payments = $this->db->select(
            "SELECT payment_method_code, payment_method_name, amount, reference_number, term_days, time_unit
             FROM invoice_payments WHERE invoice_id = ? ORDER BY id ASC",
            [$invoiceId]
        );

        return Response::view('billing.invoices.show', [
            'title' => 'Factura',
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $invoice = $this->db->selectOne("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) {
            return Response::notFound('Factura no encontrada');
        }

        $items = $this->db->select(
            "SELECT description, quantity, unit_price, discount_amount, tax_percentage, total
             FROM invoice_items WHERE invoice_id = ? ORDER BY sequence ASC",
            [$invoiceId]
        );
        $payments = $this->db->select(
            "SELECT payment_method_code, payment_method_name, amount, reference_number, term_days, time_unit
             FROM invoice_payments WHERE invoice_id = ? ORDER BY id ASC",
            [$invoiceId]
        );

        $pdf = (new InvoicePdfService())->render($invoice, $items, $payments);
        $filename = 'invoice_' . $invoiceId . '.pdf';

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=\"' . $filename . '\"',
        ]);
    }
    public function edit(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $organizationId = (int) session('organization_id', 0);
        $locationId = (int) session('current_location_id', 0);

        $invoice = $this->db->selectOne(
            "SELECT * FROM invoices WHERE id = ?" . ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$invoiceId, $locationId] : [$invoiceId]
        );

        if (!$invoice) {
            return Response::notFound('Factura no encontrada');
        }

        $patients = $this->db->select(
            "SELECT id, first_name, last_name, id_number, email FROM patients" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY last_name, first_name LIMIT 200",
            $organizationId > 0 ? [$organizationId] : []
        );

        $appointmentTypes = $this->db->select(
            "SELECT id, name, price_default, code, tax_percentage FROM appointment_types" .
            ($organizationId > 0 ? " WHERE organization_id = ?" : "") .
            " ORDER BY name",
            $organizationId > 0 ? [$organizationId] : []
        );

        $emissionPoints = $this->db->select(
            "SELECT id, code FROM emission_points WHERE location_id = ? ORDER BY code",
            [$locationId]
        );

        $items = $this->db->select(
            "SELECT description, quantity, unit_price, tax_percentage, main_code, discount_amount, appointment_type_id
             FROM invoice_items WHERE invoice_id = ? ORDER BY sequence ASC",
            [$invoiceId]
        );

        $payments = $this->db->select(
            "SELECT payment_method_code, payment_method_name, amount, reference_number, term_days, time_unit
             FROM invoice_payments WHERE invoice_id = ? ORDER BY id ASC",
            [$invoiceId]
        );

        return Response::view('billing.invoices.edit', [
            'title' => 'Editar Factura',
            'invoice' => $invoice,
            'patients' => $patients,
            'appointmentTypes' => $appointmentTypes,
            'emissionPoints' => $emissionPoints,
            'items' => $items,
            'payments' => $payments,
        ]);
    }

    public function update(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $locationId = (int) session('current_location_id', 0);
        $isAjax = $request->isAjax();

        $invoice = $this->db->selectOne(
            "SELECT id FROM invoices WHERE id = ?" . ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$invoiceId, $locationId] : [$invoiceId]
        );
        if (!$invoice) {
            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'message' => 'Factura no encontrada.',
                ], 404);
            }
            return Response::notFound('Factura no encontrada');
        }

        $items = $request->input('items', []);
        $additionalInfo = $this->normalizeAdditionalInfo(
            $request->input('additional_info', []),
            trim((string) $request->input('remission_guide', ''))
        );
        $invoiceDiscount = (float) $request->input('invoice_discount', 0);
        $tip = (float) $request->input('tip', 0);
        $totalIce = (float) $request->input('total_ice', 0);
        [$totals, $computedItems, $validItems] = $this->calculateTotals($items, $invoiceDiscount, $tip, $totalIce);

        if ($validItems === 0) {
            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'message' => 'Agregue al menos un item valido.',
                ], 422);
            }
            session()->setFlash('error', 'Agregue al menos un item valido.');
            return Response::redirect('/billing/invoices/' . $invoiceId . '/edit');
        }

        $action = $request->input('action', 'draft');

        $this->db->update('invoices', [
            'patient_id' => (int) $request->input('patient_id') ?: null,
            'issue_date' => $request->input('issue_date', date('Y-m-d')),
            'due_date' => $request->input('due_date') ?: null,
            'buyer_name' => trim((string) $request->input('buyer_name')),
            'buyer_id_type' => $request->input('buyer_id_type', '05'),
            'buyer_id_number' => trim((string) $request->input('buyer_id_number')),
            'buyer_address' => trim((string) $request->input('buyer_address')) ?: null,
            'buyer_email' => trim((string) $request->input('buyer_email')) ?: null,
            'buyer_phone' => trim((string) $request->input('buyer_phone')) ?: null,
            'subtotal_no_tax' => $totals['subtotal_no_tax'],
            'subtotal_0' => $totals['subtotal_0'],
            'subtotal_12' => $totals['subtotal_12'],
            'subtotal_15' => $totals['subtotal_15'],
            'total_discount' => $totals['total_discount'],
            'subtotal' => $totals['subtotal_no_tax'],
            'total_tax' => $totals['total_tax'],
            'tip' => $totals['tip'],
            'total_ice' => $totals['total_ice'],
            'total' => $totals['total'],
            'additional_info' => $additionalInfo ? json_encode($additionalInfo, JSON_UNESCAPED_UNICODE) : null,
        ], 'id = ?', [$invoiceId]);

        $this->persistItems($invoiceId, $computedItems);
        $this->persistPayments($invoiceId, $request->input('payments', []));

        $message = $action === 'emit' ? 'Factura actualizada y emitida correctamente.' : 'Borrador actualizado correctamente.';

        if ($isAjax) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'invoice_id' => $invoiceId,
                'action' => $action,
            ]);
        }

        session()->setFlash('success', $message);
        return Response::redirect('/billing/invoices/' . $invoiceId);
    }

    public function void(Request $request): Response
    {
        $invoiceId = (int) $request->param('id');
        $locationId = (int) session('current_location_id', 0);
        $invoice = $this->db->selectOne(
            "SELECT id FROM invoices WHERE id = ?" . ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$invoiceId, $locationId] : [$invoiceId]
        );
        if (!$invoice) {
            return Response::notFound('Factura no encontrada');
        }

        $userId = (int) (user()['id'] ?? 0);
        $this->db->update('invoices', [
            'status' => 'voided',
            'voided_at' => date('Y-m-d H:i:s'),
            'voided_by_user_id' => $userId ?: null,
        ], 'id = ?', [$invoiceId]);

        session()->setFlash('success', 'Factura anulada.');
        return Response::redirect('/billing/invoices/' . $invoiceId);
    }

    private function calculateTotals(array $items, float $invoiceDiscount = 0.0, float $tip = 0.0, float $totalIce = 0.0): array
    {
        $validItems = 0;
        $lines = [];
        $totalNet = 0.0;
        $itemDiscountTotal = 0.0;

        foreach ($items as $item) {
            $desc = trim((string) ($item['description'] ?? ''));
            $qty = (float) ($item['quantity'] ?? 0);
            $unit = (float) ($item['unit_price'] ?? 0);
            if ($desc === '' || $qty <= 0 || $unit < 0) {
                continue;
            }

            $lineSubtotal = $qty * $unit;
            $lineDiscount = (float) ($item['discount_amount'] ?? 0);
            if ($lineDiscount < 0) {
                $lineDiscount = 0.0;
            }
            if ($lineDiscount > $lineSubtotal) {
                $lineDiscount = $lineSubtotal;
            }
            $lineNet = $lineSubtotal - $lineDiscount;
            $taxPct = (float) ($item['tax_percentage'] ?? 0);

            $lines[] = [
                'description' => $desc,
                'quantity' => $qty,
                'unit_price' => $unit,
                'discount_amount' => $lineDiscount,
                'tax_percentage' => $taxPct,
                'main_code' => $item['main_code'] ?? 'SERV',
                'aux_code' => $item['aux_code'] ?? null,
                'appointment_type_id' => (int) ($item['appointment_type_id'] ?? 0) ?: null,
                'line_subtotal' => $lineSubtotal,
                'line_net' => $lineNet,
            ];

            $totalNet += $lineNet;
            $itemDiscountTotal += $lineDiscount;
            $validItems++;
        }

        $invoiceDiscount = max(0.0, $invoiceDiscount);
        if ($invoiceDiscount > $totalNet) {
            $invoiceDiscount = $totalNet;
        }

        $subtotalNoTax = 0.0;
        $subtotal0 = 0.0;
        $subtotal12 = 0.0;
        $subtotal15 = 0.0;
        $totalTax = 0.0;

        $computedLines = [];
        foreach ($lines as $line) {
            $share = $totalNet > 0 ? ($line['line_net'] / $totalNet) * $invoiceDiscount : 0.0;
            $netAfter = max(0.0, $line['line_net'] - $share);
            $taxPct = (float) $line['tax_percentage'];
            $taxAmount = $netAfter * ($taxPct / 100);
            $lineTotal = $netAfter + $taxAmount;
            $lineDiscountTotal = $line['discount_amount'] + $share;

            $subtotalNoTax += $netAfter;
            if ($taxPct === 0.0) {
                $subtotal0 += $netAfter;
            } elseif ($taxPct === 12.0) {
                $subtotal12 += $netAfter;
            } elseif ($taxPct === 15.0) {
                $subtotal15 += $netAfter;
            }
            $totalTax += $taxAmount;

            $computedLines[] = [
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_amount' => $lineDiscountTotal,
                'subtotal' => $netAfter,
                'tax_percentage' => $taxPct,
                'tax_amount' => $taxAmount,
                'total' => $lineTotal,
                'main_code' => $line['main_code'],
                'aux_code' => $line['aux_code'],
                'appointment_type_id' => $line['appointment_type_id'],
            ];
        }

        $totalDiscount = $itemDiscountTotal + $invoiceDiscount;
        $totalWithTax = $subtotalNoTax + $totalTax + $totalIce;
        $total = $totalWithTax + $tip;

        return [
            [
                'subtotal_no_tax' => $subtotalNoTax,
                'subtotal_0' => $subtotal0,
                'subtotal_12' => $subtotal12,
                'subtotal_15' => $subtotal15,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'total_ice' => $totalIce,
                'tip' => $tip,
                'total' => $total,
            ],
            $computedLines,
            $validItems,
        ];
    }

    private function persistItems(int $invoiceId, array $items): void
    {
        $this->db->delete('invoice_items', 'invoice_id = ?', [$invoiceId]);
        $sequence = 1;
        foreach ($items as $item) {
            $taxPct = (float) ($item['tax_percentage'] ?? 0);
            $taxRateCode = match ($taxPct) {
                0.0 => '0',
                12.0 => '2',
                15.0 => '4',
                default => '4',
            };
            $this->db->insert('invoice_items', [
                'invoice_id' => $invoiceId,
                'sequence' => $sequence++,
                'main_code' => $item['main_code'] ?? 'SERV',
                'aux_code' => $item['aux_code'] ?? null,
                'description' => $item['description'] ?? '',
                'quantity' => (float) ($item['quantity'] ?? 0),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'discount_amount' => (float) ($item['discount_amount'] ?? 0),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
                'tax_code' => '2',
                'tax_percentage' => $taxPct,
                'tax_rate_code' => $taxRateCode,
                'tax_amount' => (float) ($item['tax_amount'] ?? 0),
                'total' => (float) ($item['total'] ?? 0),
                'appointment_type_id' => $item['appointment_type_id'] ?: null,
            ]);
        }
    }

    private function persistPayments(int $invoiceId, array $payments): void
    {
        $this->db->delete('invoice_payments', 'invoice_id = ?', [$invoiceId]);
        foreach ($payments as $payment) {
            $amount = (float) ($payment['amount'] ?? 0);
            $method = trim((string) ($payment['payment_method_code'] ?? ''));
            if ($amount <= 0 || $method === '') {
                continue;
            }
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
    }

    private function normalizeAdditionalInfo(array $additionalInfo, string $remissionGuide): array
    {
        $normalized = [];
        foreach ($additionalInfo as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($name === '' || $value === '') {
                continue;
            }
            $normalized[] = ['name' => $name, 'value' => $value];
        }
        if ($remissionGuide !== '') {
            $normalized[] = ['name' => 'Guia de Remision', 'value' => $remissionGuide];
        }
        return $normalized;
    }
}
