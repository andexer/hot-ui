<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * The component state is invalid right before serialization or an action
 * ran, and the component refused to keep going.
 */
final class InvalidStateException extends \LogicException implements HotfireException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}