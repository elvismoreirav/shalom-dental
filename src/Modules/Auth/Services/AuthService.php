<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Auth Service
 * ============================================================================
 * Archivo: src/Modules/Auth/Services/AuthService.php
 * Descripción: Servicio de autenticación completo
 * ============================================================================
 */

namespace App\Modules\Auth\Services;

use App\Core\Database;
use App\Core\Session;

class AuthService
{
    private Database $db;
    private Session $session;
    private ?array $user = null;
    
    private array $config = [
        'password_cost' => 12,
        'max_login_attempts' => 5,
        'lockout_time' => 15,
        'remember_token_lifetime' => 43200,
        'session_lifetime' => 120,
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = new Session();
    }
    
    /**
     * Intentar hacer login
     */
    public function attempt(string $email, string $password, bool $remember = false): array
    {
        // 1. Buscar usuario
        $user = $this->db->selectOne(
            "SELECT u.*, r.code as role_code, r.name as role_name, o.id as organization_id
             FROM users u
             JOIN roles r ON u.role_id = r.id
             JOIN organizations o ON u.organization_id = o.id
             WHERE u.email = ?",
            [$email]
        );
        
        // 2. Validaciones básicas
        if (!$user) {
            return $this->failedResponse('Credenciales incorrectas');
        }
        
        if ($this->isLocked($user)) {
            $remaining = $this->getRemainingLockoutMinutes($user);
            return $this->failedResponse("Cuenta bloqueada. Intente en {$remaining} minutos.");
        }
        
        if (!$user['is_active']) {
            return $this->failedResponse('Su cuenta ha sido desactivada.');
        }
        
        // 3. Verificar password
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementFailedAttempts($user['id']);
            return $this->failedResponse('Credenciales incorrectas');
        }
        
        // 4. Login exitoso
        $this->resetFailedAttempts($user['id']);
        $this->createSession($user, $remember);
        
        // Cargar datos completos en memoria
        $this->user = $this->loadUserWithPermissions($user['id']);
        
        // Actualizar último acceso
        $this->db->query(
            "UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
            [$_SERVER['REMOTE_ADDR'] ?? null, $user['id']]
        );
        
        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => $this->sanitizeUser($this->user),
        ];
    }
    
    /**
     * Crear sesión PHP y DB
     */
    private function createSession(array $user, bool $remember = false): void
    {
        $this->session->regenerate(true);
        $this->session->set('user_id', $user['id']);
        $this->session->set('organization_id', $user['organization_id']);
        $this->session->set('logged_in_at', time());
        
        if ($remember) {
            $this->createRememberToken($user['id']);
        }
    }

    /**
     * Verificar si el usuario está logueado
     * ESTE ES EL MÉTODO QUE FALTABA
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Obtener ID del usuario actual
     */
    public function id(): ?int
    {
        return $this->user() ? $this->user['id'] : null;
    }
    
    /**
     * Obtener usuario actual (Lazy loading)
     */
    public function user(): ?array
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->session->has('user_id')) {
            $userId = $this->session->get('user_id');
            $this->user = $this->loadUserWithPermissions($userId);
        }

        return $this->user;
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        $this->forgetRememberToken();
        $this->session->destroy();
        $this->user = null;
    }

    /**
     * Intentar login con cookie "Recuérdame"
     */
    public function attemptRememberToken(): bool
    {
        if (!isset($_COOKIE['remember_token'])) return false;

        $parts = explode('|', $_COOKIE['remember_token']);
        if (count($parts) !== 2) return false;

        [$userId, $token] = $parts;
        $hash = hash('sha256', $token);

        // Buscar sesión válida en BD
        $session = $this->db->selectOne(
            "SELECT * FROM user_sessions 
             WHERE user_id = ? AND token_hash = ? AND expires_at > NOW()",
            [$userId, $hash]
        );

        if ($session) {
            // Login automático
            $user = $this->loadUserWithPermissions($userId);
            if ($user && $user['is_active']) {
                $this->createSession($user, true);
                $this->user = $user;
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // Helpers Privados
    // =========================================================================

    private function loadUserWithPermissions(int $userId): ?array
    {
        $user = $this->db->selectOne(
            "SELECT u.*, r.code as role_code, r.name as role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.id = ?", 
            [$userId]
        );

        if (!$user) return null;

        // Cargar permisos del rol
        $permissions = $this->db->select(
            "SELECT p.module, p.resource, p.action
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?",
            [$user['role_id']]
        );

        $user['permissions'] = array_map(
            fn(array $perm) => $perm['module'] . '.' . $perm['resource'] . '.' . $perm['action'],
            $permissions
        );

        // Cargar sedes del usuario (para selector en layout y cambio de sede)
        $user['locations'] = $this->db->select(
            "SELECT l.*, lu.is_primary
             FROM locations l
             JOIN location_users lu ON lu.location_id = l.id
             WHERE lu.user_id = ?
             ORDER BY lu.is_primary DESC, l.name ASC",
            [$userId]
        );

        return $user;
    }
    
    private function createRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 días

        // Guardar en BD
        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'session_id' => session_id(),
            'token_hash' => $hash,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at' => $expires
        ]);

        // Guardar Cookie
        setcookie('remember_token', "$userId|$token", time() + (86400 * 30), '/', '', false, true);
    }

    private function forgetRememberToken(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    }

    private function isLocked(array $user): bool 
    { 
        return !empty($user['locked_until']) && strtotime($user['locked_until']) > time(); 
    }

    private function getRemainingLockoutMinutes(array $user): int 
    { 
        return max(0, ceil((strtotime($user['locked_until']) - time()) / 60)); 
    }

    private function incrementFailedAttempts(int $userId): void 
    { 
        $this->db->query("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?", [$userId]); 
    }

    private function resetFailedAttempts(int $userId): void 
    { 
        $this->db->query("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?", [$userId]); 
    }

    private function sanitizeUser(array $user): array 
    { 
        unset($user['password_hash']); 
        return $user; 
    }

    private function failedResponse(string $msg): array 
    { 
        return ['success' => false, 'message' => $msg]; 
    }
    
    // Métodos de permisos (Placeholders)
    public function can(string $permission): bool
    {
        $user = $this->user();
        if (!$user) return false;

        // Super admin: acceso total
        if (($user['role_code'] ?? null) === 'super_admin') {
            return true;
        }

        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions, true);
    }

    public function hasRole(string ...$roles): bool
    {
        $user = $this->user();
        $roleCode = $user['role_code'] ?? '';
        return in_array($roleCode, $roles, true);
    }
}
