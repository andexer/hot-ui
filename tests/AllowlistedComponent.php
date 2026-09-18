<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Component;

final class AllowlistedComponent extends Component
{
    protected string $view = 'components/hotfire/typed-component';

    public int $total = 10;

    public function allowedActions(): ?array
    {
        return ['add'];
    }

    public function add(int $step): void
    {
        $this->total += $step;
    }

    public function subtract(int $step): void
    {
        $this->total -= $step;
    }
}
