<?php
namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\Auth\Services\AuthService;

class LocationMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $session = new Session();
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }

        // Asegurar que el usuario tenga sedes cargadas
        if (empty($user['locations'])) {
            $auth = new AuthService();
            $user = $auth->user() ?? $user;
            $request->setUser($user);
        }

        $locations = $user['locations'] ?? [];
        $currentLocationId = (int) $session->get('current_location_id', 0);

        // Si no hay sedes, continuar sin seleccionar
        if (empty($locations)) {
            return $next($request);
        }

        $hasCurrent = false;
        foreach ($locations as $location) {
            if ((int) $location['id'] === $currentLocationId) {
                $hasCurrent = true;
                break;
            }
        }

        if (!$hasCurrent) {
            // Seleccionar sede primaria o la primera disponible
            $primary = null;
            foreach ($locations as $location) {
                if (!empty($location['is_primary'])) {
                    $primary = $location;
                    break;
                }
            }
            $selected = $primary ?? $locations[0];
            $session->set('current_location_id', (int) $selected['id']);
            $currentLocationId = (int) $selected['id'];
        }

        // Compartir datos con vistas
        $currentLocation = null;
        foreach ($locations as $location) {
            if ((int) $location['id'] === $currentLocationId) {
                $currentLocation = $location;
                break;
            }
        }

        app('view')->share('currentLocation', $currentLocation);
        app('view')->share('userLocations', $locations);

        return $next($request);
    }
}
