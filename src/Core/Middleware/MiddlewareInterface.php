<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Middleware Interface
 * ============================================================================
 * Archivo: src/Core/Middleware/MiddlewareInterface.php
 * Descripción: Contrato que deben implementar todos los middleware
 * ============================================================================
 */

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

interface MiddlewareInterface
{
    /**
     * Manejar la petición entrante
     *
     * @param Request $request La petición HTTP
     * @param callable $next El siguiente middleware o controlador
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
}