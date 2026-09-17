<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * A directory the engine needs is absent: the views components folder, or one
 * namespace folder inside it.
 */
final class MissingDirectoryException extends \RuntimeException implements HotUiException
{
    public function __construct(
        private readonly string $path,
        string $kind = 'components directory',
    ) {
        parent::__construct(sprintf('%s [%s] does not exist.', ucfirst($kind), $path));
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
