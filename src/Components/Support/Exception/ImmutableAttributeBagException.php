<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * ArrayAccess write access on AttributeBag. The bag is a value object handed to
 * templates, so mutation is a programming error rather than a data error.
 */
final class ImmutableAttributeBagException extends \LogicException implements SupportException
{
    public function __construct()
    {
        parent::__construct('AttributeBag is immutable.');
    }
}
