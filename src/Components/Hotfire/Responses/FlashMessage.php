<?php

declare(strict_types=1);

namespace Components\Hotfire\Responses;

/**
 * Flash message response for Hotfire actions.
 */
final class FlashMessage
{
    public function __construct(
        public readonly string $message,
        public readonly string $type = 'info', // success, error, warning, info
    ) {}
}
