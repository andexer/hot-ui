<?php

declare(strict_types=1);

namespace Components\Tests\Support;

use Components\Support\AttributeBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributeBagTest extends TestCase
{
    public function testGetHasAndAccessors(): void
    {
        $bag = new AttributeBag(['id' => 'x', 'required' => true, 'hidden' => false]);

        self::assertSame('x', $bag->get('id'));
        self::assertSame('def', $bag->get('missing', 'def'));
        self::assertTrue($bag->has('id'));
        self::assertFalse($bag->has('nope'));
        self::assertTrue($bag->hasAny(['a', 'id']));
        self::assertSame('x', $bag['id']);
    }

    public function testOnlyPreservesGivenOrder(): void
    {
        $bag = new AttributeBag(['a' => '1', 'b' => '2', 'c' => '3']);

        self::assertSame(['b' => '2', 'a' => '1'], $bag->only('b', 'a')->all());
        self::assertSame(['b' => '2'], $bag->except('a', 'c')->all());
    }

    public function testWhereStartsWithFiltersNames(): void
    {
        $bag = new AttributeBag([
            'data-hot-model' => 'user.name',
            'aria-label' => 'Email',
            'class' => 'btn',
        ]);

        $bound = $bag->whereStartsWith('data-hot-');
        self::assertSame(['data-hot-model' => 'user.name'], $bound->all());

        self::assertSame(
            ['aria-label' => 'Email', 'class' => 'btn'],
            $bag->whereDoesntStartWith(['data-hot-'])->all(),
        );
    }

    public function testMergePrependsDefaultClassesAndAppendsStyles(): void
    {
        $bag = new AttributeBag(['class' => 'user-class', 'style' => 'color:red']);

        $merged = $bag->merge(['class' => 'default', 'style' => 'margin:0', 'type' => 'text']);

        self::assertSame('default user-class', $merged->get('class'));
        self::assertSame('margin:0; color:red', $merged->get('style'));
        self::assertSame('text', $merged->get('type'));
        // Original untouched (immutable).
        self::assertSame('user-class', $bag->get('class'));
    }

    public function testClassAppendsConditionalEntries(): void
    {
        $bag = new AttributeBag(['class' => 'base']);

        $out = $bag->class(['always', 'yes' => true, 'no' => false, 'nested' => ['deep' => true, 'off' => false]]);

        self::assertSame('base always yes deep', $out->get('class'));
    }

    public function testTwMergeRendersAllAttributesWithResolvedClassLast(): void
    {
        $bag = new AttributeBag(['class' => 'p-4', 'id' => 'x', 'data-n' => '1']);

        $attr = $bag->twMerge('p-8 text-sm');

        self::assertStringStartsWith('id="x" data-n="1"', $attr);
        self::assertStringEndsWith('class="p-8 text-sm"', $attr);
        self::assertStringNotContainsString('p-4', $attr);
    }

    public function testToStringEscapesValuesAndHandlesBooleans(): void
    {
        $bag = new AttributeBag([
            'title' => 'He "said" <ok>',
            'disabled' => true,
            'autofocus' => false,
            'data-items' => ['a', null, 'b'],
        ]);

        $html = (string) $bag;

        self::assertSame(
            ' title="He &quot;said&quot; &lt;ok&gt;" disabled data-items="a b"',
            $html,
        );
    }

    public function testIllegalAttributeNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        echo new AttributeBag(['bad"name' => 'x']);
    }

    public static function invalidKeysProvider(): array
    {
        return [
            ['has space'],
            ['quote"in'],
        ];
    }

    #[DataProvider('invalidKeysProvider')]
    public function testVariousIllegalKeysThrow(string $key): void
    {
        $this->expectException(\InvalidArgumentException::class);

        echo new AttributeBag([$key => 'v']);
    }

    public function testAlpineAndColonAttributesAreAllowed(): void
    {
        $bag = new AttributeBag([
            '@click' => 'open = true',
            ':class' => "isOpen ? 'on' : 'off'",
            'x-on:submit.prevent' => 'save',
            'aria-label' => 'Close',
        ]);

        $html = (string) $bag;

        self::assertStringContainsString('@click="open = true"', $html);
        self::assertStringContainsString(":class=\"isOpen ? &#039;on&#039; : &#039;off&#039;\"", $html);
        self::assertStringContainsString('x-on:submit.prevent="save"', $html);
    }
}
