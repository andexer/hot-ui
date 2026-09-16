<?php

declare(strict_types=1);

namespace Components\Support;

use Closure;
use Stringable;

/**
 * A lazily-rendered chunk of pre-escaped HTML (the component slot).
 *
 * Content parts may be strings, Stringable values or closures. Closures run
 * only when the slot is rendered, so unused slots cost nothing. A closure may
 * return a string/Stringable or echo HTML directly between ?> and <?php tags;
 * both styles are captured.
 */
final class Slot implements Stringable
{
    /** @var list<Closure|string|Stringable|null> */
    private readonly array $parts;

    private ?string $resolved = null;

    public function __construct(Closure|string|Stringable|null ...$parts)
    {
        $this->parts = array_values($parts);
    }

    /**
     * Normalises any accepted content value into a Slot instance.
     *
     * @param Closure|self|string|Stringable|null $content
     */
    public static function make(Closure|self|string|Stringable|null $content): self
    {
        if ($content instanceof self) {
            return $content;
        }

        return new self($content);
    }

    /**
     * Renders every part and memoises the result.
     */
    public function render(): string
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $out = '';
        foreach ($this->parts as $part) {
            $out .= match (true) {
                $part === null => '',
                $part instanceof Closure => self::capture($part),
                default => (string) $part,
            };
        }

        return $this->resolved = $out;
    }

    public function isEmpty(): bool
    {
        return trim($this->render()) === '';
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Captures echoed output plus the closure return value.
     */
    private static function capture(Closure $part): string
    {
        ob_start();

        try {
            $returned = $part();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        $echoed = (string) ob_get_clean();
        if ($returned instanceof Stringable) {
            $returned = (string) $returned;
        }

        return $echoed.(is_string($returned) ? $returned : '');
    }
}
