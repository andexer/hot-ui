<?php

declare(strict_types=1);

namespace Components\Reactivity;

/**
 * Post-render pass that converts declarative hot:* attributes in component
 * views into the data-hot-* the JS driver reads:
 *
 *   <button hot:click="increment">   →   <button data-hot-click="increment">
 *   <input hot:model="count">        →   <input data-hot-model="count">
 *   <div hot:poll="2000">            →   <div data-hot-poll="2000">
 *
 * Only known directives are rewritten, so unknown hot:* attributes stay put
 * (hosts can ship their own semantics on top).
 */
final class HtmlTransform
{
    /** @var array<string, string> Directive name → data-attribute suffix. */
    private const MAP = [
        'click' => 'click',
        'model' => 'model',
        'poll' => 'poll',
        'key' => 'key',
        'change' => 'change',
    ];

    public static function apply(string $html): string
    {
        $pattern = '/\s(hot:)([\w-]+)\s*=\s*("([^"]*)"|\'([^\']*)\')/';

        return preg_replace_callback(
            $pattern,
            static function (array $match): string {
                $directive = $match[2];
                if (! isset(self::MAP[$directive])) {
                    return $match[0];
                }
                $value = $match[4] !== '' ? $match[4] : ($match[5] ?? '');
                $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                return ' data-hot-'.self::MAP[$directive].'="'.$escaped.'"';
            },
            $html,
        ) ?? $html;
    }
}