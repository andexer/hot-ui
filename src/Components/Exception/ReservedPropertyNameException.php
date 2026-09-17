<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * A component declared a prop using a name the template scope already owns
 * (attributes, slot, slots, __ctx), which would shadow the engine's own data.
 */
final class ReservedPropertyNameException extends \InvalidArgumentException implements HotUiException
{
    public function __construct(private readonly string $propertyName)
    {
        parent::__construct(sprintf(
            'Prop [%s] collides with a reserved template variable.',
            $propertyName,
        ));
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
