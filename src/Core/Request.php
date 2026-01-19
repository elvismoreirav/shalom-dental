<?php

namespace App\Core;

class Request
{
    private array $query;
    private array $post;
    private array $server;
    private array $cookies;
    private array $files;
    private array $headers;
    private array $routeParams = [];
    private ?array $jsonBody = null;
    private ?array $user = null;
    private array $attributes = [];
    
    public function __construct()
    {
        $this->query = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->headers = $this->parseHeaders();
        $this->parseJsonBody();
    }

    public static function capture(): self
    {
        return new self();
    }
    
    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        
        if (isset($this->server['CONTENT_TYPE'])) {
            $headers['CONTENT-TYPE'] = $this->server['CONTENT_TYPE'];
        }
        return $headers;
    }
    
    private function parseJsonBody(): void
    {
        $contentType = $this->header('CONTENT-TYPE', '');
        if (str_contains($contentType, 'application/json')) {
            $body = file_get_contents('php://input');
            if (!empty($body)) {
                $this->jsonBody = json_decode($body, true) ?? [];
            }
        }
    }
    
    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'POST') {
            $override = $this->post['_method'] ?? $this->header('X-HTTP-Method-Override');
            if ($override && in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'])) {
                return strtoupper($override);
            }
        }
        return $method;
    }
    
    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        return '/' . trim($uri, '/');
    }
    
    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') === 'on' || ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
    
    public function isAjax(): bool
    {
        return $this->header('X-REQUESTED-WITH') === 'XMLHttpRequest';
    }
    
    public function wantsJson(): bool
    {
        return str_contains($this->header('ACCEPT', ''), 'application/json');
    }
    
    public function header(string $name, mixed $default = null): mixed
    {
        $name = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$name] ?? $this->headers[str_replace('_', '-', $name)] ?? $default;
    }
    
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->query;
        return $this->query[$key] ?? $default;
    }
    
    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->post;
        return $this->post[$key] ?? $default;
    }

    public function file(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->files;
        return $this->files[$key] ?? $default;
    }
    
    public function input(?string $key = null, mixed $default = null): mixed
    {
        $input = array_merge($this->query, $this->post, $this->jsonBody ?? []);
        if ($key === null) return $input;
        
        // Soporte básico para "dot notation"
        $data = $input;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }

    public function all(): array
    {
        return $this->input();
    }

    /**
     * Verificar si existe un valor en la petición
     * (ESTE ERA EL MÉTODO QUE FALTABA)
     */
    public function has(string $key): bool
    {
        $all = $this->all();
        return array_key_exists($key, $all);
    }

    /**
     * Obtener solo ciertos campos
     */
    public function only(array $keys): array
    {
        $results = [];
        $input = $this->all();
        foreach ($keys as $key) {
            if (array_key_exists($key, $input)) {
                $results[$key] = $input[$key];
            }
        }
        return $results;
    }

    /**
     * Obtener todo excepto ciertos campos
     */
    public function except(array $keys): array
    {
        $results = $this->all();
        foreach ($keys as $key) {
            unset($results[$key]);
        }
        return $results;
    }
    
    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->cookies;
        return $this->cookies[$key] ?? $default;
    }
    
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }
    
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function setUser(?array $user): void
    {
        $this->user = $user;
    }

    public function user(): ?array
    {
        return $this->user;
    }
}
