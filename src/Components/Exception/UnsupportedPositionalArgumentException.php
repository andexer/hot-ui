<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * A component was called with a positional argument the engine cannot place:
 * not an array (props), string, closure, Stringable or null (slot content).
 */
final class UnsupportedPositionalArgumentException extends \InvalidArgumentException implements HotUiException
{
    public function __construct(
        private readonly int $position,
        private readonly string $type,
    ) {
        parent::__construct(sprintf(
            'Unsupported positional argument #%d of type [%s]; pass arrays, strings or closures.',
            $position,
            $type,
        ));
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
