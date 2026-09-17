<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Safely serialises a PHP value into a JavaScript literal, suitable for
 * embedding inside double-quoted HTML attributes.
 *
 * JSON_HEX_* flags escape <, >, ', & and in-value " as \uXXXX sequences; the
 * structural quotes are then rewritten too, so the output is valid JavaScript
 * AND can never terminate an HTML attribute. Raw U+2028/U+2029 (unescaped by
 * JSON_UNESCAPED_UNICODE) are additionally rewritten to \uXXXX escapes so the
 * literal can never break an inline <script> literal boundary.
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

        return str_replace(
            ["\u{2028}", "\u{2029}", '"'],
            ['\\u2028', '\\u2029', '\\u0022'],
            $json,
        );
    }
}
