<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * A verified snapshot names a class that is not a registered Hotfire
 * component: unknown, misspelled, or not extending Component.
 */
final class UnknownComponentException extends \RuntimeException implements HotfireException
{
    public function __construct(private readonly string $componentClass)
    {
        parent::__construct(sprintf('Hotfire: [%s] is not a registered Hotfire component.', $componentClass));
    }

    public function getComponentClass(): string
    {
        return $this->componentClass;
    }
}
