<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Post-render pass that converts declarative hot:* attributes in component
 * views into the data-hot-* the JS driver reads:
 *
 *   <button hot:click="increment">   ->   <button data-hot-click="increment">
 *   <input hot:model="count">        ->   <input data-hot-model="count">
 *   <div hot:poll="2000">            ->   <div data-hot-poll="2000">
 *
 * Only directives declared in Config are rewritten, so unknown hot:* attributes
 * stay put (hosts can ship their own semantics on top).
 */
final class HtmlTransform
{
    public static function apply(string $html, ?Config $config = null): string
    {
        $directives = ($config ?? Config::shared())->directives();
        if ($directives === []) {
            return $html;
        }

        $names = implode('|', array_map(static fn (string $name): string => preg_quote($name, '/'), array_keys($directives)));
        $pattern = '/\s(hot:)('.$names.')\s*=\s*("([^"]*)"|\'([^\']*)\')/';

        return preg_replace_callback(
            $pattern,
            static function (array $match) use ($directives): string {
                $attribute = $directives[$match[2]] ?? null;
                if ($attribute === null) {
                    return $match[0];
                }
                $value = $match[4] !== '' ? $match[4] : ($match[5] ?? '');
                $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                return ' data-hot-'.$attribute.'="'.$escaped.'"';
            },
            $html,
        ) ?? $html;
    }
}