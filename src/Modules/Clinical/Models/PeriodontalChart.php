<?php

namespace App\Modules\Clinical\Models;

class PeriodontalChart
{
    public int $id;
    public int $patientId;
    public ?int $appointmentId;
    public string $chartDate;
    public string $chartType;
    public array $chartData;
    public ?string $notes;
    public ?string $recommendations;
    public ?string $riskLevel;
    public ?string $treatmentUrgency;
    public ?string $followUpDate;
    public ?int $createdBy;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    /**
     * Fill model properties from array data
     */
    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            $property = $this->snakeToCamelCase($key);
            if (property_exists($this, $property)) {
                $this->$property = $this->castValue($property, $value);
            }
        }
    }

    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patientId,
            'appointment_id' => $this->appointmentId,
            'chart_date' => $this->chartDate,
            'chart_type' => $this->chartType,
            'chart_data' => $this->chartData,
            'notes' => $this->notes,
            'recommendations' => $this->recommendations,
            'risk_level' => $this->riskLevel,
            'treatment_urgency' => $this->treatmentUrgency,
            'follow_up_date' => $this->followUpDate,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    /**
     * Get chart data for specific arch
     */
    public function getArchData(string $arch): array
    {
        return $this->chartData[$arch] ?? [];
    }

    /**
     * Get data for specific tooth
     */
    public function getToothData(string $arch, int $toothNumber): array
    {
        return $this->chartData[$arch][$toothNumber] ?? [];
    }

    /**
     * Update specific tooth data
     */
    public function updateToothData(string $arch, int $toothNumber, array $data): void
    {
        if (!isset($this->chartData[$arch])) {
            $this->chartData[$arch] = [];
        }
        
        if (!isset($this->chartData[$arch][$toothNumber])) {
            $this->chartData[$arch][$toothNumber] = [
                'probing' => ['B' => '', 'M' => '', 'L' => ''],
                'bleeding' => ['B' => '', 'M' => '', 'L' => ''],
                'recession' => ['B' => '', 'M' => '', 'L' => ''],
                'mobility' => '',
                'furcation' => ''
            ];
        }
        
        $this->chartData[$arch][$toothNumber] = array_merge(
            $this->chartData[$arch][$toothNumber],
            $data
        );
    }

    /**
     * Get average probing depth
     */
    public function getAverageProbing(): float
    {
        $total = 0;
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($this->getArchData($arch) as $toothData) {
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
     * Count deep sites (>4mm)
     */
    public function countDeepSites(): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($this->getArchData($arch) as $toothData) {
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
     * Count bleeding sites
     */
    public function countBleedingSites(): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($this->getArchData($arch) as $toothData) {
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
     * Count mobile teeth
     */
    public function countMobility(): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($this->getArchData($arch) as $toothData) {
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
    public function countFurcation(): int
    {
        $count = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($this->getArchData($arch) as $toothData) {
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
    public function calculateHealthScore(): int
    {
        $score = 100;
        
        // Deduct points for issues
        $score -= min($this->countDeepSites() * 2, 40);
        $score -= min($this->countBleedingSites(), 30);
        $score -= min($this->countMobility() * 5, 20);
        $score -= min($this->countFurcation() * 3, 10);
        
        return max(0, $score);
    }

    /**
     * Perform risk assessment
     */
    public function performRiskAssessment(): array
    {
        $riskScore = 0;

        // Calculate risk score based on various factors
        if ($this->getAverageProbing() >= 5) $riskScore += 3;
        elseif ($this->getAverageProbing() >= 4) $riskScore += 2;
        elseif ($this->getAverageProbing() >= 3) $riskScore += 1;

        if ($this->countDeepSites() >= 10) $riskScore += 3;
        elseif ($this->countDeepSites() >= 5) $riskScore += 2;
        elseif ($this->countDeepSites() >= 2) $riskScore += 1;

        if ($this->countBleedingSites() >= 20) $riskScore += 2;
        elseif ($this->countBleedingSites() >= 10) $riskScore += 1;

        if ($this->countMobility() >= 3) $riskScore += 3;
        elseif ($this->countMobility() >= 1) $riskScore += 1;

        if ($this->countFurcation() >= 2) $riskScore += 2;
        elseif ($this->countFurcation() >= 1) $riskScore += 1;

        // Determine risk level
        if ($riskScore >= 8) $riskLevel = 'severe';
        elseif ($riskScore >= 5) $riskLevel = 'moderate';
        elseif ($riskScore >= 3) $riskLevel = 'mild';
        else $riskLevel = 'low';

        return [
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'progression_risk' => $this->calculateProgressionRisk(),
            'treatment_urgency' => $this->determineTreatmentUrgency($riskLevel)
        ];
    }

    /**
     * Calculate progression risk
     */
    private function calculateProgressionRisk(): string
    {
        $riskFactors = 0;
        
        if ($this->getAverageProbing() >= 4) $riskFactors++;
        if ($this->countDeepSites() >= 5) $riskFactors++;
        if ($this->countBleedingSites() >= 15) $riskFactors++;
        if ($this->countMobility() >= 2) $riskFactors++;
        if ($this->countFurcation() >= 1) $riskFactors++;

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
     * Get alerts based on chart data
     */
    public function getAlerts(): array
    {
        $alerts = [];
        
        if ($this->getAverageProbing() >= 5) {
            $alerts[] = [
                'type' => 'critical',
                'message' => 'Profundidad de sondaje promedio severa'
            ];
        }
        
        if ($this->countDeepSites() >= 10) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Múltiples sitios profundos detectados'
            ];
        }
        
        if ($this->countMobility() >= 2) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Movilidad dental significativa'
            ];
        }
        
        if ($this->countFurcation() >= 1) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Involución de furcación presente'
            ];
        }

        return $alerts;
    }

    /**
     * Generate recommendations
     */
    public function generateRecommendations(): array
    {
        $recommendations = [];
        $riskAssessment = $this->performRiskAssessment();
        
        if ($this->getAverageProbing() >= 5) {
            $recommendations[] = 'Terapia periodontal inmediata requerida - raspado y alisado radicular profundo';
        }
        
        if ($this->countDeepSites() >= 10) {
            $recommendations[] = 'Plan de tratamiento periodontal comprehensivo recomendado';
        }
        
        if ($this->countBleedingSites() >= 15) {
            $recommendations[] = 'Instrucción intensiva de higiene oral y programa de control de placa';
        }
        
        if ($this->countMobility() >= 2) {
            $recommendations[] = 'Considerar ferulización o extracción para dientes móviles';
        }
        
        if ($this->countFurcation() >= 1) {
            $recommendations[] = 'Manejo de involución de furcación requerido';
        }

        if ($riskAssessment['progression_risk'] === 'high') {
            $recommendations[] = 'Monitoreo cercano - reevaluación en 3 meses';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Continuar mantenimiento regular y buena higiene oral';
        }

        return $recommendations;
    }

    /**
     * Update chart metadata
     */
    public function updateMetadata(): void
    {
        $riskAssessment = $this->performRiskAssessment();
        $this->riskLevel = $riskAssessment['risk_level'];
        $this->treatmentUrgency = $riskAssessment['treatment_urgency'];
        
        // Calculate follow-up date based on risk
        $followUpDays = match($this->riskLevel) {
            'severe' => 30,
            'moderate' => 60,
            'mild' => 90,
            default => 180
        };
        
        $this->followUpDate = date('Y-m-d', strtotime("+{$followUpDays} days"));
        
        // Generate recommendations
        $this->recommendations = implode('. ', $this->generateRecommendations());
    }

    /**
     * Get summary statistics
     */
    public function getSummary(): array
    {
        return [
            'average_probing' => $this->getAverageProbing(),
            'deep_sites_count' => $this->countDeepSites(),
            'bleeding_sites_count' => $this->countBleedingSites(),
            'mobility_count' => $this->countMobility(),
            'furcation_count' => $this->countFurcation(),
            'overall_health_score' => $this->calculateHealthScore(),
            'risk_assessment' => $this->performRiskAssessment(),
            'alerts' => $this->getAlerts(),
            'recommendations' => $this->generateRecommendations()
        ];
    }

    /**
     * Validate chart data
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->patientId)) {
            $errors[] = 'Patient ID is required';
        }
        
        if (empty($this->chartDate)) {
            $errors[] = 'Chart date is required';
        }
        
        if (!in_array($this->chartType, ['initial', 'follow_up', 'maintenance', 'post_treatment'])) {
            $errors[] = 'Invalid chart type';
        }
        
        if (empty($this->chartData) || !is_array($this->chartData)) {
            $errors[] = 'Chart data is required and must be an array';
        }
        
        // Validate chart structure
        if (!empty($this->chartData)) {
            $requiredArches = ['upper', 'lower'];
            foreach ($requiredArches as $arch) {
                if (!isset($this->chartData[$arch])) {
                    $errors[] = "Missing {$arch} arch data";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Convert snake_case to camelCase
     */
    private function snakeToCamelCase(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    /**
     * Cast value to appropriate type
     */
    private function castValue(string $property, $value): mixed
    {
        return match($property) {
            'id', 'patientId', 'appointmentId', 'createdBy' => (int)$value,
            'chartData' => is_string($value) ? json_decode($value, true) : $value,
            default => $value
        };
    }

    /**
     * Get chart date formatted
     */
    public function getFormattedDate(): string
    {
        return date('d/m/Y', strtotime($this->chartDate));
    }

    /**
     * Get chart age in days
     */
    public function getAgeInDays(): int
    {
        $chartDate = new \DateTime($this->chartDate);
        $today = new \DateTime();
        return $today->diff($chartDate)->days;
    }

    /**
     * Check if chart is recent (within last 30 days)
     */
    public function isRecent(): bool
    {
        return $this->getAgeInDays() <= 30;
    }

    /**
     * Check if chart needs update (older than 6 months)
     */
    public function needsUpdate(): bool
    {
        return $this->getAgeInDays() > 180;
    }

    /**
     * Get chart type label
     */
    public function getChartTypeLabel(): string
    {
        return match($this->chartType) {
            'initial' => 'Inicial',
            'follow_up' => 'Seguimiento',
            'maintenance' => 'Mantenimiento',
            'post_treatment' => 'Post-tratamiento',
            default => ucfirst($this->chartType)
        };
    }

    /**
     * Get risk level label with color
     */
    public function getRiskLevelDisplay(): array
    {
        return match($this->riskLevel) {
            'low' => ['label' => 'Riesgo Bajo', 'color' => 'green'],
            'mild' => ['label' => 'Riesgo Leve', 'color' => 'yellow'],
            'moderate' => ['label' => 'Riesgo Moderado', 'color' => 'orange'],
            'severe' => ['label' => 'Riesgo Severo', 'color' => 'red'],
            default => ['label' => 'No evaluado', 'color' => 'gray']
        };
    }
}