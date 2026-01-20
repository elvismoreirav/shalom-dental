<?php

use App\Core\Database;

/**
 * Seeder for Periodontal Charts
 */
class PeriodontalChartsSeeder
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        echo "Seeding Periodontal Charts...\n";

        // Get sample patients
        $patients = $this->db->fetchAll("SELECT id FROM patients ORDER BY RAND() LIMIT 5");
        
        if (empty($patients)) {
            echo "No patients found. Skipping periodontal charts seeding.\n";
            return;
        }

        foreach ($patients as $patient) {
            $this->createSampleCharts($patient['id']);
        }

        echo "Periodontal Charts seeding completed.\n";
    }

    private function createSampleCharts(int $patientId): void
    {
        $chartTypes = ['initial', 'follow_up', 'maintenance'];
        $dates = [
            'initial' => date('Y-m-d', strtotime('-90 days')),
            'follow_up' => date('Y-m-d', strtotime('-30 days')),
            'maintenance' => date('Y-m-d', strtotime('-7 days'))
        ];

        foreach ($chartTypes as $type) {
            $chartData = $this->generateSampleChartData($type);
            
            $this->db->execute(
                "INSERT INTO periodontal_charts (
                    patient_id, chart_date, chart_type, chart_data, 
                    notes, risk_level, treatment_urgency, follow_up_date,
                    created_by, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $patientId,
                    $dates[$type],
                    $type,
                    json_encode($chartData),
                    $this->generateSampleNotes($type),
                    $this->calculateRiskLevel($chartData),
                    $this->calculateTreatmentUrgency($type),
                    date('Y-m-d', strtotime('+90 days')),
                    1, // Assuming user ID 1 exists
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ]
            );
        }
    }

    private function generateSampleChartData(string $type): array
    {
        $baseData = [];
        
        // Initialize upper arch (18-11)
        for ($tooth = 18; $tooth >= 11; $tooth--) {
            $baseData['upper'][$tooth] = [
                'probing' => ['B' => '', 'M' => '', 'L' => ''],
                'bleeding' => ['B' => '', 'M' => '', 'L' => ''],
                'recession' => ['B' => '', 'M' => '', 'L' => ''],
                'mobility' => '',
                'furcation' => ''
            ];
        }

        // Initialize lower arch (31-46)
        for ($tooth = 31; $tooth <= 46; $tooth++) {
            $baseData['lower'][$tooth] = [
                'probing' => ['B' => '', 'M' => '', 'L' => ''],
                'bleeding' => ['B' => '', 'M' => '', 'L' => ''],
                'recession' => ['B' => '', 'M' => '', 'L' => ''],
                'mobility' => '',
                'furcation' => ''
            ];
        }

        // Add sample measurements based on chart type
        switch ($type) {
            case 'initial':
                // Poor periodontal health for initial chart
                $this->addPoorHealthMeasurements($baseData);
                break;
            case 'follow_up':
                // Moderate improvement for follow-up
                $this->addModerateHealthMeasurements($baseData);
                break;
            case 'maintenance':
                // Good health for maintenance
                $this->addGoodHealthMeasurements($baseData);
                break;
        }

        return $baseData;
    }

    private function addPoorHealthMeasurements(array &$data): void
    {
        // Add some deep probing depths
        $deepTeeth = [16, 36, 37, 46];
        foreach ($deepTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['probing'] = ['B' => 5, 'M' => 6, 'L' => 5];
                $data['upper'][$tooth]['bleeding'] = ['B' => '+', 'M' => '++', 'L' => '+'];
                $data['upper'][$tooth]['mobility'] = '1';
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['probing'] = ['B' => 5, 'M' => 6, 'L' => 5];
                $data['lower'][$tooth]['bleeding'] = ['B' => '+', 'M' => '++', 'L' => '+'];
                $data['lower'][$tooth]['mobility'] = '1';
            }
        }

        // Add furcation involvement on molars
        $molarsUpper = [16, 17, 26, 27];
        foreach ($molarsUpper as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['furcation'] = '2';
            }
        }

        $molarsLower = [36, 37, 46, 47];
        foreach ($molarsLower as $tooth) {
            if (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['furcation'] = '2';
            }
        }

        // Add recession
        $recessionTeeth = [12, 22, 32, 42];
        foreach ($recessionTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['recession'] = ['B' => 2, 'M' => 3, 'L' => 2];
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['recession'] = ['B' => 2, 'M' => 3, 'L' => 2];
            }
        }
    }

    private function addModerateHealthMeasurements(array &$data): void
    {
        // Add moderate probing depths
        $moderateTeeth = [15, 35, 45];
        foreach ($moderateTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['probing'] = ['B' => 3, 'M' => 4, 'L' => 3];
                $data['upper'][$tooth]['bleeding'] = ['B' => '+', 'M' => '-', 'L' => '-'];
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['probing'] = ['B' => 3, 'M' => 4, 'L' => 3];
                $data['lower'][$tooth]['bleeding'] = ['B' => '+', 'M' => '-', 'L' => '-'];
            }
        }

        // Add light furcation involvement
        $furcationTeeth = [16, 36];
        foreach ($furcationTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['furcation'] = '1';
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['furcation'] = '1';
            }
        }

        // Add light recession
        $lightRecessionTeeth = [11, 31];
        foreach ($lightRecessionTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['recession'] = ['B' => 1, 'M' => 1, 'L' => 0];
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['recession'] = ['B' => 1, 'M' => 1, 'L' => 0];
            }
        }
    }

    private function addGoodHealthMeasurements(array &$data): void
    {
        // Add normal probing depths
        $normalTeeth = [14, 24, 34, 44];
        foreach ($normalTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['probing'] = ['B' => 1, 'M' => 2, 'L' => 1];
                $data['upper'][$tooth]['bleeding'] = ['B' => '-', 'M' => '-', 'L' => '-'];
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['probing'] = ['B' => 1, 'M' => 2, 'L' => 1];
                $data['lower'][$tooth]['bleeding'] = ['B' => '-', 'M' => '-', 'L' => '-'];
            }
        }

        // Add minimal recession on some teeth
        $minimalRecessionTeeth = [11, 21, 31, 41];
        foreach ($minimalRecessionTeeth as $tooth) {
            if (isset($data['upper'][$tooth])) {
                $data['upper'][$tooth]['recession'] = ['B' => 0, 'M' => 1, 'L' => 0];
            } elseif (isset($data['lower'][$tooth])) {
                $data['lower'][$tooth]['recession'] = ['B' => 0, 'M' => 1, 'L' => 0];
            }
        }
    }

    private function generateSampleNotes(string $type): string
    {
        return match($type) {
            'initial' => 'Paciente presenta enfermedad periodontal severa con múltiples sitios de profundidad >5mm y sangrado generalizado. Se recomienda terapia periodontal inicial inmediata.',
            'follow_up' => 'Significativa mejora después de tratamiento inicial. Reducción de sitios profundos y sangrado. Continuar con mantenimiento y evaluación en 3 meses.',
            'maintenance' => 'Salud periodontal estable. Sin sitios de profundidad >4mm ni sangrado al sondaje. Mantener programa de mantenimiento cada 6 meses.',
            default => ''
        };
    }

    private function calculateRiskLevel(array $chartData): string
    {
        $totalProbing = 0;
        $count = 0;
        $bleedingCount = 0;

        foreach (['upper', 'lower'] as $arch) {
            foreach ($chartData[$arch] as $toothData) {
                foreach (['B', 'M', 'L'] as $site) {
                    $probingValue = $toothData['probing'][$site] ?? '';
                    if (is_numeric($probingValue) && $probingValue > 0) {
                        $totalProbing += (float)$probingValue;
                        $count++;
                    }

                    $bleedingValue = $toothData['bleeding'][$site] ?? '';
                    if ($bleedingValue === '+' || $bleedingValue === '++') {
                        $bleedingCount++;
                    }
                }
            }
        }

        $avgProbing = $count > 0 ? $totalProbing / $count : 0;

        if ($avgProbing >= 5 || $bleedingCount >= 20) {
            return 'severe';
        } elseif ($avgProbing >= 4 || $bleedingCount >= 10) {
            return 'moderate';
        } elseif ($avgProbing >= 3 || $bleedingCount >= 5) {
            return 'mild';
        } else {
            return 'low';
        }
    }

    private function calculateTreatmentUrgency(string $type): string
    {
        return match($type) {
            'initial' => 'immediate',
            'follow_up' => 'urgent',
            'maintenance' => 'routine',
            default => 'routine'
        };
    }
}