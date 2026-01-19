<?php
/**
 * ============================================================================
 * SHALOM DENTAL - HTTP Exception
 * ============================================================================
 * Archivo: src/Core/Exceptions/HttpException.php
 * ============================================================================
 */

namespace Shalom\Core\Exceptions;

use Exception;

class HttpException extends Exception
{
    protected int $statusCode;
    protected array $headers;
    
    public function __construct(
        int $statusCode = 500, 
        string $message = '', 
        array $headers = [], 
        ?Exception $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        
        parent::__construct($message, 0, $previous);
    }
    
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
