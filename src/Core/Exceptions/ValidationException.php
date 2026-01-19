<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Validation Exception
 * ============================================================================
 * Archivo: src/Core/Exceptions/ValidationException.php
 * ============================================================================
 */

namespace App\Core\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;
    
    public function __construct(array $errors, string $message = 'Error de validación')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public static function withErrors(array $errors, string $message = 'Error de validación'): self
    {
        return new self($errors, $message);
    }
}