<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * A publishing helper could not work out which directory to use: no framework
 * constant to fall back on, no working directory, or no recognisable web root.
 * Every case is fixed the same way — passing the directory explicitly — so it
 * is a caller-facing argument error; getPurpose() names what was missing, so
 * hosts can react per case without parsing the sentence.
 */
final class UnresolvedDirectoryException extends \InvalidArgumentException implements HotUiException
{
    public function __construct(
        private readonly string $purpose,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** The directory the caller wanted: "public directory", "views directory"… */
    public function getPurpose(): string
    {
        return $this->purpose;
    }
}
