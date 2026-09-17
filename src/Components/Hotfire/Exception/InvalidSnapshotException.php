<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

use Components\Hotfire\SnapshotFailure;

/**
 * The signed state carried in the DOM is unusable: malformed, oversized,
 * tampered with, or encoding an unknown payload version.
 *
 * Extends \InvalidArgumentException so callers that already rejected bad
 * snapshots that way keep working.
 *
 * getFailure() is the machine-readable kind (the round-trip endpoint maps it to
 * a status); getReason() is the human detail kept for logs and metrics, and is
 * the older of the two, so it stays as it was.
 */
final class InvalidSnapshotException extends \InvalidArgumentException implements HotfireException
{
    private function __construct(
        private readonly SnapshotFailure $failure,
        private readonly ?string $detail,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function malformed(): self
    {
        return new self(SnapshotFailure::Malformed, null, 'Hotfire: malformed snapshot payload.');
    }

    public static function tooLarge(int $bytes): self
    {
        return new self(
            SnapshotFailure::TooLarge,
            sprintf('%d bytes exceed the size limit', $bytes),
            'Hotfire: snapshot payload exceeds the size limit.',
        );
    }

    public static function checksumMismatch(): self
    {
        return new self(SnapshotFailure::ChecksumMismatch, null, 'Hotfire: snapshot checksum mismatch.');
    }

    public static function notValid(): self
    {
        return new self(SnapshotFailure::NotValid, null, 'Hotfire: snapshot payload is not valid.');
    }

    /** Machine-readable kind of failure, for callers that must branch. */
    public function getFailure(): SnapshotFailure
    {
        return $this->failure;
    }

    /** Human-readable detail of what failed, for logs and metrics. */
    public function getReason(): string
    {
        return $this->detail ?? $this->failure->value;
    }
}
