<?php

namespace App\Core;

use Dotenv\Dotenv;

class Application
{
    protected static ?Application $instance = null;
    protected Router $router;
    protected Database $database;
    protected ?View $view = null;
    protected string $basePath;

    private function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->loadEnvironment();
        $this->configureErrorHandling();
        
        // 1. Inicializar Router
        $this->router = new Router();
        
        // 2. Inicializar View Engine
        // Verificamos si existe la carpeta para evitar errores si aún no la creas
        $viewsPath = $this->basePath . '/src/Views';
        if (!is_dir($viewsPath)) {
            // Fallback temporal si no existe la carpeta real
            $viewsPath = $this->basePath; 
        }
        $this->view = new View($viewsPath);
    }

    /**
     * Singleton Instance
     * CORRECCIÓN PHP 8.4: Se agrega '?' antes de string para hacerlo explícitamente nullable
     */
    public static function getInstance(?string $basePath = null): Application
    {
        if (self::$instance === null) {
            // Si basePath es null, intentamos adivinarlo (un nivel arriba de public)
            $path = $basePath ?? dirname(__DIR__, 2); 
            self::$instance = new self($path);
        }
        return self::$instance;
    }

    protected function loadEnvironment(): void
    {
        if (file_exists($this->basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($this->basePath);
            $dotenv->safeLoad();
        }
    }

    protected function configureErrorHandling(): void
    {
        error_reporting(E_ALL);
        $debug = isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';
        
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        // Aseguramos que el directorio de logs exista
        $logPath = $this->basePath . '/storage/logs';
        if (!is_dir($logPath)) {
             @mkdir($logPath, 0755, true);
        }
        ini_set('error_log', $logPath . '/php_error.log');
    }

    public function run(): void
    {
        try {
            // 1. Capturar la Petición
            $request = Request::capture();
            
            // 2. Despachar al Router
            $response = $this->router->dispatch($request);
            
            // 3. Enviar respuesta
            $response->send();

        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    protected function handleException(\Exception $e): void
    {
        $code = $e->getCode() ?: 500;
        if (!is_int($code) || $code < 100 || $code > 599) {
            $code = 500;
        }

        http_response_code($code);
        
        if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            echo "<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #f87171;'>";
            echo "<h1 style='margin-top:0'>Error del Sistema</h1>";
            echo "<p><strong>Mensaje:</strong> {$e->getMessage()}</p>";
            echo "<p><strong>Archivo:</strong> {$e->getFile()} (Línea {$e->getLine()})</p>";
            echo "<pre style='background: white; padding: 10px; overflow: auto;'>{$e->getTraceAsString()}</pre>";
            echo "</div>";
        } else {
            echo "<h1>500 - Error Interno del Servidor</h1>";
            echo "<p>Ha ocurrido un error inesperado.</p>";
        }
    }

    // Getters
    public function getRouter(): Router { return $this->router; }
    public function getBasePath(): string { return $this->basePath; }
    public function getView(): View { return $this->view; }
}