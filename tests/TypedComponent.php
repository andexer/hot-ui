<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Component;

final class TypedComponent extends Component
{
    public string $uninitialized;
    public ?string $nullable = null;
    public int $total = 10;

    public function add(int $step): void
    {
        $this->total += $step;
    }
}
