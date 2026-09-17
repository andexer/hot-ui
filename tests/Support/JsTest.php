<?php

declare(strict_types=1);

namespace Components\Tests\Support;

use Components\Support\Js;
use PHPUnit\Framework\TestCase;

enum Suit: string
{
    case Hearts = 'hearts';
}

final class JsTest extends TestCase
{
    public function testScalars(): void
    {
        self::assertSame('42', Js::from(42));
        self::assertSame('true', Js::from(true));
        self::assertSame('null', Js::from(null));
        self::assertSame('1.0', Js::from(1.0));
    }

    public function testStringsAreAttributeSafe(): void
    {
        // Every double quote (structural or in-value) becomes \u0022: the
        // literal stays valid JavaScript and can never break class="...".
        self::assertSame('\u0022\u0022quoted\u0022\u0022', Js::from('"quoted"'));
        self::assertStringNotContainsString('"', Js::from('<script>alert("x")</script>'));
        self::assertStringNotContainsString('"', Js::from(['show' => false]));
    }

    public function testLineSeparatorsAreEscaped(): void
    {
        // JSON_UNESCAPED_UNICODE keeps U+2028/U+2029 raw; inside an inline
        // <script> they terminate the literal early, so they must become \uXXXX.
        self::assertSame('\u0022\u2028\u0022', Js::from("\u{2028}"));
        self::assertSame('\u0022\u2029\u0022', Js::from("\u{2029}"));
        self::assertStringNotContainsString("\u{2028}", Js::from("a\u{2028}b"));
        self::assertStringNotContainsString("\u{2029}", Js::from("a\u{2029}b"));
    }

    public function testTagSequencesNeverEscapeTheAttribute(): void
    {
        $literal = Js::from('<script>function(){return "</script>"}</script>');

        self::assertStringNotContainsString('<', $literal);
        self::assertStringNotContainsString('>', $literal);
        self::assertStringNotContainsString('"', $literal);
    }

    public function testArraysAndObjects(): void
    {
        self::assertSame('[1,2]', Js::from([1, 2]));
        self::assertSame('{\u0022a\u0022:1}', Js::from(['a' => 1]));
    }

    public function testEnumsUnwrap(): void
    {
        self::assertSame('\u0022hearts\u0022', Js::from(Suit::Hearts));
    }
}
