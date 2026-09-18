<?php

declare(strict_types=1);

namespace Components\Hotfire\Responses;

/**
 * No content response for Hotfire actions.
 */
final class NoContentResponse
{
    public function __construct(
        public readonly int $status = 204,
    ) {}
}
