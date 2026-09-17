<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * A scaffold artifact could not be written: the component folder cannot be
 * created, or the target file is not writable.
 */
final class ComponentWriteException extends \RuntimeException implements HotfireException
{
    private function __construct(string $message, private readonly string $path)
    {
        parent::__construct($message);
    }

    public static function uncreatableDirectory(string $path): self
    {
        return new self(sprintf('Cannot create directory %s.', $path), $path);
    }

    public static function unwritableFile(string $path): self
    {
        return new self(sprintf('Cannot write %s.', $path), $path);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
