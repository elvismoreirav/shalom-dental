<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Clase de Manejo de Sesiones
 * ============================================================================
 * Archivo: src/Core/Session.php
 * Descripción: Gestión de sesiones PHP con soporte para flash messages
 * ============================================================================
 */

namespace App\Core;

class Session
{
    private array $config;
    private bool $started = false;
    
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'name' => 'shalom_session',
            'lifetime' => 120,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'http_only' => true,
            'same_site' => 'lax',
        ], $config);
    }
    
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }
        
        // Configurar opciones de sesión
        session_name($this->config['name']);
        
        session_set_cookie_params([
            'lifetime' => $this->config['lifetime'] * 60,
            'path' => $this->config['path'],
            'domain' => $this->config['domain'] ?? '',
            'secure' => $this->config['secure'],
            'httponly' => $this->config['http_only'],
            'samesite' => $this->config['same_site'],
        ]);
        
        session_start();
        $this->started = true;
        
        // Procesar mensajes flash del request anterior
        $this->ageFlashData();
    }
    
    public function regenerate(bool $deleteOldSession = true): bool
    {
        if (!$this->started) $this->start();
        return session_regenerate_id($deleteOldSession);
    }
    
    public function destroy(): void
    {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
        $this->started = false;
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->started) $this->start();
        return $this->has($key) ? $_SESSION[$key] : $default;
    }
    
    public function set(string $key, mixed $value): void
    {
        if (!$this->started) $this->start();
        $_SESSION[$key] = $value;
    }
    
    public function has(string $key): bool
    {
        if (!$this->started) $this->start();
        return isset($_SESSION[$key]);
    }
    
    public function remove(string $key): void
    {
        if (!$this->started) $this->start();
        unset($_SESSION[$key]);
    }
    
    public function all(): array
    {
        if (!$this->started) $this->start();
        return $_SESSION ?? [];
    }
    
    public function clear(): void
    {
        if (!$this->started) $this->start();
        $_SESSION = [];
    }
    
    public function getId(): string
    {
        if (!$this->started) $this->start();
        return session_id();
    }
    
    // =========================================================================
    // FLASH MESSAGES
    // =========================================================================
    
    public function setFlash(string $key, mixed $value): void
    {
        if (!$this->started) $this->start();
        $_SESSION['_flash']['new'][$key] = $value;
    }
    
    public function getFlash(string $key, mixed $default = null): mixed
    {
        if (!$this->started) $this->start();
        return $_SESSION['_flash']['old'][$key] 
            ?? $_SESSION['_flash']['new'][$key] 
            ?? $default;
    }
    
    public function hasFlash(string $key): bool
    {
        if (!$this->started) $this->start();
        return isset($_SESSION['_flash']['old'][$key]) 
            || isset($_SESSION['_flash']['new'][$key]);
    }
    
    public function reflash(): void
    {
        if (!$this->started) $this->start();
        $_SESSION['_flash']['new'] = array_merge(
            $_SESSION['_flash']['new'] ?? [],
            $_SESSION['_flash']['old'] ?? []
        );
        $_SESSION['_flash']['old'] = [];
    }
    
    private function ageFlashData(): void
    {
        if (isset($_SESSION['_flash']['new'])) {
            $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'];
        }
        $_SESSION['_flash']['new'] = [];
    }
    
    // =========================================================================
    // CSRF TOKEN
    // =========================================================================
    
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->set('_csrf_token', $token);
        $this->set('_csrf_token_time', time());
        return $token;
    }
    
    public function getCsrfToken(): string
    {
        if (!$this->has('_csrf_token')) {
            return $this->generateCsrfToken();
        }
        return $this->get('_csrf_token');
    }
    
    public function validateCsrfToken(string $token, int $maxAge = 7200): bool
    {
        $storedToken = $this->get('_csrf_token');
        $tokenTime = $this->get('_csrf_token_time', 0);
        
        if (empty($storedToken) || empty($token)) {
            return false;
        }
        
        if ((time() - $tokenTime) > $maxAge) {
            return false;
        }
        
        return hash_equals($storedToken, $token);
    }
}