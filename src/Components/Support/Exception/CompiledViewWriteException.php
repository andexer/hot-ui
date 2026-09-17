<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * The compiler could not persist a compiled view: the staging file would not
 * write, or the atomic rename onto the cache key failed.
 */
final class CompiledViewWriteException extends \RuntimeException implements SupportException
{
    private function __construct(
        private readonly string $path,
        private readonly string $reason,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** The staged file could not be written. */
    public static function writeFailed(string $path): self
    {
        return new self($path, 'write', sprintf('Cannot write compiled view [%s].', $path));
    }

    /** The staged file could not replace the cached artifact. */
    public static function replaceFailed(string $path): self
    {
        return new self($path, 'replace', sprintf('Cannot replace compiled view [%s].', $path));
    }

    /** The compiled artifact that could not be persisted. */
    public function getPath(): string
    {
        return $this->path;
    }

    /** Which step failed: "write" or "replace". */
    public function getReason(): string
    {
        return $this->reason;
    }
}
