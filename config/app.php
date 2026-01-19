<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Configuración Principal de la Aplicación
 * ============================================================================
 * Archivo: config/app.php
 * Descripción: Configuraciones generales del sistema
 * ============================================================================
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Nombre de la Aplicación
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'Shalom Dental'),
    
    /*
    |--------------------------------------------------------------------------
    | Entorno de la Aplicación
    |--------------------------------------------------------------------------
    | Valores: local, development, staging, production
    */
    'env' => env('APP_ENV', 'production'),
    
    /*
    |--------------------------------------------------------------------------
    | Modo Debug
    |--------------------------------------------------------------------------
    */
    'debug' => env('APP_DEBUG', false),
    
    /*
    |--------------------------------------------------------------------------
    | URL de la Aplicación
    |--------------------------------------------------------------------------
    */
    'url' => env('APP_URL', 'http://localhost'),
    
    /*
    |--------------------------------------------------------------------------
    | Zona Horaria
    |--------------------------------------------------------------------------
    */
    'timezone' => env('APP_TIMEZONE', 'America/Guayaquil'),
    
    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    */
    'locale' => env('APP_LOCALE', 'es'),
    
    /*
    |--------------------------------------------------------------------------
    | Clave de Encriptación
    |--------------------------------------------------------------------------
    */
    'key' => env('APP_KEY', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Sesión
    |--------------------------------------------------------------------------
    */
    'session' => [
        'name' => env('SESSION_NAME', 'shalom_session'),
        'lifetime' => env('SESSION_LIFETIME', 120), // minutos
        'expire_on_close' => false,
        'encrypt' => false,
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => env('SESSION_SECURE', false),
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Autenticación
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'password_cost' => 12, // bcrypt cost
        'max_login_attempts' => 5,
        'lockout_time' => 15, // minutos
        'remember_token_lifetime' => 43200, // 30 días en minutos
        'session_lifetime' => 120, // minutos
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de CSRF
    |--------------------------------------------------------------------------
    */
    'csrf' => [
        'token_name' => '_csrf_token',
        'header_name' => 'X-CSRF-Token',
        'lifetime' => 120, // minutos
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rutas Públicas (sin autenticación)
    |--------------------------------------------------------------------------
    */
    'public_routes' => [
        '/login',
        '/forgot-password',
        '/reset-password',
        '/api/auth/login',
        '/api/auth/forgot-password',
        '/api/auth/reset-password',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Proveedores de Servicios
    |--------------------------------------------------------------------------
    */
    'providers' => [
        \Shalom\Core\Providers\DatabaseServiceProvider::class,
        \Shalom\Core\Providers\SessionServiceProvider::class,
        \Shalom\Core\Providers\AuthServiceProvider::class,
        \Shalom\Core\Providers\ViewServiceProvider::class,
    ],
];
