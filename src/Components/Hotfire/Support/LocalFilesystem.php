<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

/**
 * Local filesystem implementation using PHP's native filesystem functions.
 * 
 * This is the default implementation that wraps PHP's filesystem functions
 * in the FilesystemInterface, allowing for easy testing and potential
 * alternative implementations (virtual filesystems, cloud storage, etc.).
 */
final readonly class LocalFilesystem implements FilesystemInterface
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function read(string $path): string
    {
        if (! $this->exists($path)) {
            throw FilesystemException::notFound($path);
        }

        if (! $this->isReadable($path)) {
            throw FilesystemException::notReadable($path);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw FilesystemException::notFound($path);
        }

        return $content;
    }

    public function write(string $path, string $content): void
    {
        $directory = dirname($path);
        if (! $this->exists($directory)) {
            $this->createDirectory($directory);
        }

        if (! $this->isWritable($directory)) {
            throw FilesystemException::notWritable($directory);
        }

        $result = file_put_contents($path, $content, LOCK_EX);
        if ($result === false) {
            throw FilesystemException::writeFailed($path);
        }
    }

    public function createDirectory(string $path, int $mode = 0o755): void
    {
        if ($this->exists($path)) {
            return;
        }

        if (! mkdir($path, $mode, true) && ! $this->isDirectory($path)) {
            throw FilesystemException::directoryCreationFailed($path);
        }
    }

    public function ensureDirectory(string $path): bool
    {
        if ($this->exists($path)) {
            return $this->isDirectory($path);
        }

        try {
            $this->createDirectory($path);
            return true;
        } catch (FilesystemException) {
            return false;
        }
    }
}
