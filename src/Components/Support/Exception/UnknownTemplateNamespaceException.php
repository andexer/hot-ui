<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * A template was referenced as "ns::name" with an ns that was never registered,
 * so the renderer has no folder to resolve it against.
 */
final class UnknownTemplateNamespaceException extends \RuntimeException implements SupportException
{
    public function __construct(
        private readonly string $namespace,
        private readonly string $template,
    ) {
        parent::__construct(sprintf('Unknown template namespace [%s] in [%s].', $namespace, $template));
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
