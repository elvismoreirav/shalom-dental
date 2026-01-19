<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Funciones Helper Globales
 * ============================================================================
 * Archivo: src/Core/Helpers/helpers.php
 * Descripción: Funciones de ayuda disponibles globalmente
 * ============================================================================
 */

use App\Core\Application;
use App\Core\Response;
use App\Core\Session;
use App\Modules\Auth\Services\AuthService;

if (!function_exists('app')) {
    /**
     * Obtener instancia de la aplicación o resolver un servicio
     */
    function app(?string $abstract = null): mixed
    {
        $instance = Application::getInstance();
        
        if ($abstract === null) {
            return $instance;
        }
        
        // Mapeo manual de servicios (Service Locator simple)
        return match($abstract) {
            'router'  => $instance->getRouter(),
            'view'    => $instance->getView(),
            'session' => new Session(),
            'auth'    => new AuthService(),
            default   => $instance
        };
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true': case '(true)': return true;
            case 'false': case '(false)': return false;
            case 'null': case '(null)': return null;
            case 'empty': case '(empty)': return '';
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return match($key) {
            'app.url' => env('APP_URL', 'http://localhost:8000'),
            'app.name' => env('APP_NAME', 'Shalom Dental'),
            default => $default
        };
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, mixed $default = null): mixed
    {
        $session = new Session();
        if ($key === null) return $session;
        return $session->get($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): AuthService
    {
        return new AuthService();
    }
}

if (!function_exists('user')) {
    function user(): ?array
    {
        return auth()->user();
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return auth()->can($permission);
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string ...$roles): bool
    {
        return auth()->hasRole(...$roles);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return (new Session())->getCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $oldInput = (new Session())->getFlash('old_input');
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): void
    {
        Response::redirect($url, $status)->send();
        exit;
    }
}

if (!function_exists('back')) {
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        return Response::view($template, $data)->getContent();
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return $key;
    }
}

if (!function_exists('activeStartsWith')) {
    function activeStartsWith(string $prefix, string $class = 'active'): string
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return str_starts_with($currentPath, $prefix) ? $class : '';
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        echo '<div style="background-color: #1a202c; color: #cbd5e0; padding: 1rem; z-index: 9999; position: relative; font-family: monospace;">';
        foreach ($vars as $var) {
            echo '<pre>'; var_dump($var); echo '</pre>';
        }
        echo '</div>';
        die(1);
    }
}

if (!function_exists('now')) {
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}

// === FLASH MESSAGES (LOS QUE FALTABAN) ===

if (!function_exists('flash')) {
    /**
     * Establecer mensaje flash
     */
    function flash(string $key, mixed $value): void
    {
        (new Session())->setFlash($key, $value);
    }
}

if (!function_exists('hasFlash')) {
    /**
     * Verificar si existe mensaje flash
     */
    function hasFlash(string $key): bool
    {
        return (new Session())->hasFlash($key);
    }
}

if (!function_exists('getFlash')) {
    /**
     * Obtener mensaje flash
     */
    function getFlash(string $key, mixed $default = null): mixed
    {
        return (new Session())->getFlash($key, $default);
    }
}

if (!function_exists('currentLocation')) {
    function currentLocation(): ?int
    {
        return session('current_location_id');
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $date, string $format = 'd/m/Y'): string
    {
        if (empty($date)) return '';
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney(float $amount, string $currency = '$'): string
    {
        return $currency . ' ' . number_format($amount, 2, '.', ',');
    }
}
