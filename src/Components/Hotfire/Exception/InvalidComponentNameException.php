<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * A component name cannot be turned into safe path segments: it is empty,
 * garbled, or tries to traverse out of the views folder.
 */
final class InvalidComponentNameException extends \InvalidArgumentException implements HotfireException
{
    public function __construct(
        private readonly string $componentName,
        private readonly string $reason,
    ) {
        parent::__construct(sprintf('Invalid Hotfire component name [%s]; %s', $componentName, $reason));
    }

    public function getComponentName(): string
    {
        return $this->componentName;
    }

    /** Why the name was rejected; safe to print on a CLI. */
    public function getReason(): string
    {
        return $this->reason;
    }
}
