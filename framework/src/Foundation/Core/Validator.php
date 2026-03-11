<?php

namespace Foundation\Core;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];

    /**
     * @param array $data  Datos a validar, por ejemplo $_POST.
     * @param array $rules Reglas de validación, por ejemplo:
     *                     ['email' => 'required|email', 'password' => 'required|min:6']
     */
    public function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    /**
     * Ejecuta la validación según las reglas establecidas.
     */
    public function validate(): void
    {

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = isset($this->data[$field]) ? trim($this->data[$field]) : null;

            foreach ($rules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $this->addError($field, "El campo {$field} es requerido.");
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres.");
                    }
                }
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "El campo {$field} debe tener un formato de correo válido.");
                }
            }
        }
    }

    /**
     * Agrega un error al array de errores.
     */
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Retorna true si existen errores.
     */
    public function fails(): bool
    {
        $this->validate();
        return !empty($this->errors);
    }

    /**
     * Devuelve los errores de validación.
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
