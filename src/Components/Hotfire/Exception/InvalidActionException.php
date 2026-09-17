<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

/**
 * The action a client asked for cannot run on the component: it targets a
 * reserved framework method, a non-public method, or a member that is not part
 * of the signed public state.
 */
final class InvalidActionException extends \RuntimeException implements HotfireException
{
    private function __construct(
        string $message,
        private readonly string $action,
        private readonly string $componentClass,
    ) {
        parent::__construct($message);
    }

    /** A model update targeted a member outside the signed state. */
    public static function notAStateProperty(string $componentClass, string $property): self
    {
        return new self(
            sprintf(
                'Hotfire: [%s] is not a declared public state property of %s.',
                $property === '' ? '(empty)' : $property,
                $componentClass,
            ),
            $property,
            $componentClass,
        );
    }

    /** A framework lifecycle method was used as an action. */
    public static function reservedMethod(string $componentClass, string $method): self
    {
        return new self(
            sprintf('Hotfire: framework method [%s] cannot be used as an action.', $method),
            $method,
            $componentClass,
        );
    }

    /** The named method exists but is not callable from a template. */
    public static function notAPublicMethod(string $componentClass, string $method): self
    {
        return new self(
            sprintf('Hotfire: action [%s] is not a public method of %s.', $method, $componentClass),
            $method,
            $componentClass,
        );
    }

    /** The property or method name the client tried to run. */
    public function getAction(): string
    {
        return $this->action;
    }

    public function getComponentClass(): string
    {
        return $this->componentClass;
    }
}
