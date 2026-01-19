<?php
namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class RoleMiddleware implements MiddlewareInterface
{
    private array $roles;
    
    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }
    
    public function handle(Request $request, callable $next): Response
    {
        $user = $request->user(); // Asegúrate de que Request tenga setUser/user implementado
        
        if (!$user) {
            return $this->forbidden($request);
        }
        
        $userRole = $user['role_code'] ?? null;
        
        if ($userRole === 'super_admin' || in_array($userRole, $this->roles)) {
            return $next($request);
        }
        
        return $this->forbidden($request);
    }
    
    private function forbidden(Request $request): Response
    {
        if ($request->wantsJson() || $request->isAjax()) {
            return Response::forbidden('Sin permisos');
        }
        return Response::view('errors.403', [], 403);
    }
}