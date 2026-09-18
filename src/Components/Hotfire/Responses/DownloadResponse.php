<?php

declare(strict_types=1);

namespace Components\Hotfire\Responses;

/**
 * Download response for Hotfire actions.
 */
final class DownloadResponse
{
    public function __construct(
        public readonly string $content,
        public readonly string $filename,
        public readonly string $mimeType = 'application/octet-stream',
    ) {}
}
