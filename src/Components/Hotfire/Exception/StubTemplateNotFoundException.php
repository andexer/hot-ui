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

    /**
     * The generator asks for a stub file that cannot be read.
     *
     * The reported name uses the *.stub convention even when the strategy
     * resolved the suffixed template (e.g. component_class.php.stub), so
     * callers can tell the user which scaffold template is missing.
     */
    public static function notFound(string $stub): self
    {
        $reported = str_ends_with($stub, '.php.stub')
            ? substr($stub, 0, -strlen('.php.stub')).'.stub'
            : $stub;

        return new self($reported);
    }

    public function getStub(): string
    {
        return $this->stub;
    }
}
