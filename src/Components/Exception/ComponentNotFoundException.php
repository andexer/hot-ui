<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * Thrown when a component name cannot be resolved, optionally listing known
 * candidates to speed up typo hunting.
 */
final class ComponentNotFoundException extends \RuntimeException implements HotUiException
{
    /**
     * @param string      $requested  The unresolved component name.
     * @param list<string> $candidates Known components offered as alternatives.
     */
    public function __construct(string $requested, array $candidates = [])
    {
        $hint = $candidates === []
            ? ''
            : ' Did you mean: '.implode(', ', $candidates).'?';

        parent::__construct(sprintf('Unknown component [%s].%s', $requested, $hint));
    }
}
