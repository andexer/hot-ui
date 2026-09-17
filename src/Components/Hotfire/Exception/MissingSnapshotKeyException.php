<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * Hotfire refuses to run unsigned: no snapshot key was passed and the
 * configured environment variable is empty.
 */
final class MissingSnapshotKeyException extends \RuntimeException implements HotfireException
{
    public function __construct()
    {
        parent::__construct(
            'Hotfire: no snapshot key configured. Set the snapshot key environment variable or pass a key.',
        );
    }
}
