<?php

namespace App\Modules\Clinical\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Modules\Clinical\Repositories\PeriodontalChartRepository;

class PeriodontalChartController
{
    private Database $db;
    private PeriodontalChartRepository $repository;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->repository = new PeriodontalChartRepository($this->db);
    }

    /**
     * Get periodontal chart for a specific patient and date
     */
    public function getChart(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $date = $request->query('date', date('Y-m-d'));

        if (!$patientId) {
            return Response::error('Patient ID is required', 400);
        }

        // Verify patient exists
        $patient = $this->db->selectOne('SELECT * FROM patients WHERE id = ?', [$patientId]);
        if (!$patient) {
            return Response::error('Patient not found', 404);
        }

        // Get chart data
        $chart = $this->repository->findByPatientAndDate($patientId, $date);

        if (!$chart) {
            // Create new chart with default values
            $chart = $this->createDefaultChart($patientId, $date);
        }

        return Response::success([
            'chart' => $chart,
            'patient' => $patient,
            'previous_charts' => $this->repository->getPreviousCharts($patientId, $date)
        ]);
    }

    /**
     * Save or update periodontal chart
     */
    public function saveChart(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $date = $request->input('date', date('Y-m-d'));
        $chartData = $request->input('chart_data', []);
        $notes = $request->input('notes', '');

        if (!$patientId) {
            return Response::error('Patient ID is required', 400);
        }

        if (empty($chartData)) {
            return Response::error('Chart data is required', 400);
        }

        // Validate chart data structure
        $validationResult = $this->validateChartData($chartData);
        if (!$validationResult['valid']) {
            return Response::error($validationResult['message'], 400);
        }

        try {
            // Check if chart exists
            $existingChart = $this->repository->findByPatientAndDate($patientId, $date);

            if ($existingChart) {
                // Update existing chart
                $chartData['id'] = $existingChart['id'];
                $chart = $this->repository->update($chartData);
            } else {
                // Create new chart
                $chartData['patient_id'] = $patientId;
                $chartData['chart_date'] = $date;
                $chartData['notes'] = $notes;
                $chartData['created_by'] = $_SESSION['user_id'] ?? null;
                $chart = $this->repository->create($chartData);
            }

            return Response::success([
                'chart' => $chart,
                'message' => 'Periodontal chart saved successfully'
            ]);

        } catch (\Exception $e) {
            error_log("Error saving periodontal chart: " . $e->getMessage());
            return Response::error('Error saving periodontal chart', 500);
        }
    }

    /**
     * Get periodontal chart history for a patient
     */
    public function getHistory(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $limit = (int) $request->query('limit', 10);

        if (!$patientId) {
            return Response::error('Patient ID is required', 400);
        }

        $charts = $this->repository->getPatientHistory($patientId, $limit);

        // Calculate trends and statistics
        $trends = $this->calculateTrends($charts);

        return Response::success([
            'charts' => $charts,
            'trends' => $trends,
            'statistics' => $this->getOverallStatistics($charts)
        ]);
    }

    /**
     * Compare two periodontal charts
     */
    public function compareCharts(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $date1 = $request->query('date1');
        $date2 = $request->query('date2');

        if (!$patientId || !$date1 || !$date2) {
            return Response::error('Patient ID and both dates are required', 400);
        }

        $chart1 = $this->repository->findByPatientAndDate($patientId, $date1);
        $chart2 = $this->repository->findByPatientAndDate($patientId, $date2);

        if (!$chart1 || !$chart2) {
            return Response::error('One or both charts not found', 404);
        }

        $comparison = $this->performComparison($chart1, $chart2);

        return Response::success([
            'chart1' => $chart1,
            'chart2' => $chart2,
            'comparison' => $comparison
        ]);
    }

    /**
     * Get periodontal chart summary and statistics
     */
    public function getSummary(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $date = $request->query('date', date('Y-m-d'));

        if (!$patientId) {
            return Response::error('Patient ID is required', 400);
        }

        $chart = $this->repository->findByPatientAndDate($patientId, $date);

        if (!$chart) {
            return Response::error('Chart not found for specified date', 404);
        }

        $summary = $this->generateSummary($chart);
        $riskAssessment = $this->performRiskAssessment($chart);

        return Response::success([
            'summary' => $summary,
            'risk_assessment' => $riskAssessment,
            'recommendations' => $this->generateRecommendations($chart, $riskAssessment)
        ]);
    }

    /**
     * Export periodontal chart as PDF
     */
    public function exportPdf(Request $request): Response
    {
        $patientId = (int) $request->param('patientId');
        $date = $request->query('date', date('Y-m-d'));

        if (!$patientId) {
            return Response::error('Patient ID is required', 400);
        }

        $chart = $this->repository->findByPatientAndDate($patientId, $date);
        $patient = $this->db->selectOne('SELECT * FROM patients WHERE id = ?', [$patientId]);

        if (!$chart || !$patient) {
            return Response::error('Chart or patient not found', 404);
        }

        try {
            $pdfPath = $this->generatePdfReport($chart, $patient);

            return Response::success([
                'pdf_path' => $pdfPath,
                'download_url' => "/downloads/periodontal_chart_{$patientId}_{$date}.pdf"
            ]);

        } catch (\Exception $e) {
            error_log("Error generating PDF: " . $e->getMessage());
            return Response::error('Error generating PDF', 500);
        }
    }

    /**
     * Create default chart structure
     */
    private function createDefaultChart(int $patientId, string $date): array
    {
        $defaultData = [];
        
        // Initialize upper arch (teeth 18-11)
        for ($tooth = 18; $tooth >= 11; $tooth--) {
            $defaultData['upper'][$tooth] = [
                'probing' => ['B' => '', 'M' => '', 'L' => ''],
                'bleeding' => ['B' => '', 'M' => '', 'L' => ''],
                'recession' => ['B' => '', 'M' => '', 'L' => ''],
                'mobility' => '',
                'furcation' => ''
            ];
        }

        // Initialize lower arch (teeth 31-46)
        for ($tooth = 31; $tooth <= 46; $tooth++) {
            $defaultData['lower'][$tooth] = [
                'probing' => ['B' => '', 'M' => '', 'L' => ''],
                'bleeding' => ['B' => '', 'M' => '', 'L' => ''],
                'recession' => ['B' => '', 'M' => '', 'L' => ''],
                'mobility' => '',
                'furcation' => ''
            ];
        }

        return [
            'patient_id' => $patientId,
            'chart_date' => $date,
            'chart_data' => json_encode($defaultData),
            'notes' => '',
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Validate chart data structure
     */
    private function validateChartData(array $chartData): array
    {
        $requiredKeys = ['upper', 'lower'];
        
        foreach ($requiredKeys as $key) {
            if (!isset($chartData[$key]) || !is_array($chartData[$key])) {
                return ['valid' => false, 'message' => "Missing or invalid {$key} arch data"];
            }
        }

        // Validate tooth data structure
        $toothFields = ['probing', 'bleeding', 'recession', 'mobility', 'furcation'];
        $sites = ['B', 'M', 'L'];

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $tooth => $toothData) {
                if (!is_array($toothData)) {
                    return ['valid' => false, 'message' => "Invalid data structure for tooth {$tooth}"];
                }

                foreach ($toothFields as $field) {
                    if (!isset($toothData[$field])) {
                        return ['valid' => false, 'message' => "Missing field {$field} for tooth {$tooth}"];
                    }

                    if (in_array($field, ['probing', 'bleeding', 'recession']) && !is_array($toothData[$field])) {
                        return ['valid' => false, 'message' => "Invalid {$field} structure for tooth {$tooth}"];
                    }

                    if (in_array($field, ['probing', 'bleeding', 'recession'])) {
                        foreach ($sites as $site) {
                            if (!isset($toothData[$field][$site])) {
                                return ['valid' => false, 'message' => "Missing site {$site} in {$field} for tooth {$tooth}"];
                            }
                        }
                    }
                }
            }
        }

        return ['valid' => true];
    }

    /**
     * Calculate trends from chart history
     */
    private function calculateTrends(array $charts): array
    {
        if (count($charts) < 2) {
            return ['trend' => 'insufficient_data', 'message' => 'Need at least 2 charts for trend analysis'];
        }

        $latest = $charts[0];
        $previous = $charts[1];

        $latestData = json_decode($latest['chart_data'], true);
        $previousData = json_decode($previous['chart_data'], true);

        $trends = [];
        
        // Calculate probing depth trends
        $latestAvg = $this->calculateAverageProbing($latestData);
        $previousAvg = $this->calculateAverageProbing($previousData);
        
        $trends['probing_trend'] = $latestAvg > $previousAvg ? 'worsening' : ($latestAvg < $previousAvg ? 'improving' : 'stable');
        $trends['probing_change'] = round($latestAvg - $previousAvg, 1);

        // Calculate bleeding trends
        $latestBleeding = $this->countBleedingSites($latestData);
        $previousBleeding = $this->countBleedingSites($previousData);
        
        $trends['bleeding_trend'] = $latestBleeding > $previousBleeding ? 'worsening' : ($latestBleeding < $previousBleeding ? 'improving' : 'stable');
        $trends['bleeding_change'] = $latestBleeding - $previousBleeding;

        return $trends;
    }

    /**
     * Calculate average probing depth
     */
    private function calculateAverageProbing(array $chartData): float
    {
        $total = 0;
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $toothData) {
                foreach (['B', 'M', 'L'] as $site) {
                    $value = $toothData['probing'][$site] ?? '';
                    if (is_numeric($value) && $value > 0) {
                        $total += (float)$value;
                        $count++;
                    }
                }
            }
        }

        return $count > 0 ? $total / $count : 0;
    }

    /**
     * Count bleeding sites
     */
    private function countBleedingSites(array $chartData): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $toothData) {
                foreach (['B', 'M', 'L'] as $site) {
                    $value = $toothData['bleeding'][$site] ?? '';
                    if ($value === '+' || $value === '++') {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Generate chart summary
     */
    private function generateSummary(array $chart): array
    {
        $chartData = json_decode($chart['chart_data'], true);
        
        return [
            'average_probing' => $this->calculateAverageProbing($chartData),
            'deep_sites_count' => $this->countDeepSites($chartData),
            'bleeding_sites_count' => $this->countBleedingSites($chartData),
            'mobility_count' => $this->countMobility($chartData),
            'furcation_count' => $this->countFurcation($chartData),
            'overall_health_score' => $this->calculateHealthScore($chartData)
        ];
    }

    /**
     * Perform risk assessment
     */
    private function performRiskAssessment(array $chart): array
    {
        $summary = $this->generateSummary($chart);
        
        $riskLevel = 'low';
        $riskScore = 0;

        // Calculate risk score based on various factors
        if ($summary['average_probing'] >= 5) $riskScore += 3;
        elseif ($summary['average_probing'] >= 4) $riskScore += 2;
        elseif ($summary['average_probing'] >= 3) $riskScore += 1;

        if ($summary['deep_sites_count'] >= 10) $riskScore += 3;
        elseif ($summary['deep_sites_count'] >= 5) $riskScore += 2;
        elseif ($summary['deep_sites_count'] >= 2) $riskScore += 1;

        if ($summary['bleeding_sites_count'] >= 20) $riskScore += 2;
        elseif ($summary['bleeding_sites_count'] >= 10) $riskScore += 1;

        if ($summary['mobility_count'] >= 3) $riskScore += 3;
        elseif ($summary['mobility_count'] >= 1) $riskScore += 1;

        if ($summary['furcation_count'] >= 2) $riskScore += 2;
        elseif ($summary['furcation_count'] >= 1) $riskScore += 1;

        // Determine risk level
        if ($riskScore >= 8) $riskLevel = 'severe';
        elseif ($riskScore >= 5) $riskLevel = 'moderate';
        elseif ($riskScore >= 3) $riskLevel = 'mild';

        return [
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'progression_risk' => $this->calculateProgressionRisk($summary),
            'treatment_urgency' => $this->determineTreatmentUrgency($riskLevel)
        ];
    }

    /**
     * Count deep sites (>4mm)
     */
    private function countDeepSites(array $chartData): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $toothData) {
                foreach (['B', 'M', 'L'] as $site) {
                    $value = $toothData['probing'][$site] ?? '';
                    if (is_numeric($value) && $value >= 4) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Count mobility
     */
    private function countMobility(array $chartData): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $toothData) {
                $value = $toothData['mobility'] ?? '';
                if (is_numeric($value) && $value > 0) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Count furcation involvement
     */
    private function countFurcation(array $chartData): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $toothData) {
                $value = $toothData['furcation'] ?? '';
                if (is_numeric($value) && $value > 0) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Calculate overall health score (0-100)
     */
    private function calculateHealthScore(array $chartData): int
    {
        $score = 100;
        
        // Deduct points for issues
        $score -= min($this->countDeepSites($chartData) * 2, 40);
        $score -= min($this->countBleedingSites($chartData), 30);
        $score -= min($this->countMobility($chartData) * 5, 20);
        $score -= min($this->countFurcation($chartData) * 3, 10);
        
        return max(0, $score);
    }

    /**
     * Calculate progression risk
     */
    private function calculateProgressionRisk(array $summary): string
    {
        $riskFactors = 0;
        
        if ($summary['average_probing'] >= 4) $riskFactors++;
        if ($summary['deep_sites_count'] >= 5) $riskFactors++;
        if ($summary['bleeding_sites_count'] >= 15) $riskFactors++;
        if ($summary['mobility_count'] >= 2) $riskFactors++;
        if ($summary['furcation_count'] >= 1) $riskFactors++;

        if ($riskFactors >= 4) return 'high';
        if ($riskFactors >= 2) return 'moderate';
        return 'low';
    }

    /**
     * Determine treatment urgency
     */
    private function determineTreatmentUrgency(string $riskLevel): string
    {
        return match($riskLevel) {
            'severe' => 'immediate',
            'moderate' => 'urgent',
            'mild' => 'soon',
            default => 'routine'
        };
    }

    /**
     * Generate recommendations based on chart data
     */
    private function generateRecommendations(array $chart, array $riskAssessment): array
    {
        $recommendations = [];
        $summary = $this->generateSummary($chart);
        
        if ($summary['average_probing'] >= 5) {
            $recommendations[] = 'Immediate periodontal therapy required - deep scaling and root planing';
        }
        
        if ($summary['deep_sites_count'] >= 10) {
            $recommendations[] = 'Comprehensive periodontal treatment plan recommended';
        }
        
        if ($summary['bleeding_sites_count'] >= 15) {
            $recommendations[] = 'Intensive oral hygiene instruction and plaque control program';
        }
        
        if ($summary['mobility_count'] >= 2) {
            $recommendations[] = 'Splinting or extraction consideration for mobile teeth';
        }
        
        if ($summary['furcation_count'] >= 1) {
            $recommendations[] = 'Furcation involvement management required';
        }

        if ($riskAssessment['progression_risk'] === 'high') {
            $recommendations[] = 'Close monitoring - re-evaluation in 3 months';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Continue regular maintenance and good oral hygiene';
        }

        return $recommendations;
    }

    /**
     * Perform comparison between two charts
     */
    private function performComparison(array $chart1, array $chart2): array
    {
        $data1 = json_decode($chart1['chart_data'], true);
        $data2 = json_decode($chart2['chart_data'], true);

        $comparison = [];

        // Compare average probing
        $avg1 = $this->calculateAverageProbing($data1);
        $avg2 = $this->calculateAverageProbing($data2);
        $comparison['probing'] = [
            'chart1' => $avg1,
            'chart2' => $avg2,
            'difference' => round($avg2 - $avg1, 1),
            'trend' => $avg2 > $avg1 ? 'worsening' : ($avg2 < $avg1 ? 'improving' : 'stable')
        ];

        // Compare bleeding sites
        $bleeding1 = $this->countBleedingSites($data1);
        $bleeding2 = $this->countBleedingSites($data2);
        $comparison['bleeding'] = [
            'chart1' => $bleeding1,
            'chart2' => $bleeding2,
            'difference' => $bleeding2 - $bleeding1,
            'trend' => $bleeding2 > $bleeding1 ? 'worsening' : ($bleeding2 < $bleeding1 ? 'improving' : 'stable')
        ];

        // Compare health scores
        $score1 = $this->calculateHealthScore($data1);
        $score2 = $this->calculateHealthScore($data2);
        $comparison['health_score'] = [
            'chart1' => $score1,
            'chart2' => $score2,
            'difference' => $score2 - $score1,
            'trend' => $score2 > $score1 ? 'improving' : ($score2 < $score1 ? 'worsening' : 'stable')
        ];

        return $comparison;
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport(array $chart, array $patient): string
    {
        // This would integrate with a PDF generation library
        // For now, return a placeholder path
        $filename = "periodontal_chart_{$chart['patient_id']}_{$chart['chart_date']}.pdf";
        $path = storage_path("exports/{$filename}");
        
        // Implementation would go here
        // Generate professional PDF with charts, tables, and analysis
        
        return $path;
    }

    /**
     * Get overall statistics for a set of charts
     */
    private function getOverallStatistics(array $charts): array
    {
        if (empty($charts)) {
            return [
                'total_charts' => 0,
                'date_range' => null,
                'improvement_rate' => 0,
                'stability_rate' => 0,
                'worsening_rate' => 0
            ];
        }

        $totalCharts = count($charts);
        $improving = 0;
        $stable = 0;
        $worsening = 0;

        for ($i = 1; $i < $totalCharts; $i++) {
            $trends = $this->calculateTrends([$charts[$i-1], $charts[$i]]);
            
            if ($trends['probing_trend'] === 'improving') {
                $improving++;
            } elseif ($trends['probing_trend'] === 'worsening') {
                $worsening++;
            } else {
                $stable++;
            }
        }

        return [
            'total_charts' => $totalCharts,
            'date_range' => [
                'start' => $charts[$totalCharts-1]['chart_date'],
                'end' => $charts[0]['chart_date']
            ],
            'improvement_rate' => $totalCharts > 1 ? round(($improving / ($totalCharts - 1)) * 100, 1) : 0,
            'stability_rate' => $totalCharts > 1 ? round(($stable / ($totalCharts - 1)) * 100, 1) : 0,
            'worsening_rate' => $totalCharts > 1 ? round(($worsening / ($totalCharts - 1)) * 100, 1) : 0
        ];
    }
}