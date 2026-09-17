<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

use Components\Hotfire\Exception\InvalidComponentPropertyException;

/**
 * Property validator for component properties.
 * 
 * Single Responsibility: Validates component property names and ensures
 * they don't conflict with reserved names or follow invalid patterns.
 * 
 * This class follows the Single Responsibility Principle by focusing
 * exclusively on property validation logic.
 */
final readonly class PropertyValidator
{
    /** Reserved property names that cannot be used in components. */
    private const RESERVED_PROPERTIES = ['view', 'component'];

    /**
     * Validates a single property name.
     * 
     * @param string $property The property name to validate
     * @return bool True if valid, false otherwise
     */
    public function isValidProperty(string $property): bool
    {
        if ($property === '') {
            return false;
        }

        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $property)) {
            return false;
        }

        if (in_array($property, self::RESERVED_PROPERTIES, true)) {
            return false;
        }

        return true;
    }

    /**
     * Validates and parses a comma-separated list of properties.
     * 
     * @param string $raw The raw property string (comma-separated)
     * @return array<int, string> The validated property names
     * @throws InvalidComponentPropertyException If a property is invalid
     */
    public function parseProperties(string $raw): array
    {
        $properties = [];
        $parts = preg_split('/\s*,\s*/', trim($raw)) ?: [];

        foreach ($parts as $prop) {
            if ($prop === '') {
                continue;
            }

            if (! $this->isValidProperty($prop)) {
                throw InvalidComponentPropertyException::invalidName($prop);
            }

            $properties[] = $prop;
        }

        return array_values($properties);
    }

    /**
     * Checks if a property name is reserved.
     * 
     * @param string $property The property name to check
     * @return bool True if reserved, false otherwise
     */
    public function isReserved(string $property): bool
    {
        return in_array($property, self::RESERVED_PROPERTIES, true);
    }

    /**
     * Gets the list of reserved property names.
     * 
     * @return array<int, string> The reserved property names
     */
    public function getReservedProperties(): array
    {
        return self::RESERVED_PROPERTIES;
    }
}
