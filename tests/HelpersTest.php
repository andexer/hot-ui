<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\AttributeBag;
use Components\Support\RenderContext;
use Components\Support\Slot;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testE(): void
    {
        self::assertSame('&lt;b&gt;', e('<b>'));
        self::assertSame('plain', e('plain'));
        self::assertSame('&amp;amp;', e('&amp;'));
        self::assertSame('&amp;', e('&amp;', doubleEncode: false));
    }

    public function testJsIsAttributeSafe(): void
    {
        $literal = js(['show' => false]);

        self::assertStringNotContainsString('"', $literal);
    }

    public function testClassesConditional(): void
    {
        self::assertSame('a b', classes(['a', 'b' => true, 'c' => false, 'd' => 1 === 2]));
        self::assertSame('', classes([]));
        self::assertSame('&quot;q&quot;', classes(['"q"' => true]));
    }

    public static function urlProvider(): array
    {
        return [
            [null, null],
            ['', ''],
            ['#anchor', '#anchor'],
            ['/relative/path', '/relative/path'],
            ['img/pic.png', 'img/pic.png'],
            ['//cdn.example.com/x.js', '//cdn.example.com/x.js'],
            ['https://ok.com', 'https://ok.com'],
            ['HTTP://UPPER.COM', 'HTTP://UPPER.COM'],
            ['mailto:a@b.c', 'mailto:a@b.c'],
            ['tel:+123', 'tel:+123'],
            ['javascript:alert(1)', '#'],
            ['data:text/html,x', '#'],
            ['vbscript:x', '#'],
        ];
    }

    #[DataProvider('urlProvider')]
    public function testSafeUrl(?string $input, ?string $expected): void
    {
        self::assertSame($expected, safe_url($input));
    }

    public function testPropsResolvesDefaultsSlotsAndAttributes(): void
    {
        $ctx = new RenderContext(
            props: ['variant' => 'ghost', 'class' => 'extra', 'id' => 'i1', 'unknown' => 'u'],
            slot: new Slot('body'),
            slots: ['leading' => new Slot('<x/>')],
        );

        $out = props($ctx, ['variant' => 'default', 'size' => 'sm']);

        self::assertSame('ghost', $out['variant'], 'Explicit value wins over default');
        self::assertSame('sm', $out['size']);
        self::assertInstanceOf(Slot::class, $out['leading']);
        self::assertSame('<x/>', (string) $out['leading']);
        self::assertInstanceOf(AttributeBag::class, $out['attributes']);
        self::assertSame(
            ['class' => 'extra', 'id' => 'i1', 'unknown' => 'u'],
            $out['attributes']->all(),
            'Declared props are stripped from the attribute bag',
        );
        self::assertSame('body', (string) $out['slot']);
    }

    public function testPropsRejectsReservedNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        props(new RenderContext([], new Slot()), ['attributes' => 'boom']);
    }

    public function testAwarePrefersExplicitOverSharedOverDefault(): void
    {
        $ctx = new RenderContext(
            props: ['indicator' => 'radio'],
            slot: new Slot(),
            shared: ['indicator' => 'checkbox', 'layout' => 'vertical'],
        );

        $resolved = aware($ctx, ['indicator' => 'check', 'layout' => 'horizontal']);

        self::assertSame('radio', $resolved['indicator'], 'Explicit prop beats shared');
        self::assertSame('vertical', $resolved['layout'], 'Shared beats declared default');
    }
}
