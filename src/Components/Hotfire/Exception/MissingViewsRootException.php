<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * No views root is available, so component folders cannot be located: the
 * discovery ran without one, or the console could not resolve APPPATH.
 */
final class MissingViewsRootException extends \InvalidArgumentException implements HotfireException
{
    public function __construct(private readonly string $reason)
    {
        parent::__construct($reason);
    }

    /** Library-side: discovery needs a views root to walk. */
    public static function forDiscovery(): self
    {
        return new self('Hotfire discovery needs a views root; construct ComponentPaths with one.');
    }

    /** Console-side: neither --views nor APPPATH.'Views' could be used. */
    public static function forConsole(): self
    {
        return new self('Cannot resolve APPPATH; run from a CodeIgniter 4 application or pass --views="/path/to/views".');
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
