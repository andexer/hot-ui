<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Everything a component template needs to know about its invocation. A fresh
 * context is pushed per component render and popped afterwards; nested renders
 * inherit the shared() values of their ancestors.
 */
final class RenderContext
{
    /**
     * @param array<string, mixed> $props  Raw call-site data (props + attributes).
     * @param Slot                 $slot   Default slot content.
     * @param array<string, Slot>  $slots  Named slot contents.
     * @param array<string, mixed> $shared Values published by ancestors via share().
     */
    public function __construct(
        public readonly array $props,
        public readonly Slot $slot,
        public readonly array $slots = [],
        public array $shared = [],
    ) {}
}
