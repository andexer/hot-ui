<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * The signed state carried in the DOM is unusable: malformed, oversized,
 * tampered with, or encoding an unknown payload version.
 *
 * Extends \InvalidArgumentException so callers that already rejected bad
 * snapshots that way keep working, and the round-trip endpoint can answer 422.
 */
final class InvalidSnapshotException extends \InvalidArgumentException implements HotfireException
{
    private function __construct(string $message, private readonly string $reason)
    {
        parent::__construct($message);
    }

    public static function malformed(): self
    {
        return new self('Hotfire: malformed snapshot payload.', 'malformed payload');
    }

    public static function tooLarge(int $bytes): self
    {
        return new self(
            'Hotfire: snapshot payload exceeds the size limit.',
            sprintf('%d bytes exceed the size limit', $bytes),
        );
    }

    public static function checksumMismatch(): self
    {
        return new self('Hotfire: snapshot checksum mismatch.', 'checksum mismatch');
    }

    public static function notValid(): self
    {
        return new self('Hotfire: snapshot payload is not valid.', 'payload not valid');
    }

    /** Machine-friendly tag of what failed, for logs and metrics. */
    public function getReason(): string
    {
        return $this->reason;
    }
}
