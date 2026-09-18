<?php

declare(strict_types=1);

namespace Components\Hotfire\Validation;

/**
 * Trait for validating requests in Hotfire components.
 * 
 * Provides integration with CodeIgniter 4 validation system:
 * - rules() method for defining validation rules
 * - validate() for validating with full rules
 * - validateOnly() for validating specific fields
 * - Custom error messages
 * - Serializable error bag
 */
trait ValidatesRequests
{
    /**
     * Validation errors.
     * 
     * @var array<string, string>
     */
    public array $errors = [];

    /**
     * Custom error messages.
     * 
     * @var array<string, string>
     */
    public array $customMessages = [];

    /**
     * Defines validation rules for the component.
     * 
     * Override this method to define your validation rules.
     * 
     * @return array<string, string|array<string>> Validation rules
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Validates all properties against the defined rules.
     * 
     * @return bool Whether validation passed
     */
    public function validate(): bool
    {
        $rules = $this->rules();
        if ($rules === []) {
            return true;
        }

        $this->errors = [];
        $state = $this->state();

        foreach ($rules as $field => $fieldRules) {
            $value = $state[$field] ?? null;
            // Convert string rules to array if needed
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $fieldErrors = $this->validateField($field, $value, $fieldRules);
            
            if ($fieldErrors !== []) {
                foreach ($fieldErrors as $error) {
                    $this->errors[$field] = $error;
                }
            }
        }

        return $this->errors === [];
    }

    /**
     * Validates only specific fields.
     * 
     * @param array<int, string> $fields Fields to validate
     * @return bool Whether validation passed
     */
    public function validateOnly(array $fields): bool
    {
        $rules = $this->rules();
        if ($rules === []) {
            return true;
        }

        $this->errors = [];
        $state = $this->state();

        foreach ($fields as $field) {
            if (! isset($rules[$field])) {
                continue;
            }

            $value = $state[$field] ?? null;
            $fieldRules = is_array($rules[$field]) ? $rules[$field] : explode('|', $rules[$field]);
            $fieldErrors = $this->validateField($field, $value, $fieldRules);
            
            if ($fieldErrors !== []) {
                foreach ($fieldErrors as $error) {
                    $this->errors[$field] = $error;
                }
            }
        }

        return $this->errors === [];
    }

    /**
     * Validates a single field against its rules.
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array<int, string> $rules Validation rules
     * @return array<int, string> Error messages
     */
    private function validateField(string $field, mixed $value, array $rules): array
    {
        $errors = [];

        foreach ($rules as $rule) {
            // Parse rule with parameters (e.g., "min:3")
            if (str_contains($rule, ':')) {
                [$ruleName, $parameter] = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $parameter = null;
            }

            if (! $this->passesRule($ruleName, $field, $value, $parameter)) {
                $errors[] = $this->getErrorMessage($field, $ruleName, $parameter);
            }
        }

        return $errors;
    }

    /**
     * Checks if a field passes a validation rule.
     * 
     * @param string $rule Rule name
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string|null $parameter Rule parameter
     * @return bool Whether the rule passed
     */
    private function passesRule(string $rule, string $field, mixed $value, ?string $parameter): bool
    {
        return match ($rule) {
            'required' => $value !== null && $value !== '',
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => is_numeric($value) && (float) $value >= (float) ($parameter ?? 0),
            'max' => is_numeric($value) && (float) $value <= (float) ($parameter ?? PHP_INT_MAX),
            'min_length' => is_string($value) && strlen($value) >= (int) ($parameter ?? 0),
            'max_length' => is_string($value) && strlen($value) <= (int) ($parameter ?? PHP_INT_MAX),
            'numeric' => is_numeric($value),
            'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            default => true, // Unknown rules pass by default
        };
    }

    /**
     * Gets the error message for a rule.
     * 
     * @param string $field Field name
     * @param string $rule Rule name
     * @param string|null $parameter Rule parameter
     * @return string Error message
     */
    private function getErrorMessage(string $field, string $rule, ?string $parameter): string
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }

        if (isset($this->customMessages[$rule])) {
            return $this->customMessages[$rule];
        }

        return match ($rule) {
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'min' => "The {$field} must be at least {$parameter}.",
            'max' => "The {$field} may not be greater than {$parameter}.",
            'min_length' => "The {$field} must be at least {$parameter} characters.",
            'max_length' => "The {$field} may not be greater than {$parameter} characters.",
            'numeric' => "The {$field} must be a number.",
            'integer' => "The {$field} must be an integer.",
            'url' => "The {$field} must be a valid URL.",
            default => "The {$field} is invalid.",
        };
    }

    /**
     * Gets the error bag as a serializable array.
     * 
     * @return array<string, string> Error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Checks if the component has any validation errors.
     * 
     * @return bool Whether there are errors
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Gets the first error message.
     * 
     * @return string|null First error or null
     */
    public function firstError(): ?string
    {
        if ($this->errors === []) {
            return null;
        }

        return reset($this->errors);
    }

    /**
     * Clears all validation errors.
     * 
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
}
