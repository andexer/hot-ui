<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Post-render pass that converts <hot:*> component tags and hot:* attributes
 * into the format the JS driver reads:
 *
 *   <hot:counter init="5">      ->   <div data-hot-component="counter" data-hot-props='{"init":5}'>
 *   <hot:counter :init="5">     ->   <div data-hot-component="counter" data-hot-props='{"init":5}'>
 *   <button hot:click="...">    ->   <button data-hot-click="...">
 *   <input hot:model="count">     ->   <input data-hot-model="count">
 *   <div hot:poll="2000">        ->   <div data-hot-poll="2000">
 *
 * Simple syntax: <hot:counter init="5" /> for component calls
 */
final class HtmlTransform
{
    public static function apply(string $html, ?Config $config = null): string
    {
        $directives = ($config ?? Config::shared())->directives();
        
        // Convert <hot:component-name ...> to data-hot-component div
        $html = preg_replace_callback(
            '/<hot:([a-z][a-z0-9-]*)\b([^>]*)>/i',
            static function (array $match): string {
                $componentName = $match[1];
                $attributes = $match[2];
                
                // Parse attributes into props object
                $props = [];
                if ($attributes !== '') {
                    // Match :prop="value" or prop="value"
                    preg_match_all('/([a-z][a-z0-9-]*)\s*=\s*("([^"]*)"|\'([^\']*)\')/i', $attributes, $matches);
                    for ($i = 0; $i < count($matches[1]); $i++) {
                        $key = $matches[1][$i];
                        $value = $matches[2][$i] !== '' ? $matches[2][$i] : ($matches[3][$i] ?? '');
                        // Remove leading colon if present (for :prop syntax)
                        $key = ltrim($key, ':');
                        $props[$key] = $value;
                    }
                }
                
                $propsJson = json_encode($props, JSON_THROW_ON_ERROR);
                $propsJson = htmlspecialchars($propsJson, ENT_QUOTES, 'UTF-8');
                
                return '<div data-hot-component="'.$componentName.'" data-hot-props=\''.$propsJson.'\'>';
            },
            $html,
        );
        
        // Remove closing tags (self-closing components)
        $html = preg_replace('/<\/hot:[a-z0-9-]+>/i', '', $html);
        
        // Convert hot:* attributes to data-hot-*
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