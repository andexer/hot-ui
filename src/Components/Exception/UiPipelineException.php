<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * The streaming pipeline is used out of order: a slot or close without an open
 * frame, or a shared value published outside a render. These are call-sequence
 * bugs in the template, not bad data.
 */
final class UiPipelineException extends \LogicException implements HotUiException
{
    private function __construct(
        private readonly string $method,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** into()/close() ran while no component frame was open. */
    public static function withoutOpen(string $method): self
    {
        return new self($method, sprintf('%s() called without a matching open().', $method));
    }

    /** share() ran while no template was rendering. */
    public static function outsideRender(string $method): self
    {
        return new self($method, sprintf('%s() can only be called while rendering a template.', $method));
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
