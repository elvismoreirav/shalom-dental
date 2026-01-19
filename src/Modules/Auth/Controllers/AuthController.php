<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Auth Controller
 * ============================================================================
 * Archivo: src/Modules/Auth/Controllers/AuthController.php
 */

namespace App\Modules\Auth\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database; // Importante para switchLocation
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Validators\LoginValidator;

class AuthController
{
    private AuthService $authService;
    
    public function __construct()
    {
        // Instancia directa o vía helper si ya funciona tu inyección
        $this->authService = new AuthService();
    }
    
    /**
     * Mostrar formulario de login
     * GET /login
     */
    public function showLogin(Request $request): Response
    {
        if ($this->authService->check()) {
            return Response::redirect('/dashboard');
        }
        
        if ($this->authService->attemptRememberToken()) {
            $intended = session('intended_url', '/dashboard');
            session()->remove('intended_url');
            return Response::redirect($intended);
        }
        
        return Response::view('auth.login', [
            'title' => 'Iniciar Sesión - Shalom Dental',
        ]);
    }
    
    /**
     * Procesar login
     * POST /login
     */
    public function login(Request $request): Response
    {
        $validator = new LoginValidator();
        $validation = $validator->validate($request->all());
        
        if (!$validation['valid']) {
            if ($request->wantsJson()) {
                return Response::validationError($validation['errors']);
            }
            
            flash('errors', $validation['errors']);
            flash('old_input', $request->only(['email']));
            return Response::redirect('/login');
        }
        
        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->has('remember');
        
        $result = $this->authService->attempt($email, $password, $remember);
        
        if (!$result['success']) {
            if ($request->wantsJson()) {
                return Response::error($result['message'], 401);
            }
            
            flash('error', $result['message']);
            flash('old_input', $request->only(['email']));
            return Response::redirect('/login');
        }
        
        if ($request->wantsJson()) {
            return Response::success([
                'user' => $result['user'],
                'redirect' => session('intended_url', '/dashboard'),
            ], 'Inicio de sesión exitoso');
        }
        
        $intended = session('intended_url', '/dashboard');
        session()->remove('intended_url');
        
        flash('success', '¡Bienvenido de vuelta!');
        return Response::redirect($intended);
    }
    
    /**
     * API Login
     * POST /api/auth/login
     */
    public function apiLogin(Request $request): Response
    {
        $validator = new LoginValidator();
        $validation = $validator->validate($request->all());
        
        if (!$validation['valid']) {
            return Response::validationError($validation['errors']);
        }
        
        $result = $this->authService->attempt(
            $request->input('email'),
            $request->input('password'),
            (bool) $request->input('remember', false)
        );
        
        if (!$result['success']) {
            return Response::error($result['message'], 401, null, 'AUTH_FAILED');
        }
        
        return Response::success([
            'user' => $result['user'],
            'csrf_token' => csrf_token(),
        ], 'Inicio de sesión exitoso');
    }
    
    /**
     * Cerrar sesión
     * POST /logout
     */
    public function logout(Request $request): Response
    {
        $this->authService->logout();
        
        if ($request->wantsJson()) {
            return Response::success(null, 'Sesión cerrada exitosamente');
        }
        
        flash('success', 'Ha cerrado sesión exitosamente');
        return Response::redirect('/login');
    }
    
    /**
     * Obtener usuario actual
     * GET /api/auth/me
     */
    public function me(Request $request): Response
    {
        $user = $this->authService->user();
        
        if (!$user) {
            return Response::unauthorized();
        }
        
        unset($user['password_hash']);
        
        return Response::success([
            'user' => $user,
            'permissions' => $user['permissions'] ?? [],
            'locations' => $user['locations'] ?? [],
            'current_location_id' => session('current_location_id'),
        ]);
    }
    
    /**
     * Cambiar sede activa
     * POST /api/auth/switch-location
     */
    public function switchLocation(Request $request): Response
    {
        $locationId = (int) $request->input('location_id');
        
        if (!$locationId) {
            return Response::error('ID de sede requerido', 400);
        }
        
        $user = $this->authService->user();
        $userLocations = $user['locations'] ?? [];
        
        $hasAccess = false;
        foreach ($userLocations as $location) {
            if ((int) $location['id'] === $locationId) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            return Response::forbidden('No tiene acceso a esta sede');
        }
        
        session()->set('current_location_id', $locationId);
        
        $db = Database::getInstance();
        $location = $db->selectOne(
            "SELECT id, code, name FROM locations WHERE id = ?",
            [$locationId]
        );
        
        return Response::success([
            'location' => $location,
        ], 'Sede cambiada exitosamente');
    }
    
    public function csrfToken(Request $request): Response
    {
        $token = session()->generateCsrfToken();
        return Response::success(['csrf_token' => $token]);
    }
    
    public function check(Request $request): Response
    {
        return Response::success([
            'authenticated' => $this->authService->check(),
            'user_id' => $this->authService->id(),
        ]);
    }
}