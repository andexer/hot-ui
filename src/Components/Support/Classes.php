<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Zero-dependency conditional CSS class resolver (the plain-PHP counterpart of
 * Blade's @class / Arr::toCssClasses).
 *
 * Numeric entries pass through as classes when their value is truthy; string
 * keys are emitted only when their value is truthy; nested arrays recurse.
 * The result is a plain, un-escaped space-separated string (escaping is the
 * caller's concern, matching how other class expressions flow into props()).
 */
final class Classes
{
    /**
     * @param array<array-key, mixed> $classList
     */
    public static function render(array $classList): string
    {
        $parts = [];

        foreach ($classList as $key => $value) {
            if (is_array($value)) {
                $nested = self::render($value);
                if ($nested !== '') {
                    $parts[] = $nested;
                }
                continue;
            }
            if ((bool) $value) {
                $parts[] = is_int($key) ? (string) $value : (string) $key;
            }
        }

        return implode(' ', $parts);
    }
}