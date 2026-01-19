<?php
namespace App\Modules\Auth\Validators;

class LoginValidator
{
    private array $rules = [
        'email' => ['required', 'email', 'max:255'],
        'password' => ['required', 'min:6', 'max:100'],
    ];
    
    private array $messages = [
        'email.required' => 'El correo es obligatorio',
        'email.email' => 'Formato de correo inválido',
        'password.required' => 'La contraseña es obligatoria',
    ];
    
    public function validate(array $data): array
    {
        $errors = [];
        
        foreach ($this->rules as $field => $rules) {
            $value = $data[$field] ?? null;
            foreach ($rules as $rule) {
                $error = $this->checkRule($field, $value, $rule);
                if ($error) {
                    $errors[$field][] = $error;
                    break;
                }
            }
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    private function checkRule(string $field, mixed $value, string $rule): ?string
    {
        $params = [];
        if (str_contains($rule, ':')) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }
        
        $passed = match ($rule) {
            'required' => !empty($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => strlen((string)$value) >= ($params[0] ?? 0),
            'max' => strlen((string)$value) <= ($params[0] ?? 0),
            default => true,
        };
        
        return $passed ? null : ($this->messages["{$field}.{$rule}"] ?? "Campo inválido");
    }
}