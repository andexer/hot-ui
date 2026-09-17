<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * The components folder exists but holds no namespace folder, so no component
 * could ever be resolved from it.
 */
final class MissingComponentNamespaceException extends \RuntimeException implements HotUiException
{
    public function __construct(private readonly string $path)
    {
        parent::__construct(sprintf('No component namespaces found inside [%s].', $path));
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
