<?php

namespace App\Modules\Clinical\Repositories;

use App\Core\Database;

class PeriodontalChartRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Find periodontal chart by patient ID and date
     */
    public function findByPatientAndDate(int $patientId, string $date): ?array
    {
        $sql = "SELECT * FROM periodontal_charts 
                WHERE patient_id = ? AND chart_date = ? 
                LIMIT 1";
        
        $result = $this->db->selectOne($sql, [$patientId, $date]);
        return $result ?: null;
    }

    /**
     * Create new periodontal chart
     */
    public function create(array $data): array
    {
        $insertData = [
            'patient_id' => $data['patient_id'],
            'chart_date' => $data['chart_date'],
            'chart_data' => is_string($data['chart_data']) ? $data['chart_data'] : json_encode($data['chart_data']),
            'notes' => $data['notes'] ?? '',
            'created_by' => $data['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->insert('periodontal_charts', $insertData);

        return $this->findById($id);
    }

    /**
     * Update existing periodontal chart
     */
    public function update(array $data): array
    {
        $updateData = [
            'chart_data' => is_string($data['chart_data']) ? $data['chart_data'] : json_encode($data['chart_data']),
            'notes' => $data['notes'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->update('periodontal_charts', $updateData, 'id = ?', [$data['id']]);

        return $this->findById($data['id']);
    }

    /**
     * Find periodontal chart by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM periodontal_charts WHERE id = ? LIMIT 1";
        $result = $this->db->selectOne($sql, [$id]);
        return $result ?: null;
    }

    /**
     * Get previous charts for a patient
     */
    public function getPreviousCharts(int $patientId, string $currentDate, int $limit = 5): array
    {
        $sql = "SELECT * FROM periodontal_charts 
                WHERE patient_id = ? AND chart_date < ? 
                ORDER BY chart_date DESC 
                LIMIT ?";
        
        return $this->db->select($sql, [$patientId, $currentDate, $limit]);
    }

    /**
     * Get patient's chart history
     */
    public function getPatientHistory(int $patientId, int $limit = 10): array
    {
        $sql = "SELECT * FROM periodontal_charts 
                WHERE patient_id = ? 
                ORDER BY chart_date DESC 
                LIMIT ?";
        
        return $this->db->select($sql, [$patientId, $limit]);
    }

    /**
     * Get charts within date range
     */
    public function getChartsByDateRange(int $patientId, string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM periodontal_charts 
                WHERE patient_id = ? AND chart_date BETWEEN ? AND ? 
                ORDER BY chart_date ASC";
        
        return $this->db->select($sql, [$patientId, $startDate, $endDate]);
    }

    /**
     * Get latest chart for patient
     */
    public function getLatestChart(int $patientId): ?array
    {
        $sql = "SELECT * FROM periodontal_charts 
                WHERE patient_id = ? 
                ORDER BY chart_date DESC 
                LIMIT 1";
        
        $result = $this->db->selectOne($sql, [$patientId]);
        return $result ?: null;
    }

    /**
     * Delete periodontal chart
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM periodontal_charts WHERE id = ?";
        $this->db->query($sql, [$id]);
        return $id;
    }

    /**
     * Get charts with alerts (high risk indicators)
     */
    public function getChartsWithAlerts(int $limit = 20): array
    {
        $sql = "SELECT pc.*, p.first_name, p.last_name 
                FROM periodontal_charts pc
                JOIN patients p ON pc.patient_id = p.id
                WHERE JSON_EXTRACT(pc.chart_data, '$.alerts') IS NOT NULL
                ORDER BY pc.updated_at DESC
                LIMIT ?";
        
        return $this->db->select($sql, [$limit]);
    }

    /**
     * Search charts by criteria
     */
    public function search(array $criteria): array
    {
        $sql = "SELECT pc.*, p.first_name, p.last_name, p.id_number 
                FROM periodontal_charts pc
                JOIN patients p ON pc.patient_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($criteria['patient_id'])) {
            $sql .= " AND pc.patient_id = ?";
            $params[] = $criteria['patient_id'];
        }
        
        if (!empty($criteria['date_from'])) {
            $sql .= " AND pc.chart_date >= ?";
            $params[] = $criteria['date_from'];
        }
        
        if (!empty($criteria['date_to'])) {
            $sql .= " AND pc.chart_date <= ?";
            $params[] = $criteria['date_to'];
        }
        
        if (!empty($criteria['search'])) {
            $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.id_number LIKE ?)";
            $searchTerm = '%' . $criteria['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY pc.chart_date DESC";
        
        if (!empty($criteria['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$criteria['limit'];
        }
        
        return $this->db->select($sql, $params);
    }

    /**
     * Get statistics for a patient
     */
    public function getPatientStatistics(int $patientId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_charts,
                    MIN(chart_date) as first_chart_date,
                    MAX(chart_date) as last_chart_date,
                    AVG(JSON_EXTRACT(chart_data, '$.average_probing')) as avg_probing
                FROM periodontal_charts 
                WHERE patient_id = ?";
        
        $result = $this->db->selectOne($sql, [$patientId]);
        return $result ?: [];
    }

    /**
     * Check if patient has any charts
     */
    public function hasCharts(int $patientId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM periodontal_charts WHERE patient_id = ?";
        $result = $this->db->selectOne($sql, [$patientId]);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get charts needing follow-up
     */
    public function getChartsNeedingFollowUp(int $daysThreshold = 90): array
    {
        $sql = "SELECT pc.*, p.first_name, p.last_name 
                FROM periodontal_charts pc
                JOIN patients p ON pc.patient_id = p.id
                WHERE pc.chart_date <= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                AND JSON_EXTRACT(pc.chart_data, '$.treatment_urgency') IN ('urgent', 'immediate')
                ORDER BY pc.chart_date ASC
                LIMIT 50";
        
        return $this->db->select($sql, [$daysThreshold]);
    }

    /**
     * Update chart metadata (risk assessment, alerts, etc.)
     */
    public function updateMetadata(int $id, array $metadata): bool
    {
        $chartDataSql = "UPDATE periodontal_charts 
                         SET chart_data = JSON_SET(
                             chart_data, 
                             '$.metadata', 
                             JSON_OBJECT(
                                 'risk_assessment', ?, 
                                 'alerts', ?,
                                 'last_updated', ?
                             )
                         ),
                         updated_at = ?
                         WHERE id = ?";
        
        $params = [
            json_encode($metadata['risk_assessment'] ?? []),
            json_encode($metadata['alerts'] ?? []),
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            $id
        ];
        
        $this->db->query($chartDataSql, $params);
        return true;
    }

    /**
     * Export charts data
     */
    public function exportCharts(array $criteria = []): array
    {
        $sql = "SELECT pc.*, 
                       p.first_name, p.last_name, p.id_number, p.birth_date,
                       u.first_name as professional_first_name, u.last_name as professional_last_name
                FROM periodontal_charts pc
                JOIN patients p ON pc.patient_id = p.id
                LEFT JOIN users u ON pc.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($criteria['patient_id'])) {
            $sql .= " AND pc.patient_id = ?";
            $params[] = $criteria['patient_id'];
        }
        
        if (!empty($criteria['date_from'])) {
            $sql .= " AND pc.chart_date >= ?";
            $params[] = $criteria['date_from'];
        }
        
        if (!empty($criteria['date_to'])) {
            $sql .= " AND pc.chart_date <= ?";
            $params[] = $criteria['date_to'];
        }
        
        $sql .= " ORDER BY pc.chart_date DESC, pc.patient_id ASC";
        
        return $this->db->select($sql, $params);
    }

    /**
     * Get chart trends over time
     */
    public function getTrends(int $patientId, int $months = 12): array
    {
        $sql = "SELECT chart_date, chart_data 
                FROM periodontal_charts 
                WHERE patient_id = ? 
                AND chart_date >= DATE_SUB(CURRENT_DATE, INTERVAL ? MONTH)
                ORDER BY chart_date ASC";
        
        return $this->db->select($sql, [$patientId, $months]);
    }

    /**
     * Bulk update charts
     */
    public function bulkUpdate(array $updates): bool
    {
        if (empty($updates)) {
            return true;
        }

        $this->db->beginTransaction();
        
        try {
            foreach ($updates as $update) {
                $sql = "UPDATE periodontal_charts 
                        SET chart_data = ?, notes = ?, updated_at = ? 
                        WHERE id = ?";
                
                $params = [
                    $update['chart_data'],
                    $update['notes'] ?? '',
                    date('Y-m-d H:i:s'),
                    $update['id']
                ];
                
        $this->db->query($sql, $params);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get patients with active periodontal issues
     */
    public function getPatientsWithActiveIssues(): array
    {
        $sql = "SELECT DISTINCT pc.patient_id, p.first_name, p.last_name, p.id_number,
                       MAX(pc.chart_date) as last_chart_date,
                       JSON_EXTRACT(pc.chart_data, '$.risk_assessment.risk_level') as risk_level
                FROM periodontal_charts pc
                JOIN patients p ON pc.patient_id = p.id
                WHERE JSON_EXTRACT(pc.chart_data, '$.risk_assessment.risk_level') IN ('moderate', 'severe')
                GROUP BY pc.patient_id, p.first_name, p.last_name, p.id_number
                HAVING last_chart_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                ORDER BY risk_level DESC, last_chart_date DESC";
        
        return $this->db->select($sql);
    }

    /**
     * Count total charts
     */
    public function count(array $criteria = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM periodontal_charts WHERE 1=1";
        $params = [];
        
        if (!empty($criteria['patient_id'])) {
            $sql .= " AND patient_id = ?";
            $params[] = $criteria['patient_id'];
        }
        
        if (!empty($criteria['date_from'])) {
            $sql .= " AND chart_date >= ?";
            $params[] = $criteria['date_from'];
        }
        
        if (!empty($criteria['date_to'])) {
            $sql .= " AND chart_date <= ?";
            $params[] = $criteria['date_to'];
        }
        
        $result = $this->db->selectOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }
}