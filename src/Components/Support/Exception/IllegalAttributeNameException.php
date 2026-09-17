<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * An attribute name is not safe to render into markup, so the bag refuses to
 * sanitise it silently and escapes nothing on its behalf.
 */
final class IllegalAttributeNameException extends \InvalidArgumentException implements SupportException
{
    public function __construct(private readonly string $name)
    {
        parent::__construct(sprintf('Illegal attribute name [%s].', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }
}
