<?php

declare(strict_types=1);

namespace Components\Reactivity;

/**
 * Stateless state transport for reactive components.
 *
 * Public properties of a Component are serialized to a signed payload that
 * lives in the DOM. On every interaction the browser sends it back; the
 * server verifies the HMAC checksum, hydrates the component, runs the action
 * and returns a freshly signed snapshot. No server-side session storage is
 * used, so multiple tabs and horizontal scale keep working.
 *
 * The signing key must be a stable per-app secret. Provide HOTUI_SNAPSHOT_KEY
 * (or pass $key to encode/decode).
 */
final class Snapshot
{
    public const VERSION = 1;

    /** Default key source: env HOTUI_SNAPSHOT_KEY. */
    public static function defaultKey(): ?string
    {
        $key = getenv('HOTUI_SNAPSHOT_KEY');
        return is_string($key) && $key !== '' ? $key : null;
    }

    /**
     * Signs component state for embedding in the HTML/DOM.
     *
     * @param array<string, mixed> $state Serialized component public properties.
     *
     * @return array{payload: string, checksum: string}
     */
    public static function encode(array $state, ?string $key = null): array
    {
        $key ??= self::defaultKey();
        if ($key === null || $key === '') {
            throw new \RuntimeException(
                'Hot-UI reactivity: no snapshot key configured. Set HOTUI_SNAPSHOT_KEY in your environment/app config.',
            );
        }

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
    public static function decode(array $snapshot, ?string $key = null): array
    {
        $key ??= self::defaultKey();
        if ($key === null || $key === '') {
            throw new \RuntimeException(
                'Hot-UI reactivity: no snapshot key configured. Set HOTUI_SNAPSHOT_KEY in your environment/app config.',
            );
        }
        if (! is_string($snapshot['payload'] ?? null) || ! is_string($snapshot['checksum'] ?? null)) {
            throw new \InvalidArgumentException('Hot-UI: malformed snapshot payload.');
        }

        $payload = $snapshot['payload'];
        $expected = $snapshot['checksum'];
        if (! hash_equals($expected, self::checksum($payload, $key))) {
            throw new \InvalidArgumentException('Hot-UI: snapshot checksum mismatch.');
        }

        $decoded = json_decode((string) base64_decode($payload, true), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($decoded) || ($decoded['v'] ?? null) !== self::VERSION || ! is_array($decoded['s'] ?? null)) {
            throw new \InvalidArgumentException('Hot-UI: snapshot payload is not valid.');
        }

        return $decoded['s'];
    }

    private static function checksum(string $payload, string $key): string
    {
        return hash_hmac('sha256', $payload, $key);
    }
}