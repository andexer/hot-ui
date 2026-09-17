<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * The visual marker prefixed to every component folder (🔥 by default) is
 * empty or contains characters that would break path derivation.
 */
final class InvalidComponentMarkerException extends \InvalidArgumentException implements HotfireException
{
    public function __construct(private readonly string $marker)
    {
        parent::__construct(sprintf(
            'Hotfire emoji [%s] must be a non-empty directory-safe marker (no "/", "\\" or ".").',
            $marker,
        ));
    }

    public function getMarker(): string
    {
        return $this->marker;
    }
}
