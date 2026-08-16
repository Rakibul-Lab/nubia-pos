<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple, extensible input validator.
 *
 * Usage:
 *   $v = new Validator($request->all(), [
 *       'name'  => 'required|min:2|max:120',
 *       'email' => 'required|email|unique:users,email',
 *   ]);
 *   if ($v->fails()) { ... $v->errors() ... }
 *
 * @package App\Core
 */
final class Validator
{
    /** @var array<string,string[]> */
    private array $errors = [];

    /**
     * @param array<string,mixed>  $data
     * @param array<string,string> $rules
     * @param array<string,string> $labels
     */
    public function __construct(
        private array $data,
        private array $rules,
        private array $labels = []
    ) {
        $this->validate();
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * @return array<string,string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            return $messages[0] ?? null;
        }
        return null;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleset) {
            $rules = explode('|', $ruleset);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $param);
            }
        }
    }

    private function label(string $field): string
    {
        return $this->labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): void
    {
        $label   = $this->label($field);
        $isEmpty = $value === null || $value === '' || (is_array($value) && $value === []);

        switch ($rule) {
            case 'required':
                if ($isEmpty) {
                    $this->addError($field, "{$label} is required.");
                }
                break;

            case 'email':
                if (!$isEmpty && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$label} must be a valid email address.");
                }
                break;

            case 'numeric':
                if (!$isEmpty && !is_numeric($value)) {
                    $this->addError($field, "{$label} must be a number.");
                }
                break;

            case 'integer':
                if (!$isEmpty && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "{$label} must be an integer.");
                }
                break;

            case 'min':
                if (!$isEmpty && mb_strlen((string) $value) < (int) $param) {
                    $this->addError($field, "{$label} must be at least {$param} characters.");
                }
                break;

            case 'max':
                if (!$isEmpty && mb_strlen((string) $value) > (int) $param) {
                    $this->addError($field, "{$label} may not exceed {$param} characters.");
                }
                break;

            case 'min_val':
                if (!$isEmpty && (float) $value < (float) $param) {
                    $this->addError($field, "{$label} must be at least {$param}.");
                }
                break;

            case 'confirmed':
                if (($this->data[$field . '_confirmation'] ?? null) !== $value) {
                    $this->addError($field, "{$label} confirmation does not match.");
                }
                break;

            case 'unique':
                if (!$isEmpty && $param !== null) {
                    [$table, $column, $ignoreId] = array_pad(explode(',', $param), 3, null);
                    $sql    = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
                    $params = [$value];
                    if ($ignoreId !== null) {
                        $sql     .= ' AND id <> ?';
                        $params[] = $ignoreId;
                    }
                    if ((int) Database::getInstance()->scalar($sql, $params) > 0) {
                        $this->addError($field, "{$label} is already taken.");
                    }
                }
                break;

            case 'in':
                $allowed = explode(',', (string) $param);
                if (!$isEmpty && !in_array((string) $value, $allowed, true)) {
                    $this->addError($field, "{$label} is invalid.");
                }
                break;
        }
    }
}
