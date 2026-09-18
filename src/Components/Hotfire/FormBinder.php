<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Helper for advanced form binding in Hotfire components.
 * 
 * Handles complex form field types:
 * - Radios (single selection)
 * - Checkboxes (grouped arrays)
 * - Arrays (multiple values)
 * - Multi-selects
 * - Nested values (user.name, address.city)
 * - Field errors
 */
final class FormBinder
{
    /**
     * Gets the value for a form field, supporting nested dot notation.
     * 
     * @param Component $component The component instance
     * @param string $field Field name (supports dot notation like "user.name")
     * @return mixed The field value
     */
    public static function value(Component $component, string $field): mixed
    {
        if (str_contains($field, '.')) {
            return self::getNestedValue($component, $field);
        }

        return $component->{$field} ?? null;
    }

    /**
     * Checks if a checkbox or radio is checked.
     * 
     * @param Component $component The component instance
     * @param string $field Field name
     * @param string|int|bool $value Value to check against
     * @return bool Whether the field is checked
     */
    public static function checked(Component $component, string $field, string|int|bool $value): bool
    {
        $fieldValue = self::value($component, $field);

        if (is_array($fieldValue)) {
            return in_array($value, $fieldValue, true);
        }

        return $fieldValue === $value;
    }

    /**
     * Checks if a checkbox is checked for array-based checkboxes.
     * 
     * @param Component $component The component instance
     * @param string $field Field name
     * @param string $value Value to check against
     * @return bool Whether the checkbox is checked
     */
    public static function checkboxChecked(Component $component, string $field, string $value): bool
    {
        $fieldValue = self::value($component, $field);

        if (is_array($fieldValue)) {
            return in_array($value, $fieldValue, true);
        }

        return false;
    }

    /**
     * Gets the error message for a field.
     * 
     * @param Component $component The component instance
     * @param string $field Field name
     * @return string|null Error message or null if no error
     */
    public static function error(Component $component, string $field): ?string
    {
        if (! isset($component->errors) || ! is_array($component->errors)) {
            return null;
        }

        return $component->errors[$field] ?? null;
    }

    /**
     * Checks if a field has an error.
     * 
     * @param Component $component The component instance
     * @param string $field Field name
     * @return bool Whether the field has an error
     */
    public static function hasError(Component $component, string $field): bool
    {
        return self::error($component, $field) !== null;
    }

    /**
     * Gets all error messages.
     * 
     * @param Component $component The component instance
     * @return array<string, string> Field errors
     */
    public static function errors(Component $component): array
    {
        return $component->errors ?? [];
    }

    /**
     * Checks if the form has any errors.
     * 
     * @param Component $component The component instance
     * @return bool Whether the form has errors
     */
    public static function hasErrors(Component $component): bool
    {
        return ! empty(self::errors($component));
    }

    /**
     * Gets a nested value using dot notation.
     * 
     * @param Component $component The component instance
     * @param string $path Dot-notation path (e.g., "user.name")
     * @return mixed The nested value
     */
    public static function getNestedValue(Component $component, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $component;

        foreach ($keys as $key) {
            if (is_array($value)) {
                $value = $value[$key] ?? null;
            } elseif (is_object($value) && isset($value->{$key})) {
                $value = $value->{$key};
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Sets a nested value using dot notation.
     * 
     * @param Component $component The component instance
     * @param string $path Dot-notation path (e.g., "user.name")
     * @param mixed $value Value to set
     * @return void
     */
    public static function setNestedValue(Component $component, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $lastKey = array_pop($keys);
        if ($lastKey === null || $keys === []) {
            return;
        }

        $root = array_shift($keys);
        if ($root === null || $root === '') {
            return;
        }

        $current = $component->{$root} ?? [];
        self::setNested($current, [...$keys, $lastKey], $value);
        $component->{$root} = $current;
    }

    /**
     * @param list<string> $keys
     */
    private static function setNested(mixed &$target, array $keys, mixed $value): void
    {
        $key = array_shift($keys);
        if ($key === null) {
            return;
        }

        if ($keys === []) {
            if (is_array($target)) {
                $target[$key] = $value;
                return;
            }
            if (is_object($target)) {
                $target->{$key} = $value;
                return;
            }
            $target = [$key => $value];
            return;
        }

        if (is_array($target)) {
            $target[$key] ??= [];
            self::setNested($target[$key], $keys, $value);
            return;
        }

        if (is_object($target)) {
            if (! isset($target->{$key}) || (! is_array($target->{$key}) && ! is_object($target->{$key}))) {
                $target->{$key} = [];
            }
            self::setNested($target->{$key}, $keys, $value);
            return;
        }

        $target = [$key => []];
        self::setNested($target[$key], $keys, $value);
    }
}
