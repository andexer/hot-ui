<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Helper for nested Hotfire components.
 * 
 * Provides utilities for:
 * - Stable keys for nested components
 * - Isolated re-render of nested components
 * - Component hierarchy management
 */
final class NestedComponentHelper
{
    /**
     * Generate a stable key for a nested component.
     * 
     * @param string $parentKey Parent component key
     * @param string $childName Child component name
     * @param string|null $uniqueId Optional unique identifier
     * @return string Stable component key
     */
    public static function generateKey(string $parentKey, string $childName, ?string $uniqueId = null): string
    {
        if ($uniqueId !== null) {
            return sprintf('%s.%s-%s', $parentKey, $childName, $uniqueId);
        }
        
        return sprintf('%s.%s', $parentKey, $childName);
    }

    /**
     * Parse a component key to extract hierarchy.
     * 
     * @param string $key Component key
     * @return array{parent: string|null, child: string|null, uniqueId: string|null}
     */
    public static function parseKey(string $key): array
    {
        $parts = explode('.', $key);
        
        if (count($parts) === 1) {
            $child = $parts[0];
            $uniqueId = null;
            
            // Check if child has a unique ID suffix
            if (str_contains($child, '-')) {
                $childParts = explode('-', $child);
                $child = $childParts[0];
                $uniqueId = implode('-', array_slice($childParts, 1));
            }
            
            return [
                'parent' => null,
                'child' => $child,
                'uniqueId' => $uniqueId,
            ];
        }

        $lastPart = $parts[count($parts) - 1];
        $child = $lastPart;
        $uniqueId = null;
        
        // Check if last part has a unique ID suffix
        if (str_contains($lastPart, '-')) {
            $childParts = explode('-', $lastPart);
            $child = $childParts[0];
            $uniqueId = implode('-', array_slice($childParts, 1));
        }
        
        $parent = implode('.', array_slice($parts, 0, -1));
        
        return [
            'parent' => $parent,
            'child' => $child,
            'uniqueId' => $uniqueId,
        ];
    }

    /**
     * Check if a component key is nested.
     * 
     * @param string $key Component key
     * @return bool Whether the key represents a nested component
     */
    public static function isNested(string $key): bool
    {
        return str_contains($key, '.');
    }

    /**
     * Get the root component key from a nested key.
     * 
     * @param string $key Component key
     * @return string Root component key
     */
    public static function getRootKey(string $key): string
    {
        $parts = explode('.', $key);
        return $parts[0];
    }
}
