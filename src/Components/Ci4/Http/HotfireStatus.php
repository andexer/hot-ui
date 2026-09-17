<?php

declare(strict_types=1);

namespace Components\Ci4\Http;

use Components\Exception\ComponentNotFoundException;
use Components\Hotfire\Exception\InvalidActionException;
use Components\Hotfire\Exception\InvalidComponentNameException;
use Components\Hotfire\Exception\InvalidSnapshotException;
use Components\Hotfire\Exception\UnknownComponentException;
use Components\Hotfire\SnapshotFailure;

/**
 * Maps a round-trip failure to the HTTP status that describes it, so the driver
 * and the logs can tell a client mistake from a broken deployment.
 *
 *   413 — too large to accept: the request body, or the signed snapshot
 *   422 — the client sent something unusable: tampered, malformed, or an
 *         action/property that is not part of the signed state
 *   404 — the component or template it asked for does not exist
 *   500 — the server could not comply: a view that will not compile, a missing
 *         signing key, or a failure outside the package (a bug, not a verdict)
 *
 * Deliberately free of CodeIgniter (and of any dependency on the framework's
 * classes), so the whole mapping is unit-tested wherever the package runs — the
 * controller itself only needs a request and a response to be exercised.
 */
final class HotfireStatus
{
    public static function for(\Throwable $exception): int
    {
        return match (true) {
            $exception instanceof InvalidSnapshotException => self::forSnapshot($exception),
            $exception instanceof UnknownComponentException,
            $exception instanceof ComponentNotFoundException => 404,
            $exception instanceof InvalidActionException,
            $exception instanceof InvalidComponentNameException => 422,
            // Everything else — a component that cannot compile or render, no
            // signing key configured, an unexpected TypeError — tells the client
            // nothing true about its own request.
            default => 500,
        };
    }

    private static function forSnapshot(InvalidSnapshotException $exception): int
    {
        return $exception->getFailure() === SnapshotFailure::TooLarge ? 413 : 422;
    }
}
