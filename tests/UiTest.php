<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\RenderContext;
use Components\Support\Slot;
use Components\Ui;
use PHPUnit\Framework\TestCase;

final class UiTest extends TestCase
{
    private static ?Ui $ui = null;

    private function ui(): Ui
    {
        return self::$ui ??= Ui::new(dirname(__DIR__).'/views');
    }

    public function testBareAliasAndQualifiedNameBothResolve(): void
    {
        self::assertTrue($this->ui()->has('card'));
        self::assertTrue($this->ui()->has('ui.card'));
        self::assertTrue($this->ui()->has('uiCard'));

        $viaBare = $this->ui()->card([], 'X');
        $viaQualified = $this->ui()->renderComponent('ui.card', [[], 'X']);

        self::assertSame($viaBare, $viaQualified);
    }

    public function testLoginCardEndToEnd(): void
    {
        $html = trim((string) $this->ui()->card(['class' => 'max-w-sm'], function (): void { ?>
            <?= ui()->uiCardHeader([], function (): void { ?>
                <?= ui()->uiCardTitle([], 'Welcome back') ?>
                <?= ui()->uiCardDescription([], 'Sign in to continue.') ?>
            <?php }) ?>
            <?= ui()->uiCardContent(['class' => 'space-y-3'], function (): void { ?>
                <?= ui()->uiInput(['type' => 'email', 'placeholder' => 'm@example.com']) ?>
                <?= ui()->uiButton(['class' => 'w-full'], 'Sign in') ?>
            <?php }) ?>
        <?php }));

        self::assertStringContainsString('data-slot="card" class="max-w-sm', $html);
        self::assertStringContainsString('>Welcome back</div>', $html);
        self::assertStringContainsString('placeholder="m@example.com"', $html);
        self::assertStringContainsString('data-slot="button"', $html);
        self::assertStringContainsString('class="w-full inline-flex', $html);
        self::assertStringContainsString('>Sign in</button>', $html);
    }

    public function testNamedSlotsReachTemplates(): void
    {
        $html = $this->ui()->input(
            ['type' => 'password', 'name' => 'pw'],
            leading: '<span data-x="lead">L</span>',
        );

        self::assertStringContainsString('x-data="{ show: false }"', $html);
        self::assertStringContainsString('<span data-x="lead">L</span>', $html);
        self::assertStringContainsString('ps-9 pe-10', $html, 'Adornment padding must be applied');
    }

    public function testUnknownComponentThrowsWithSuggestions(): void
    {
        $this->expectException(\Components\Exception\ComponentNotFoundException::class);

        $this->ui()->renderComponent('definitely-not-a-component', []);
    }

    public function testEscapingOfHostileSlotText(): void
    {
        // Slot content is trusted HTML; but prop-driven text must be escaped.
        $html = $this->ui()->badge(['href' => 'javascript:alert(1)'], 'bad');

        self::assertStringContainsString('href="#"', $html, 'Dangerous scheme must collapse');
        self::assertStringNotContainsString('javascript:', $html);
    }

    public function testSharePropagatesToDescendants(): void
    {
        // description-item consumes layout/bordered via aware(); the parent
        // publishes them through share() before rendering the child.
        $html = $this->ui()->renderComponent('descriptionItem', [[], function (): string {
            share(['layout' => 'vertical', 'bordered' => true]);

            return ui()->uiDescriptionItem(['term' => 'Term'], 'Value body');
        }]);

        self::assertStringContainsString('flex flex-col gap-1 px-4 py-3', $html);
        self::assertStringContainsString('<dt', $html);
        self::assertStringContainsString('Term', $html);
        self::assertStringContainsString('Value body', $html);
    }
}
