<?php

declare(strict_types=1);

namespace Components\Hotfire\Responses;

/**
 * Redirect response for Hotfire actions.
 */
final class RedirectResponse
{
    public function __construct(
        public readonly string $url,
        public readonly int $status = 302,
    ) {}
}
