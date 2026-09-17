<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Why a signed snapshot was rejected, as a value callers can branch on instead
 * of matching on an error message.
 *
 * The round-trip endpoint uses it to pick the HTTP status: an oversized payload
 * is a `413`, the other three are a `422` because the client sent something the
 * server can never accept.
 */
enum SnapshotFailure: string
{
    /** Not a payload/checksum pair at all. */
    case Malformed = 'malformed payload';

    /** Longer than the accepted size limit. */
    case TooLarge = 'payload too large';

    /** The HMAC does not match: tampered with, or signed with another key. */
    case ChecksumMismatch = 'checksum mismatch';

    /** Decoded, but not the shape/version this release understands. */
    case NotValid = 'payload not valid';
}
