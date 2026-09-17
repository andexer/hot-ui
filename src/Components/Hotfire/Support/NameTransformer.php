<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

/**
 * Name transformer for component naming conventions.
 * 
 * Single Responsibility: Handles all transformations between different
 * naming conventions (camelCase, PascalCase, kebab-case, etc.) used
 * throughout the Hotfire component system.
 * 
 * This class follows the Single Responsibility Principle by focusing
 * exclusively on string transformations for naming conventions.
 */
final readonly class NameTransformer
{
    /**
     * Converts a string to PascalCase (UpperCamelCase).
     * 
     * @param string $string The string to convert
     * @return string The PascalCase version
     */
    public function toPascalCase(string $string): string
    {
        return str_replace([' ', '-', '_'], '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    /**
     * Converts a string to kebab-case (lowercase with hyphens).
     * 
     * @param string $string The string to convert
     * @return string The kebab-case version
     */
    public function toKebabCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }

    /**
     * Converts a string to camelCase (lowerCamelCase).
     * 
     * @param string $string The string to convert
     * @return string The camelCase version
     */
    public function toCamelCase(string $string): string
    {
        return lcfirst($this->toPascalCase($string));
    }

    /**
     * Converts a string to snake_case (lowercase with underscores).
     * 
     * @param string $string The string to convert
     * @return string The snake_case version
     */
    public function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * Validates if a string is a valid PHP namespace part.
     * 
     * @param string $part The namespace part to validate
     * @return bool True if valid, false otherwise
     */
    public function isValidNamespacePart(string $part): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $part) === 1;
    }

    /**
     * Validates if a string is a valid full namespace.
     * 
     * @param string $namespace The namespace to validate
     * @return bool True if valid, false otherwise
     */
    public function isValidNamespace(string $namespace): bool
    {
        $namespace = trim($namespace, '\\');
        if ($namespace === '') {
            return false;
        }

        foreach (explode('\\', $namespace) as $part) {
            if (! $this->isValidNamespacePart($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates if a string is a valid PHP identifier.
     * 
     * @param string $identifier The identifier to validate
     * @return bool True if valid, false otherwise
     */
    public function isValidIdentifier(string $identifier): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) === 1;
    }

    /**
     * Normalizes a component name by converting dots to slashes.
     * 
     * @param string $name The component name to normalize
     * @return string The normalized name
     */
    public function normalizeComponentName(string $name): string
    {
        return str_replace('.', '/', $name);
    }

    /**
     * Splits a component name into segments.
     * 
     * @param string $name The component name to split
     * @return array<int, string> The segments
     */
    public function splitComponentName(string $name): array
    {
        $normalized = $this->normalizeComponentName($name);
        return explode('/', $normalized);
    }
}
