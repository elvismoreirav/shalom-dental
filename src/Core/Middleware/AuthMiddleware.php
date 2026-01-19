<?php
namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\Auth\Services\AuthService;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Instancia de sesión (o estática si prefieres, pero aquí usaremos la clase)
        // Asumiendo que Session no es estática pura en tu implementación anterior
        // Usaremos el helper app() para obtener la instancia si es necesario, 
        // o métodos estáticos si Session los soporta.
        // Dado que tu Session.php tiene métodos de instancia, necesitamos una instancia.
        
        // OPCIÓN 1: Usar app('session') si lo registraste en Application (Recomendado)
        // OPCIÓN 2: Instanciarla directamente
        
        $session = new Session(); // O app('session')

        // Verificar si existe el usuario en la sesión
        if (!$session->has('user_id')) {
            // Si es una petición AJAX/API, devolver JSON
            if ($request->wantsJson() || $request->isAjax()) {
                return Response::json(['error' => 'Unauthenticated'], 401);
            }
            
            // Si es web normal, redirigir al login
            return Response::redirect('/login');
        }

        // Resolver usuario y adjuntarlo al request para middleware posteriores
        $auth = new AuthService();
        $request->setUser($auth->user());

        return $next($request);
    }
}
