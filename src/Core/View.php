<?php
/**
 * ============================================================================
 * SHALOM DENTAL - View Engine
 * ============================================================================
 * Archivo: src/Core/View.php
 * Descripción: Motor de plantillas PHP simple con Layouts y Secciones
 * ============================================================================
 */

namespace App\Core;

use Exception;

class View
{
    private string $basePath;
    private array $shared = [];
    private ?string $layout = null;
    private array $sections = [];
    private ?string $currentSection = null;
    
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }
    
    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }
    
    public function shareMany(array $data): void
    {
        $this->shared = array_merge($this->shared, $data);
    }
    
    public function render(string $template, array $data = []): string
    {
        $filePath = $this->resolvePath($template);
        
        if (!file_exists($filePath)) {
            throw new Exception("View not found: {$template} en {$filePath}");
        }
        
        // Combinar datos compartidos con datos específicos
        $data = array_merge($this->shared, $data);
        
        // Extraer variables para la vista (convierte array keys en variables $key)
        extract($data);
        
        // Capturar salida
        ob_start();
        
        try {
            include $filePath;
            $content = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        
        // Si la vista definió un layout (usando $this->extend), renderizamos el layout ahora
        if ($this->layout !== null) {
            $layout = $this->layout;
            $this->layout = null;
            
            // CORRECCIÓN: Solo usar el contenido capturado si NO se definió una sección 'content' explícita
            // Esto evita que los espacios en blanco fuera de las secciones sobrescriban tu formulario
            if (empty($this->sections['content'])) {
                $this->sections['content'] = $content;
            }
            
            // Renderizamos el layout padre
            return $this->render($layout, $data);
        }
        
        return $content;
    }
    
    public function exists(string $template): bool
    {
        return file_exists($this->resolvePath($template));
    }
    
    private function resolvePath(string $template): string
    {
        // Convertir notación de puntos (auth.login) a ruta (auth/login)
        $template = str_replace('.', '/', $template);
        return $this->basePath . '/' . $template . '.php';
    }
    
    // =========================================================================
    // MÉTODOS PARA USO DENTRO DE LAS VISTAS (.php)
    // =========================================================================
    
    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }
    
    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection(): void
    {
        if ($this->currentSection !== null) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }
    
    public function include(string $template, array $data = []): string
    {
        // Create a new View instance to avoid conflicts with current rendering context
        $newView = new self($this->basePath);
        $newView->shareMany($this->shared);
        return $newView->render($template, $data);
    }
    
    public function component(string $name, array $props = []): void
    {
        // Asume que los componentes están en src/Views/components
        echo $this->include('components/' . $name, $props);
    }
    
    // =========================================================================
    // HELPERS DE VISTA
    // =========================================================================
    
    public function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public function e(?string $value): string
    {
        return $this->escape($value);
    }
    
    public function json(mixed $data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    
    public function class(array $classes): string
    {
        $result = [];
        foreach ($classes as $class => $condition) {
            if (is_int($class)) {
                $result[] = $condition;
            } elseif ($condition) {
                $result[] = $class;
            }
        }
        return implode(' ', $result);
    }

    public function active(string $path, string $class = 'active'): string
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return $currentPath === $path ? $class : '';
    }

    public function activeStartsWith(string $prefix, string $class = 'active'): string
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return str_starts_with($currentPath, $prefix) ? $class : '';
    }
}
