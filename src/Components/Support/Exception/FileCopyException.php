<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * A file could not be copied during asset or view publication.
 */
final class FileCopyException extends \RuntimeException implements SupportException
{
    public function __construct(
        private readonly string $source,
        private readonly string $destination,
    ) {
        parent::__construct(sprintf(
            'Failed to copy file from [%s] to [%s].',
            $source,
            $destination,
        ));
    }

    public static function failed(string $source, string $destination): self
    {
        return new self($source, $destination);
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }
}
