<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * mkdir() failed (and the directory still does not exist), so nothing can be
 * published or compiled into that path.
 */
final class DirectoryCreateException extends \RuntimeException implements SupportException
{
    public function __construct(
        private readonly string $path,
        string $label = 'directory',
    ) {
        parent::__construct(sprintf('Unable to create %s [%s].', $label, $path));
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
