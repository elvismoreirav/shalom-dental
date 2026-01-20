<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use Database\Seeds\ClinicalCareSeeder;
use Database\Seeds\ClinicalPermissionsSeeder;

$db = Database::getInstance();

$seeders = [
    new ClinicalCareSeeder(),
    new ClinicalPermissionsSeeder(),
];

foreach ($seeders as $seeder) {
    $seeder->run($db);
}

echo "Seeders ejecutados correctamente.\n";
