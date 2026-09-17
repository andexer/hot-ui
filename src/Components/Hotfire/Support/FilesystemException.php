<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

use Components\Support\Exception\SupportException;

/**
 * Exception thrown when filesystem operations fail.
 * 
 * This exception hierarchy follows the Single Responsibility Principle
 * by being specific to filesystem operations rather than generic.
 */
final class FilesystemException extends SupportException
{
    public static function notFound(string $path): self
    {
        return new self("File not found: {$path}");
    }

    public static function notReadable(string $path): self
    {
        return new self("File not readable: {$path}");
    }

    public static function notWritable(string $path): self
    {
        return new self("File not writable: {$path}");
    }

    public static function directoryCreationFailed(string $path): self
    {
        return new self("Failed to create directory: {$path}");
    }

    public static function writeFailed(string $path): self
    {
        return new self("Failed to write to file: {$path}");
    }
}
