<?php
/**
 * ============================================================================
 * SHALOM DENTAL - CSRF Middleware
 * ============================================================================
 * Archivo: src/Core/Middleware/CsrfMiddleware.php
 * Descripción: Protección contra ataques Cross-Site Request Forgery
 * ============================================================================
 */

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session; // Importante

class CsrfMiddleware implements MiddlewareInterface
{
    private const PROTECTED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];
    
    private array $except = [
        '/api/webhooks/*',
        '/api/sri/callback',
    ];
    
    public function handle(Request $request, callable $next): Response
    {
        // Instanciar Session directamente si app('session') no está disponible aún
        $session = new Session();
        
        // Asegurar que existe un token CSRF
        if (!$session->has('_csrf_token')) {
            $session->generateCsrfToken();
        }
        
        // Verificar token en métodos protegidos
        if ($this->shouldVerify($request)) {
            $token = $this->getTokenFromRequest($request);
            
            if (!$session->validateCsrfToken($token)) {
                return $this->tokenMismatch($request, $session);
            }
        }
        
        return $next($request);
    }
    
    private function shouldVerify(Request $request): bool
    {
        if (!in_array($request->method(), self::PROTECTED_METHODS)) {
            return false;
        }
        
        $uri = $request->uri();
        
        foreach ($this->except as $pattern) {
            if ($this->matchesPattern($uri, $pattern)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function matchesPattern(string $uri, string $pattern): bool
    {
        if ($pattern === $uri) {
            return true;
        }
        $pattern = str_replace('*', '.*', $pattern);
        return (bool) preg_match('#^' . $pattern . '$#', $uri);
    }
    
    private function getTokenFromRequest(Request $request): string
    {
        $token = $request->header('X-CSRF-Token');
        if (!empty($token)) return $token;
        
        $token = $request->header('X-XSRF-Token');
        if (!empty($token)) return $token;
        
        return $request->input('_csrf_token', '');
    }
    
    private function tokenMismatch(Request $request, Session $session): Response
    {
        if ($request->wantsJson() || $request->isAjax()) {
            return Response::error(
                'Token CSRF inválido o expirado',
                419,
                null,
                'CSRF_TOKEN_MISMATCH'
            );
        }
        
        $session->generateCsrfToken();
        $session->setFlash('error', 'La sesión ha expirado. Por favor, intente nuevamente.');
        
        return Response::redirect($request->header('Referer', '/'));
    }
    
    public function except(string ...$uris): self
    {
        $this->except = array_merge($this->except, $uris);
        return $this;
    }
}