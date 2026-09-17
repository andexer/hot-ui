<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * A scaffold property name (--props) is not a plain PHP identifier, so it
 * cannot become a typed public property of the generated component.
 */
final class InvalidComponentPropertyException extends \InvalidArgumentException implements HotfireException
{
    public function __construct(private readonly string $propertyName)
    {
        parent::__construct(sprintf(
            'Invalid property name [%s]; use plain identifiers (title, content).',
            $propertyName,
        ));
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public static function invalidName(string $propertyName): self
    {
        return new self($propertyName);
    }
}
