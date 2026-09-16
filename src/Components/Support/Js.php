<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Safely serialises a PHP value into a JavaScript literal, suitable for
 * embedding inside double-quoted HTML attributes.
 *
 * JSON_HEX_* flags escape <, >, ', & and in-value " as \uXXXX sequences; the
 * structural quotes are then rewritten too, so the output is valid JavaScript
 * AND can never terminate an HTML attribute.
 */
final class Js
{
    private const FLAGS = JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP
        | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;

    /**
     * @param mixed $value Any json_encode-able value plus enums, dates and Stringables.
     */
    public static function from(mixed $value): string
    {
        $value = match (true) {
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \UnitEnum => $value->name,
            $value instanceof \DateTimeInterface => $value->format(DATE_ATOM),
            $value instanceof Stringable => (string) $value,
            default => $value,
        };

        $json = json_encode($value, self::FLAGS);

        return str_replace('"', '\\u0022', $json);
    }
}
