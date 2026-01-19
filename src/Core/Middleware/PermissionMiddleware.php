<?php
namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Modules\Auth\Services\AuthService;

class PermissionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $required = $request->getAttribute('permission', []);
        if (empty($required)) {
            return $next($request);
        }

        $required = is_array($required) ? $required : [$required];
        $auth = new AuthService();

        foreach ($required as $permission) {
            if ($auth->can($permission)) {
                return $next($request);
            }
        }

        if ($request->wantsJson() || $request->isAjax()) {
            return Response::forbidden('Sin permisos');
        }

        return Response::view('errors.403', [], 403);
    }
}
