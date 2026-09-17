<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * The root namespace requested for generated component classes is not a valid
 * PHP namespace, so the scaffold could never be autoloaded.
 */
final class InvalidNamespaceException extends \InvalidArgumentException implements HotfireException
{
    public function __construct(private readonly string $namespace)
    {
        parent::__construct(sprintf('Invalid namespace [%s].', $namespace));
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
