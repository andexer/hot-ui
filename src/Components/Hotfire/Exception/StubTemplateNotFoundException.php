<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * A scaffold template (src/Components/Hotfire/templates/*.stub) is missing or
 * cannot be read, so the generator has nothing to render the artifact from.
 */
final class StubTemplateNotFoundException extends \RuntimeException implements HotfireException
{
    public function __construct(private readonly string $stub)
    {
        parent::__construct(sprintf('Hotfire stub template not found or unreadable: %s', $stub));
    }

    public function getStub(): string
    {
        return $this->stub;
    }
}
