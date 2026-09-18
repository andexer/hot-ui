<?php

declare(strict_types=1);

namespace Components\Installation\Exception;

/**
 * Two installation steps depend on each other, so they cannot be ordered.
 */
final class CircularDependencyException extends \RuntimeException implements InstallationException
{
    public function __construct(string $stepId)
    {
        parent::__construct(sprintf('Circular dependency detected: %s', $stepId));
    }
}