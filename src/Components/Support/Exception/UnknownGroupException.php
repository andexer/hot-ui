<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * A group key is not one of the groups the package publishes ("css"/"js" for
 * assets, "components"/"layouts"/"partials" for views). The message lists the
 * accepted keys when the caller has a fixed set to choose from.
 */
final class UnknownGroupException extends \InvalidArgumentException implements SupportException
{
    /**
     * @param string       $kind     Asset group family: "asset" or "views".
     * @param string       $group    The rejected key.
     * @param list<string> $expected Accepted keys, in documentation order.
     */
    public function __construct(
        private readonly string $kind,
        private readonly string $group,
        private readonly array $expected = [],
    ) {
        parent::__construct($this->describe());
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    /** @return list<string> */
    public function getExpected(): array
    {
        return $this->expected;
    }

    private function describe(): string
    {
        $message = sprintf('Unknown %s group [%s]', $this->kind, $this->group);
        $accepted = $this->expected;
        if ($accepted === []) {
            return $message.'.';
        }

        $last = (string) array_pop($accepted);

        return $message.($accepted === []
            ? sprintf('; expected %s.', $last)
            : sprintf('; expected %s or %s.', implode(', ', $accepted), $last));
    }
}
