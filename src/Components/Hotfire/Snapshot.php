<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Exception\InvalidSnapshotException;
use Components\Hotfire\Exception\MissingSnapshotKeyException;

/**
 * Stateless state transport for Hotfire components.
 *
 * Public properties of a Component are serialized into a signed payload that
 * lives in the DOM. On every interaction the browser sends it back; the server
 * verifies the HMAC checksum, hydrates the component, runs the action and
 * returns a freshly signed snapshot. No server-side session storage is used,
 * so multiple tabs and horizontal scaling keep working.
 *
 * The signing key is resolved through Config (explicit key, else the configured
 * environment variable). Install a shared Config before rendering.
 */
final class Snapshot
{
    public const VERSION = 1;

    /**
     * Signs component state for embedding in the HTML/DOM.
     *
     * @param array<string, mixed> $state Serialized component public properties.
     *
     * @return array{payload: string, checksum: string}
     */
    public static function encode(array $state, ?string $key = null, ?Config $config = null): array
    {
        $key = self::resolveKey($key, $config);

        $payload = base64_encode(
            json_encode(['v' => self::VERSION, 's' => $state], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
        );

        return ['payload' => $payload, 'checksum' => self::checksum($payload, $key)];
    }

    /**
     * Verifies and decodes a signed payload.
     *
     * @param array{payload: string, checksum: string} $snapshot
     *
     * @return array<string, mixed>
     */
    public static function decode(array $snapshot, ?string $key = null, ?Config $config = null): array
    {
        $key = self::resolveKey($key, $config);

        if (! is_string($snapshot['payload'] ?? null) || ! is_string($snapshot['checksum'] ?? null)) {
            throw InvalidSnapshotException::malformed();
        }

        $payload = $snapshot['payload'];
        if (strlen($payload) > 65536) {
            throw InvalidSnapshotException::tooLarge(strlen($payload));
        }
        $expected = $snapshot['checksum'];
        if (! hash_equals($expected, self::checksum($payload, $key))) {
            throw InvalidSnapshotException::checksumMismatch();
        }

        $raw = base64_decode($payload, true);
        if ($raw === false) {
            throw InvalidSnapshotException::malformed();
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw InvalidSnapshotException::notValid();
        }

        if (! is_array($decoded) || ($decoded['v'] ?? null) !== self::VERSION || ! is_array($decoded['s'] ?? null)) {
            throw InvalidSnapshotException::notValid();
        }

        return $decoded['s'];
    }

    private static function resolveKey(?string $key, ?Config $config): string
    {
        $key ??= ($config ?? Config::shared())->snapshotKey();
        if ($key === null || $key === '') {
            throw new MissingSnapshotKeyException();
        }

        return $key;
    }

    private static function checksum(string $payload, string $key): string
    {
        return hash_hmac('sha256', $payload, $key);
    }
}