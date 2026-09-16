<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Exception\ComponentNotFoundException;
use Components\Ui;
use PHPUnit\Framework\TestCase;

final class StreamingTest extends TestCase
{
    private Ui $ui;

    private int $obLevel;

    protected function setUp(): void
    {
        // Instancia fresca por test: el stream no debe filtrarse entre casos.
        $this->ui = Ui::new(dirname(__DIR__).'/views');
        $this->obLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        $this->ui->discard();
        while (ob_get_level() > $this->obLevel) {
            ob_end_clean();
        }
    }

    /**
     * Captures everything a streaming block echoes.
     *
     * @param callable(Ui): void $block
     */
    private function capture(callable $block): string
    {
        ob_start();
        try {
            $block($this->ui);

            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        }
    }

    public function testOpenCloseProducesSameOutputAsArgumentStyle(): void
    {
        $expected = trim($this->ui->card(['class' => 'max-w-sm'], 'Bienvenido'));

        $actual = trim($this->capture(function (Ui $ui): void {
            $ui->open('card', ['class' => 'max-w-sm']);
            echo 'Bienvenido';
            echo $ui->close();
        }));

        self::assertSame($expected, $actual);
    }

    public function testKebabAndCamelAndNamespacedNamesAllResolve(): void
    {
        foreach (['card', 'card-header', 'CARD-HEADER', 'ui.card-content', 'blocks::nav-user'] as $name) {
            $html = $this->capture(function (Ui $ui) use ($name): void {
                $ui->open($name);
                echo 'x';
                echo $ui->close();
            });

            self::assertStringContainsString('data-slot', $html, "Name [$name] must render");
        }
    }

    public function testNestedStreamingMatchesDomStructure(): void
    {
        $html = $this->capture(function (Ui $ui): void {
            $ui->open('card', ['class' => 'wrap']);
            $ui->open('card-header');
            $ui->open('card-title');
            echo 'Título';
            echo $ui->close();
            echo $ui->close();
            echo $ui->close();
        });

        self::assertStringContainsString('data-slot="card-title"', $html);
        self::assertStringContainsString('>Título</div>', $html);
        self::assertStringContainsString('class="wrap ', $html);
    }

    public function testIntoTargetsNamedSlotThenReturnsToDefault(): void
    {
        $argumentStyle = $this->ui->input(
            ['type' => 'password', 'name' => 'pw'],
            leading: '<span data-mark="lead">L</span>',
        );

        $streamed = $this->capture(function (Ui $ui): void {
            $ui->open('input', ['type' => 'password', 'name' => 'pw']);
            $ui->into('leading');
            echo '<span data-mark="lead">L</span>';
            $ui->into();
            echo 'XDEFX';
            echo $ui->close();
        });
        $streamed = str_replace('XDEFX', '', $streamed);

        self::assertStringContainsString('<span data-mark="lead">L</span>', $streamed);
        self::assertStringContainsString('ps-9 pe-10', $streamed);
        
        self::assertSame(
            preg_replace('/\s+/', '', $argumentStyle),
            preg_replace('/\s+/', '', preg_replace('/texto por defecto/', '', $streamed)),
            'Streaming must equal argument-style output (minus the extra default text)',
        );
    }

    public function testTemplateVariablesAreVisibleWithoutUse(): void
    {
        $user = 'Ada';

        $html = $this->capture(function (Ui $ui) use ($user): void {
            $ui->open('badge', ['tone' => 'success']);
            // Interpolación directa: sin closures ni use().
            echo "Hola {$user}";
            echo $ui->close();
        });

        self::assertStringContainsString('Hola Ada', $html);
    }

    public function testUnbalancedCloseThrows(): void
    {
        $this->expectException(\LogicException::class);

        $this->ui->close();
    }

    public function testDiscardRestoresBufferLevel(): void
    {
        $levelBefore = ob_get_level();

        $this->ui->open('card');
        $this->ui->open('card-header');
        $this->ui->discard();

        self::assertSame($levelBefore, ob_get_level());
    }

    public function testMixedStylesCompose(): void
    {
        $html = $this->capture(function (Ui $ui): void {
            $ui->open('card', ['class' => 'mix']);
            echo $ui->cardTitle([], 'Título por método');
            $ui->open('card-description');
            echo 'Descripción por streaming';
            echo $ui->close();
            echo $ui->close();
        });

        self::assertStringContainsString('>Título por método</div>', $html);
        self::assertStringContainsString('Descripción por streaming', $html);
    }

    public function testUnknownComponentSuggestsFileStyleNames(): void
    {
        try {
            $this->ui->open('no-existe-nada');
            self::fail('Expected ComponentNotFoundException');
        } catch (ComponentNotFoundException $e) {
            self::assertStringContainsString('ui::', $e->getMessage());
        }
    }
}
