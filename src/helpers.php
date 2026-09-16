<?php

declare(strict_types=1);

/**
 * Global template and development helpers. Intentionally un-namespaced so
 * they are callable directly inside component templates and host apps.
 */

use Components\Support\Js;
use Components\Support\RenderContext;
use Components\Support\Slot;
use Components\Ui;

/**
 * Shared library instance backing the ui() call style.
 */
function ui(): Ui
{
    return Ui::shared();
}

/**
 * Renders a view file that uses the <ui:…> tag syntax.
 *
 * So controllers can page with Blade/Flux-like markup instead of manual
 * ob_start()/ob_get_clean() buffers. Inside CI4 the file is looked up under
 * APPPATH.'Views' automatically; pass $basePath otherwise.
 *
 *   ui_view('home/dashboard', ['posts' => $posts]);
 *
 * @param array<string, mixed> $data
 * @param string|null          $basePath Directory that contains the view file.
 */
function ui_view(string $template, array $data = [], ?string $basePath = null): string
{
    $basePath ??= defined('APPPATH') ? APPPATH.'Views' : null;

    return \Components\Ui::shared()->view($template, $data, $basePath);
}

/**
 * HTML-escapes any scalar or Stringable value.
 *
 * @param mixed $value        Value to escape; cast to string first.
 * @param bool  $doubleEncode Whether existing entities get re-escaped.
 */
function e(mixed $value, bool $doubleEncode = true): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
}

/**
 * Serialises a value into an attribute-safe JavaScript literal.
 *
 * @param mixed $value Any json_encode-able value plus enums, dates, Stringables.
 */
function js(mixed $value): string
{
    return Js::from($value);
}

/**
 * Component preamble: resolves declared props against the render context.
 *
 * Call as the FIRST statement of every component template:
 *
 *   <?php extract(props($__ctx, ['variant' => 'default'])); ?>
 *
 * After extraction the scope contains every declared prop (default applied
 * when absent), every named slot that was passed (as Slot instances), the
 * AttributeBag of undeclared caller data as $attributes, and $slot.
 *
 * @param RenderContext        $ctx      Active render context.
 * @param array<string, mixed> $defaults Declared prop names and their defaults.
 *
 * @return array<string, mixed> Values meant to be extract()ed.
 *
 * @throws InvalidArgumentException When a default collides with a reserved variable.
 */
function props(RenderContext $ctx, array $defaults = []): array
{
    $reserved = ['attributes', 'slot', 'slots', '__ctx'];
    $declared = [];
    $out = [];

    foreach ($defaults as $key => $default) {
        if (in_array($key, $reserved, true)) {
            throw new InvalidArgumentException(sprintf(
                'Prop [%s] collides with a reserved template variable.',
                $key,
            ));
        }

        $declared[] = $key;

        if (array_key_exists($key, $ctx->props)) {
            $out[$key] = $ctx->props[$key];
            continue;
        }

        $kebab = self_kebabize($key);
        if ($kebab !== $key && array_key_exists($kebab, $ctx->props)) {
            $out[$key] = $ctx->props[$kebab];
            continue;
        }

        $out[$key] = $default;
    }

    foreach ($ctx->slots as $name => $slot) {
        $out[$name] = $slot;
    }

    $excluded = [];
    foreach ($declared as $key) {
        $excluded[$key] = true;
        $excluded[self_kebabize($key)] = true;
    }

    $out['attributes'] = new Components\Support\AttributeBag(array_diff_key($ctx->props, $excluded));
    $out['slot'] = $ctx->slot;

    return $out;
}

/**
 * Component preamble for children consuming ancestor-published values.
 * Precedence: explicit call value > inherited shared value > declared default.
 *
 * @param RenderContext        $ctx      Active render context.
 * @param array<string, mixed> $defaults Declared prop names and their defaults.
 *
 * @return array<string, mixed>
 */
function aware(RenderContext $ctx, array $defaults = []): array
{
    $effective = $defaults;
    foreach ($defaults as $key => $default) {
        if (! array_key_exists($key, $ctx->props) && array_key_exists($key, $ctx->shared)) {
            $effective[$key] = $ctx->shared[$key];
        }
    }

    return props($ctx, $effective);
}

/**
 * Publishes values to descendant components rendered afterwards in this scope.
 *
 * @param array<string, mixed> $values
 */
function share(array $values): void
{
    Ui::publishShared($values);
}

/**
 * Builds a conditional class list; returns an attribute-safe escaped string.
 *
 * @param array<array-key, mixed> $classList Numeric keys pass when truthy; keyed entries when their condition is truthy.
 */
function classes(array $classList): string
{
    $parts = [];
    foreach ($classList as $key => $value) {
        if (is_int($key) ? (bool) $value : (bool) $value) {
            $parts[] = is_int($key) ? (string) $value : (string) $key;
        }
    }

    return htmlspecialchars(implode(' ', $parts), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Validates a caller-supplied URL against a scheme allowlist. Returns null
 * when empty; dangerous schemes collapse to '#'.
 */
function safe_url(?string $url): ?string
{
    if ($url === null) {
        return null;
    }

    $trimmed = trim($url);
    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
        return $trimmed;
    }

    $scheme = strtolower((string) (parse_url($trimmed, PHP_URL_SCHEME) ?: ''));
    if ($scheme === '') {
        return $trimmed;
    }

    return in_array($scheme, ['http', 'https', 'mailto', 'tel', 'sms', 'ftp'], true)
        ? $trimmed
        : '#';
}

/**
 * Wraps arbitrary content into a lazily-rendered Slot.
 *
 * @param Closure|Slot|string|Stringable|null $content
 */
function slot(Closure|Slot|string|Stringable|null $content): Slot
{
    return Slot::make($content);
}

/**
 * camelCase to kebab-case conversion used for prop aliasing.
 */
function self_kebabize(string $value): string
{
    $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $value) ?? $value);

    return str_replace('_', '-', $kebab);
}
