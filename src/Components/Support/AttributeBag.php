<?php

declare(strict_types=1);

namespace Components\Support;

use ArrayAccess;
use IteratorAggregate;
use Stringable;
use Traversable;

/**
 * Immutable bag of HTML attributes with merge ergonomics and a Tailwind-aware
 * class resolver.
 *
 * Values rendered through __toString() are escaped. Boolean semantics follow
 * HTML: true renders a bare attribute, false/null omits it entirely.
 *
 * @implements ArrayAccess<string, mixed>
 */
final class AttributeBag implements ArrayAccess, IteratorAggregate, \Countable, Stringable
{
    private const KEY_PATTERN = '/^[a-zA-Z_:@][a-zA-Z0-9_:.\-]*$/';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(private array $attributes = []) {}

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->attributes;
    }

    public function isEmpty(): bool
    {
        return $this->attributes === [];
    }

    /**
     * Returns an attribute value or the given default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param list<string> $keys
     */
    public function hasAny(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Keeps only the given keys, preserving their order.
     */
    public function only(string ...$keys): static
    {
        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->attributes)) {
                $out[$key] = $this->attributes[$key];
            }
        }

        return new static($out);
    }

    /**
     * Removes the given keys.
     */
    public function except(string ...$keys): static
    {
        return new static(array_diff_key($this->attributes, array_flip($keys)));
    }

    /**
     * Removes keys that declare component props.
     *
     * @param list<string> $props
     */
    public function exceptProps(array $props): static
    {
        return new static(array_diff_key($this->attributes, array_flip($props)));
    }

    /**
     * Filters entries by predicate; defaults to truthy-value filtering.
     *
     * @param callable(mixed, string): bool|null $callback
     */
    public function filter(?callable $callback = null): static
    {
        $callback ??= static fn (mixed $value): bool => (bool) $value;

        return $this->filterNames($callback);
    }

    /**
     * Keeps attributes whose name starts with any of the given needles.
     */
    public function whereStartsWith(iterable|string $needles): static
    {
        return $this->filterNames(static fn (string $name): bool => self::startsAnyWith($name, $needles));
    }

    /**
     * Removes attributes whose name starts with any of the given needles.
     */
    public function whereDoesntStartWith(iterable|string $needles): static
    {
        return $this->filterNames(static fn (string $name): bool => ! self::startsAnyWith($name, $needles));
    }

    /**
     * Merges default attribute values: existing values win, except class and
     * style which combine with defaults placed first so caller classes can
     * override them downstream via twMerge().
     */
    public function merge(array|string $defaults): static
    {
        if (is_string($defaults)) {
            $defaults = ['class' => $defaults];
        }

        $attrs = $this->attributes;
        foreach ($defaults as $key => $default) {
            if ($key === 'class') {
                $attrs['class'] = trim(trim((string) $default).' '.trim((string) ($attrs['class'] ?? '')));
                continue;
            }
            if ($key === 'style') {
                $existing = trim((string) ($attrs['style'] ?? ''), '; ');
                $attrs['style'] = trim(trim((string) $default, '; ').'; '.$existing, '; ');
                continue;
            }
            if (! array_key_exists($key, $attrs)) {
                $attrs[$key] = $default;
            }
        }

        return new static($attrs);
    }

    /**
     * Appends conditional classes; numeric keys pass when truthy, keyed entries
     * when their condition is truthy.
     *
     * @param array<array-key, mixed> $classList
     */
    public function class(array $classList): static
    {
        $extra = self::resolveConditionalClasses($classList);

        if ($extra === '') {
            return clone $this;
        }

        $attrs = $this->attributes;
        $attrs['class'] = trim(trim((string) ($attrs['class'] ?? '')).' '.$extra);

        return new static($attrs);
    }

    /**
     * Renders every attribute of the bag while resolving Tailwind conflicts in
     * the class attribute. Output is the complete escaped attribute string,
     * class always last.
     */
    public function twMerge(string ...$classes): string
    {
        $existing = trim((string) ($this->attributes['class'] ?? ''));
        $merged = TailwindMerge::merge($existing, ...array_filter($classes));

        $others = new self(array_diff_key($this->attributes, ['class' => true]));

        return trim(ltrim((string) $others).sprintf(' class="%s"', htmlspecialchars($merged, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('AttributeBag is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('AttributeBag is immutable.');
    }

    /** @return Traversable<string, mixed> */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->attributes);
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    /**
     * Renders safe name="escaped-value" pairs. Invalid attribute names throw
     * instead of being sanitised silently.
     */
    public function __toString(): string
    {
        $out = '';
        foreach ($this->attributes as $key => $value) {
            if (! preg_match(self::KEY_PATTERN, $key)) {
                throw new \InvalidArgumentException(sprintf('Illegal attribute name [%s].', $key));
            }
            if ($value === false || $value === null) {
                continue;
            }
            if ($value === true) {
                $out .= ' '.$key;
                continue;
            }
            if (is_array($value)) {
                $value = implode(' ', array_map(strval(...), array_values(array_filter(
                    $value,
                    static fn (mixed $v): bool => $v !== null && $v !== false && (string) $v !== '',
                ))));
            }

            $out .= sprintf(' %s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        return $out;
    }

    /**
     * @param array<array-key, mixed> $classList
     */
    private static function resolveConditionalClasses(array $classList): string
    {
        $parts = [];
        foreach ($classList as $key => $value) {
            if (is_array($value)) {
                $nested = self::resolveConditionalClasses($value);
                if ($nested !== '') {
                    $parts[] = $nested;
                }
                continue;
            }
            $truthy = is_int($key) ? (bool) $value : (bool) $value;
            if ($truthy) {
                $parts[] = is_int($key) ? (string) $value : (string) $key;
            }
        }

        return implode(' ', $parts);
    }

    /**
     * @param callable(string): bool $predicate
     */
    private function filterNames(callable $predicate): static
    {
        $out = [];
        foreach ($this->attributes as $key => $value) {
            if ($predicate($key)) {
                $out[$key] = $value;
            }
        }

        return new static($out);
    }

    private static function startsAnyWith(string $subject, iterable|string $needles): bool
    {
        if (is_string($needles)) {
            return str_starts_with($subject, $needles);
        }

        foreach ($needles as $needle) {
            if (str_starts_with($subject, $needle)) {
                return true;
            }
        }

        return false;
    }
}
