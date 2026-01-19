<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Response
 * ============================================================================
 * Archivo: src/Core/Response.php
 * Descripción: Encapsula la respuesta HTTP
 * ============================================================================
 */

namespace App\Core;

class Response
{
    private mixed $content;
    private int $statusCode;
    private array $headers = [];
    
    public function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
    
    public static function json(array $data, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new self(json_encode($data, JSON_UNESCAPED_UNICODE), $statusCode, $headers);
    }
    
    public static function success(mixed $data = null, string $message = 'Success'): self
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
    
    public static function error(string $message, int $statusCode = 400, ?array $errors = null, ?string $code = null): self
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if ($code !== null) {
            $response['code'] = $code;
        }
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return self::json($response, $statusCode);
    }
    
    public static function validationError(array $errors, string $message = 'Error de validación'): self
    {
        return self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
    
    public static function notFound(string $message = 'Recurso no encontrado'): self
    {
        return self::error($message, 404, null, 'NOT_FOUND');
    }
    
    public static function unauthorized(string $message = 'No autenticado'): self
    {
        return self::error($message, 401, null, 'UNAUTHORIZED');
    }
    
    public static function forbidden(string $message = 'Sin permisos'): self
    {
        return self::error($message, 403, null, 'FORBIDDEN');
    }
    
    public static function redirect(string $url, int $statusCode = 302): self
    {
        return (new self('', $statusCode))->header('Location', $url);
    }
    
    public static function view(string $template, array $data = [], int $statusCode = 200): self
    {
        // Usa el helper app() que definimos en helpers.php
        $content = app()->getView()->render($template, $data);
        return new self($content, $statusCode, ['Content-Type' => 'text/html; charset=utf-8']);
    }
    
    public static function download(string $filePath, ?string $name = null): self
    {
        if (!file_exists($filePath)) {
            return self::notFound('Archivo no encontrado');
        }
        
        $name = $name ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        
        $response = new self(file_get_contents($filePath));
        $response->header('Content-Type', $mimeType);
        $response->header('Content-Disposition', 'attachment; filename="' . $name . '"');
        $response->header('Content-Length', (string) filesize($filePath));
        
        return $response;
    }
    
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
    
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    public function getContent(): mixed
    {
        return $this->content;
    }
    
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    public function send(): void
    {
        // Enviar código de estado
        http_response_code($this->statusCode);
        
        // Enviar headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Enviar contenido
        if (is_string($this->content)) {
            echo $this->content;
        } elseif (is_array($this->content)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->content, JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function cookie(
        string $name, 
        string $value, 
        int $minutes = 0, 
        string $path = '/', 
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true
    ): self {
        $expire = $minutes > 0 ? time() + ($minutes * 60) : 0;
        setcookie($name, $value, $expire, $path, $domain ?? '', $secure, $httpOnly);
        return $this;
    }
    
    public function withCookie(string $name, string $value, int $minutes = 60 * 24 * 30): self
    {
        return $this->cookie($name, $value, $minutes);
    }
    
    public function forgetCookie(string $name): self
    {
        return $this->cookie($name, '', -1);
    }
}