<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\ViewCompiler;
use PHPUnit\Framework\TestCase;

final class ViewCompilerTest extends TestCase
{
    private ViewCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new ViewCompiler();
    }

    public function testPassthroughWhenNoComponentTags(): void
    {
        $source = "<?php declare(strict_types=1); ?>\n<div class=\"x\">hola</div>";

        self::assertSame($source, $this->compiler->compile($source));
    }

    public function testEmptySource(): void
    {
        self::assertSame('', $this->compiler->compile(''));
    }

    public function testOpenCloseBecomesStreamingCalls(): void
    {
        $out = $this->compiler->compile('<ui:card>Hola</ui:card>');

        self::assertSame(
            '<?php $__ui ??= \Components\Ui::shared(); ?>'
                .'<?php $__ui->open(\'ui.card\', []); ?>Hola<?php echo $__ui->close(); ?>',
            $out,
        );
    }

    public function testSelfClosingBecomesRenderComponent(): void
    {
        $out = $this->compiler->compile('<ui:icon />');

        self::assertSame(
            '<?php $__ui ??= \Components\Ui::shared(); ?>'
                .'<?php echo $__ui->renderComponent(\'ui.icon\', [[]]); ?>',
            $out,
        );
    }

    public function testBlocksNamespace(): void
    {
        $out = $this->compiler->compile('<blocks:kicker>portada</blocks:kicker>');

        self::assertStringContainsString("open('blocks.kicker', []);", $out);
        self::assertStringContainsString("echo \$__ui->close();", $out);
    }

    public function testStaticAttributeMappedToString(): void
    {
        $out = $this->compiler->compile('<ui:button variant="outline">x</ui:button>');

        self::assertStringContainsString("'variant' => 'outline'", $out);
    }

    public function testDynamicAttributeEvaluatesPhp(): void
    {
        $out = $this->compiler->compile('<ui:button :variant="$variant">x</ui:button>');

        self::assertStringContainsString("'variant' => \$variant", $out);
    }

    public function testClassAndDynamicClassAreMerged(): void
    {
        $out = $this->compiler->compile('<ui:button class="base p-2" :class="$extra">x</ui:button>');

        self::assertStringContainsString("'class' => 'base p-2' . ' ' . (string)(\$extra)", $out);
    }

    public function testDynamicClassOnly(): void
    {
        $out = $this->compiler->compile('<ui:button :class="$classes" />');

        self::assertStringContainsString("'class' => (string)(\$classes)", $out);
    }

    public function testClickShortcutBecomesAlpineEvent(): void
    {
        $out = $this->compiler->compile('<ui:button @click="guardar()">x</ui:button>');

        self::assertStringContainsString("'x-on:click' => 'guardar()'", $out);
    }

    public function testClickShortcutKeepsModifiers(): void
    {
        $out = $this->compiler->compile('<ui:button @keydown.enter.prevent="enviar()">x</ui:button>');

        self::assertStringContainsString("'x-on:keydown.enter.prevent' => 'enviar()'", $out);
    }

    public function testAlpineDirectiveIsLiteralString(): void
    {
        $out = $this->compiler->compile('<ui:button x-data="{ open: false }" x-model="q">x</ui:button>');

        self::assertStringContainsString("'x-data' => '{ open: false }'", $out);
        self::assertStringContainsString("'x-model' => 'q'", $out);
    }

    public function testDataAndAriaAttributesAreLiteralStrings(): void
    {
        $out = $this->compiler->compile('<ui:button data-slot="x" :data-state="open ? \'open\' : \'closed\'" aria-label="Cerrar">x</ui:button>');

        self::assertStringContainsString("'data-slot' => 'x'", $out);
        self::assertStringContainsString("':data-state' =>", $out);
        self::assertStringContainsString("'aria-label' => 'Cerrar'", $out);
    }

    public function testBareBooleanAttribute(): void
    {
        $out = $this->compiler->compile('<ui:button disabled>Guardar</ui:button>');

        self::assertStringContainsString("'disabled' => true", $out);
    }

    public function testClassDirectiveMergesConditionalClasses(): void
    {
        $out = $this->compiler->compile(
            "<ui:button @class=\"['px-4' => \$active, 'w-full', 'mt-2' => \$error]\">x</ui:button>",
        );

        self::assertStringContainsString(
            "'class' => (string)(\\Components\\Support\\Classes::render(['px-4' => \$active, 'w-full', 'mt-2' => \$error]))",
            $out,
        );
        self::assertStringNotContainsString("'x-on:class'", $out);
    }

    public function testStyleDirectiveMergesConditionalStyles(): void
    {
        $out = $this->compiler->compile(
            "<ui:button @style=\"['color' => \$danger ? 'red' : 'blue']\">x</ui:button>",
        );

        self::assertStringContainsString(
            "'style' => (string)(\\Components\\Support\\Classes::render(['color' => \$danger ? 'red' : 'blue']))",
            $out,
        );
        self::assertStringNotContainsString("'x-on:style'", $out);
    }

    public function testClassAndStyleDirectivesTolerateNestedCalls(): void
    {
        $out = $this->compiler->compile(
            "<ui:button @class=\"['ok' => str_contains('a b', ' ')]\">x</ui:button>",
        );

        self::assertStringContainsString("str_contains('a b', ' ')", $out);
    }

    public function testAttributeBagSplat(): void
    {
        $out = $this->compiler->compile('<ui:button {{ $attributes }} />');

        self::assertStringContainsString('...($attributes->all())', $out);
    }

    public function testAttributeBagSplatRespectsPosition(): void
    {
        $out = $this->compiler->compile('<ui:button class="base" {{ $attributes }} disabled />');

        self::assertStringContainsString("'class' => 'base', ...(\$attributes->all())", $out);
        self::assertStringContainsString("'disabled' => true", $out);
    }

    public function testInlineSlotWrapsSelfClosingIntoNamedSlot(): void
    {
        $out = $this->compiler->compile('<ui:button slot="title" variant="ghost" />');

        self::assertStringContainsString("into('title'); echo \$__ui->renderComponent('ui.button', [[", $out);
        self::assertStringContainsString("'variant' => 'ghost'", $out);
        self::assertStringContainsString('$__ui->into();', $out);
        self::assertStringNotContainsString("'slot' =>", $out);
    }

    public function testSingleQuotedValues(): void
    {
        $out = $this->compiler->compile("<ui:button x-on:click='it\\'s()'>x</ui:button>");

        self::assertStringContainsString("'x-on:click' =>", $out);
        self::assertStringContainsString("s()", $out);
    }

    public function testPhpBlocksAreNotScanned(): void
    {
        $out = $this->compiler->compile('<?php echo "<ui:card>"; ?><ui:icon />');

        self::assertStringContainsString('<?php echo "<ui:card>"; ?>', $out);
        self::assertStringContainsString("renderComponent('ui.icon'", $out);
    }

    public function testScriptStylesAndCommentsAreNotScanned(): void
    {
        $out = $this->compiler->compile(
            '<script>const t = "<ui:card>";</script><style>a{}</style><!-- <ui:icon/> --><ui:icon />',
        );

        self::assertStringNotContainsString("open('ui.card'", $out);
        self::assertStringContainsString('<script>const t = "<ui:card>";</script>', $out);
        self::assertSame(1, substr_count($out, "renderComponent('ui.icon'"));
    }

    public function testComponentLikeTextInsideAttributeValuesIsPreserved(): void
    {
        $out = $this->compiler->compile('<img src="<ui:fake>"><ui:icon />');

        self::assertStringContainsString('<img src="<ui:fake>">', $out);
        self::assertStringContainsString("renderComponent('ui.icon'", $out);
    }

    public function testNamedSlotMapping(): void
    {
        $out = $this->compiler->compile(
            '<ui:card><ui:slot name="header">H</ui:slot>x</ui:card>',
        );

        self::assertStringContainsString("into('header');", $out);
        self::assertStringContainsString('$__ui->into();', $out);
        self::assertStringContainsString('echo $__ui->close();', $out);
    }

    public function testNestedComponentsCompose(): void
    {
        $out = $this->compiler->compile(
            '<ui:card><ui:slot name="header"><blocks:kicker>x</blocks:kicker></ui:slot><ui:button>x</ui:button></ui:card>',
        );

        self::assertStringContainsString("open('ui.card'", $out);
        self::assertStringContainsString("open('blocks.kicker'", $out);
        self::assertStringContainsString("open('ui.button'", $out);
        self::assertSame(3, substr_count($out, 'echo $__ui->close();'));
    }

    public function testUnclosedTagThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->compiler->compile('<ui:card>sin cerrar');
    }

    public function testUnexpectedCloseTagThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->compiler->compile('</ui:card>');
    }

    public function testMismatchedCloseTagThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->compiler->compile('<ui:card></ui:card-header>');
    }

    public function testUnknownNamespaceStaysLiteral(): void
    {
        $out = $this->compiler->compile('a < b y <div>z</div> <x:y>t</x:y>');

        self::assertStringContainsString('<div>z</div>', $out);
        self::assertStringContainsString('<x:y>t</x:y>', $out);
        self::assertStringContainsString('a < b y', $out);
    }
}