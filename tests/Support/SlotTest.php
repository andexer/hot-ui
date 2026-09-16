<?php

declare(strict_types=1);

namespace Components\Tests\Support;

use Components\Support\Js;
use Components\Support\Slot;
use PHPUnit\Framework\TestCase;

final class SlotTest extends TestCase
{
    public function testStringContent(): void
    {
        self::assertSame('hello', (string) new Slot('hello'));
        self::assertSame('ab', (string) new Slot('a', 'b'));
    }

    public function testClosureEchoIsCaptured(): void
    {
        $slot = new Slot(function (): void {
            echo '<b>bold</b>';
        });

        self::assertSame('<b>bold</b>', (string) $slot);
    }

    public function testClosureReturnValueAndEchoConcatenate(): void
    {
        $slot = new Slot(function (): string {
            echo 'echoed-';

            return 'returned';
        });

        self::assertSame('echoed-returned', (string) $slot);
    }

    public function testLazinessOnlyRendersOnceUsed(): void
    {
        $calls = 0;
        $counter = function () use (&$calls): string {
            ++$calls;

            return 'x';
        };

        $slot = new Slot($counter);
        self::assertSame(0, $calls, 'Construction must not execute closures');

        $slot->isEmpty();
        self::assertSame(1, $calls);
        self::assertSame('x', (string) $slot, 'Result is memoised');
        self::assertSame(1, $calls);
    }

    public function testIsEmptyAndIsNotEmpty(): void
    {
        self::assertTrue((new Slot(null))->isEmpty());
        self::assertTrue((new Slot("  \n"))->isEmpty());
        self::assertTrue((new Slot(fn () => ''))->isEmpty());
        self::assertFalse((new Slot('<i></i>'))->isEmpty());
    }

    public function testMakeWrapsOrPassesThrough(): void
    {
        $existing = new Slot('a');
        self::assertSame($existing, Slot::make($existing));
        self::assertSame('b', (string) Slot::make('b'));
    }

    public function testNullPartRendersEmpty(): void
    {
        self::assertSame('', (string) new Slot(null));
        self::assertSame('', (string) new Slot());
    }
}
