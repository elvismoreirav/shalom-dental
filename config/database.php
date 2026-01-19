<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Configuración de Base de Datos
 * ============================================================================
 * Archivo: config/database.php
 * Descripción: Configuración de conexión a MySQL
 * ============================================================================
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Conexión por Defecto
    |--------------------------------------------------------------------------
    */
    'default' => env('DB_CONNECTION', 'mysql'),
    
    /*
    |--------------------------------------------------------------------------
    | Conexiones de Base de Datos
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'shalom_dental'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', '12345678'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Tabla de Migraciones
    |--------------------------------------------------------------------------
    */
    'migrations' => 'migrations',
];
