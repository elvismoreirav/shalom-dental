<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request): Response
    {
        $user = user();
        $locationId = (int) session('current_location_id', 0);

        $stats = $this->getStats($locationId);
        $weeklyAppointments = $this->getWeeklyAppointments($locationId);
        $monthlyBilling = $this->getMonthlyBilling($locationId);

        return Response::view('dashboard.index', [
            'title' => 'Dashboard - Shalom Dental',
            'user' => $user,
            'stats' => $stats,
            'weeklyAppointments' => $weeklyAppointments,
            'monthlyBilling' => $monthlyBilling,
        ]);
    }

    private function getStats(int $locationId): array
    {
        $appointmentsToday = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM appointments WHERE scheduled_date = CURDATE()" .
            ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$locationId] : []
        );

        $waitingRoom = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM appointments WHERE scheduled_date = CURDATE() AND status IN ('checked_in','in_progress')" .
            ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$locationId] : []
        );

        $organizationId = (int) session('organization_id', 0);

        $totalPatients = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM patients" . ($organizationId > 0 ? " WHERE organization_id = ?" : ""),
            $organizationId > 0 ? [$organizationId] : []
        );

        $billingMonth = $this->db->selectOne(
            "SELECT total_amount FROM v_monthly_billing WHERE month_year = DATE_FORMAT(CURDATE(), '%Y-%m')" .
            ($locationId > 0 ? " AND location_id = ?" : ""),
            $locationId > 0 ? [$locationId] : []
        );

        return [
            'appointments_today' => (int) ($appointmentsToday['total'] ?? 0),
            'waiting_room' => (int) ($waitingRoom['total'] ?? 0),
            'total_patients' => (int) ($totalPatients['total'] ?? 0),
            'billing_month' => (float) ($billingMonth['total_amount'] ?? 0),
        ];
    }

    private function getWeeklyAppointments(int $locationId): array
    {
        $sql = "SELECT scheduled_date as day, COUNT(*) as total
                FROM appointments
                WHERE scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
        $params = [];

        if ($locationId > 0) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " GROUP BY scheduled_date ORDER BY scheduled_date ASC";

        $rows = $this->db->select($sql, $params);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['day']] = (int) $row['total'];
        }

        $result = [];
        $current = new \DateTimeImmutable();
        for ($i = 6; $i >= 0; $i--) {
            $day = $current->sub(new \DateInterval('P' . $i . 'D'))->format('Y-m-d');
            $result[] = [
                'day' => $day,
                'total' => $map[$day] ?? 0,
            ];
        }

        return $result;
    }

    private function getMonthlyBilling(int $locationId): array
    {
        $sql = "SELECT month_year, total_amount
                FROM v_monthly_billing
                WHERE month_year >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m')";
        $params = [];

        if ($locationId > 0) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY month_year ASC";

        $rows = $this->db->select($sql, $params);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['month_year']] = (float) $row['total_amount'];
        }

        $result = [];
        $current = new \DateTimeImmutable('first day of this month');
        for ($i = 5; $i >= 0; $i--) {
            $month = $current->sub(new \DateInterval('P' . $i . 'M'))->format('Y-m');
            $result[] = [
                'month' => $month,
                'total' => $map[$month] ?? 0.0,
            ];
        }

        return $result;
    }
}
