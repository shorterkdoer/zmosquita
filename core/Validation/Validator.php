<?php

declare(strict_types=1);

namespace ZMosquita\Core\Validation;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, array<int, string>> $rules
     * @param array<string, string> $labels
     * @return array{valid:bool, errors:array<string, array<int, string>>, cleaned:array<string, mixed>}
     */
    public function validate(array $data, array $rules, array $labels = []): array
    {
        $errors = [];
        $cleaned = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $label = $labels[$field] ?? $this->humanize($field);

            $nullable = in_array('nullable', $fieldRules, true);
            $required = in_array('required', $fieldRules, true);

            if ($required && $this->isEmpty($value)) {
                $errors[$field][] = "El campo {$label} es obligatorio.";
                continue;
            }

            if ($nullable && $this->isEmpty($value)) {
                $cleaned[$field] = null;
                continue;
            }

            if (!$required && $this->isEmpty($value)) {
                $cleaned[$field] = $value;
                continue;
            }

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' || $rule === 'nullable') {
                    continue;
                }

                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field][] = "El campo {$label} debe ser numérico.";
                }

                if ($rule === 'integer' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$field][] = "El campo {$label} debe ser entero.";
                }

                if ($rule === 'email' && filter_var((string)$value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field][] = "El campo {$label} debe ser un email válido.";
                }

                if ($rule === 'date' && strtotime((string)$value) === false) {
                    $errors[$field][] = "El campo {$label} debe ser una fecha válida.";
                }

                if (str_starts_with($rule, 'max:')) {
                    $limit = (int)substr($rule, 4);
                    if (mb_strlen((string)$value) > $limit) {
                        $errors[$field][] = "El campo {$label} no debe superar {$limit} caracteres.";
                    }
                }

                if (str_starts_with($rule, 'min:')) {
                    $limit = (int)substr($rule, 4);
                    if (mb_strlen((string)$value) < $limit) {
                        $errors[$field][] = "El campo {$label} debe tener al menos {$limit} caracteres.";
                    }
                }
            }

            $cleaned[$field] = $value;
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'cleaned' => $cleaned,
        ];
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    private function humanize(string $value): string
    {
        return strtolower(str_replace('_', ' ', $value));
    }
}