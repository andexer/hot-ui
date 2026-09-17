<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * A compiled artifact was handed to the renderer and no longer exists, so the
 * cache and the caller disagree about what was compiled.
 */
final class CompiledTemplateNotFoundException extends \RuntimeException implements SupportException
{
    public function __construct(private readonly string $path)
    {
        parent::__construct(sprintf('Compiled template [%s] not found.', $path));
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
