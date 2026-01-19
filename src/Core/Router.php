<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Router
 * ============================================================================
 * Archivo: src/Core/Router.php
 * Descripción: Sistema de enrutamiento con soporte para grupos y middleware
 * ============================================================================
 */

namespace App\Core;

use Exception;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $groupStack = [];
    private array $globalMiddleware = [];
    
    public function get(string $uri, array|string|callable $action): self
    {
        return $this->addRoute('GET', $uri, $action);
    }
    
    public function post(string $uri, array|string|callable $action): self
    {
        return $this->addRoute('POST', $uri, $action);
    }
    
    public function put(string $uri, array|string|callable $action): self
    {
        return $this->addRoute('PUT', $uri, $action);
    }
    
    public function patch(string $uri, array|string|callable $action): self
    {
        return $this->addRoute('PATCH', $uri, $action);
    }
    
    public function delete(string $uri, array|string|callable $action): self
    {
        return $this->addRoute('DELETE', $uri, $action);
    }
    
    public function any(string $uri, array|string|callable $action): self
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $uri, $action);
        }
        return $this;
    }
    
    private function addRoute(string $method, string $uri, array|string|callable $action): self
    {
        $uri = $this->applyGroupPrefix($uri);
        $middleware = $this->getGroupMiddleware();
        
        $route = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware,
            'pattern' => $this->compilePattern($uri),
            'name' => null,
            'permission' => null,
        ];
        
        $this->routes[$method][] = $route;
        
        return $this;
    }
    
    public function name(string $name): self
    {
        $methods = array_keys($this->routes);
        foreach ($methods as $method) {
            $lastIndex = count($this->routes[$method]) - 1;
            if ($lastIndex >= 0) {
                $this->routes[$method][$lastIndex]['name'] = $name;
                $this->namedRoutes[$name] = &$this->routes[$method][$lastIndex];
                break;
            }
        }
        return $this;
    }
    
    public function middleware(string|array $middleware): self
    {
        $middleware = (array) $middleware;
        $methods = array_keys($this->routes);
        
        foreach ($methods as $method) {
            $lastIndex = count($this->routes[$method]) - 1;
            if ($lastIndex >= 0) {
                $this->routes[$method][$lastIndex]['middleware'] = array_merge(
                    $this->routes[$method][$lastIndex]['middleware'],
                    $middleware
                );
                break;
            }
        }
        return $this;
    }

    public function permission(string|array $permission): self
    {
        $permission = is_array($permission) ? $permission : [$permission];
        $methods = array_keys($this->routes);

        foreach ($methods as $method) {
            $lastIndex = count($this->routes[$method]) - 1;
            if ($lastIndex >= 0) {
                $this->routes[$method][$lastIndex]['permission'] = $permission;
                break;
            }
        }
        return $this;
    }
    
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }
    
    public function globalMiddleware(array $middleware): void
    {
        $this->globalMiddleware = $middleware;
    }
    
    private function applyGroupPrefix(string $uri): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        return rtrim($prefix . '/' . ltrim($uri, '/'), '/') ?: '/';
    }
    
    private function getGroupMiddleware(): array
    {
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array) $group['middleware']);
            }
        }
        return $middleware;
    }
    
    private function compilePattern(string $uri): string
    {
        // Convertir {param} a regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $uri);
        // Convertir {param?} a regex opcional
        $pattern = preg_replace('/\{([a-zA-Z_]+)\?\}/', '(?P<$1>[^/]*)?', $pattern);
        return '#^' . $pattern . '$#';
    }
    
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();
        
        // Buscar ruta coincidente
        $route = $this->findRoute($method, $uri);
        
        if ($route === null) {
            return $this->handleNotFound();
        }
        
        // Extraer parámetros de la URI
        preg_match($route['pattern'], $uri, $matches);
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        $request->setRouteParams($params);

        if (!empty($route['permission'])) {
            $request->setAttribute('permission', $route['permission']);
        }
        
        // Ejecutar middleware y acción
        $middleware = array_merge($this->globalMiddleware, $route['middleware']);
        
        return $this->runMiddlewareStack($middleware, $request, function($request) use ($route) {
            return $this->runAction($route['action'], $request);
        });
    }
    
    private function findRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }
        
        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri)) {
                return $route;
            }
        }
        
        return null;
    }
    
    private function runMiddlewareStack(array $middleware, Request $request, callable $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($next, $middlewareClass) {
                return function ($request) use ($next, $middlewareClass) {
                    $instance = $this->resolveMiddleware($middlewareClass);
                    return $instance->handle($request, $next);
                };
            },
            $destination
        );
        
        return $pipeline($request);
    }
    
    private function resolveMiddleware(string $class): object
    {
        if (!class_exists($class)) {
            throw new Exception("Middleware class not found: {$class}");
        }
        return new $class();
    }
    
    private function runAction(array|string|callable $action, Request $request): Response
    {
        if (is_callable($action)) {
            $result = $action($request);
        } elseif (is_array($action)) {
            [$controller, $method] = $action;
            $instance = new $controller();
            $result = $instance->$method($request);
        } elseif (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            $instance = new $controller();
            $result = $instance->$method($request);
        } else {
            throw new Exception('Invalid route action');
        }
        
        if ($result instanceof Response) {
            return $result;
        }
        
        return new Response($result);
    }
    
    private function handleNotFound(): Response
    {
        return new Response(['error' => 'Not Found'], 404);
    }
    
    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route not found: {$name}");
        }
        
        $uri = $this->namedRoutes[$name]['uri'];
        
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
            $uri = str_replace('{' . $key . '?}', $value, $uri);
        }
        
        return $uri;
    }
}
