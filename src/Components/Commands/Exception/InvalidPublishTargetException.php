<?php

declare(strict_types=1);

namespace Components\Commands\Exception;

/**
 * The publish command was asked for a target other than assets, views or both.
 */
final class InvalidPublishTargetException extends \InvalidArgumentException implements CommandException
{
    public function __construct(private readonly string $target)
    {
        parent::__construct(sprintf('Invalid target [%s]; use assets, views or both.', $target));
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
