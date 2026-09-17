<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Component;

/**
 * Fixture component shared by the Hotfire tests: public state plus one action.
 */
final class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
}
