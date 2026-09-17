<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Commands\Exception\CommandException;
use Components\Support\Exception\CompiledTemplateNotFoundException;
use Components\Support\Exception\CompiledViewWriteException;
use Components\Exception\ComponentNotFoundException;
use Components\Support\Exception\DirectoryCreateException;
use Components\Support\Exception\FileCopyException;
use Components\Exception\HotUiException;
use Components\Support\Exception\IllegalAttributeNameException;
use Components\Support\Exception\ImmutableAttributeBagException;
use Components\Commands\Exception\InvalidPublishTargetException;
use Components\Exception\MissingComponentNamespaceException;
use Components\Exception\MissingDirectoryException;
use Components\Exception\ReservedPropertyNameException;
use Components\Support\Exception\SupportException;
use Components\Support\Exception\TemplateNotFoundException;
use Components\Exception\UiPipelineException;
use Components\Support\Exception\UnbalancedTagException;
use Components\Support\Exception\UnknownGroupException;
use Components\Support\Exception\UnknownTemplateNamespaceException;
use Components\Exception\UnresolvedDirectoryException;
use Components\Exception\UnsupportedPositionalArgumentException;
use Components\Hotfire\Exception\HotfireException;
use Components\Hotfire\Exception\StubTemplateNotFoundException;
use Components\Support\Assets;
use Components\Support\AttributeBag;
use Components\Support\RenderContext;
use Components\Support\Slot;
use Components\Support\TemplateCompiler;
use Components\Support\TemplateRenderer;
use Components\Support\ViewCompiler;
use Components\Support\Views;
use Components\Ui;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Rule 5.1 is only satisfied when callers can catch the failure mode, so this
 * suite pins two things: every package error carries the marker and the SPL
 * base its nature deserves, and the conditions actually raise the specific
 * class rather than a generic one.
 */
final class CoreExceptionTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-exceptions-'.uniqid();
        $root = $this->tmp.'/views';
        if (! is_dir($root.'/components/ui') && ! mkdir($root.'/components/ui', 0o775, true) && ! is_dir($root.'/components/ui')) {
            throw new DirectoryCreateException($root);
        }

        file_put_contents($root.'/components/ui/button.php', <<<'PHP'
        <?php

        extract(props($__ctx, []));
        ?>button
        PHP);
    }

    protected function tearDown(): void
    {
        if (! is_dir($this->tmp)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmp, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->tmp);
    }

    /**
     * @return array<string, array{0: class-string, 1: class-string}>
     */
    public static function exceptionBases(): array
    {
        return [
            UnknownGroupException::class => [UnknownGroupException::class, \InvalidArgumentException::class],
            IllegalAttributeNameException::class => [IllegalAttributeNameException::class, \InvalidArgumentException::class],
            UnbalancedTagException::class => [UnbalancedTagException::class, \InvalidArgumentException::class],
            InvalidPublishTargetException::class => [InvalidPublishTargetException::class, \InvalidArgumentException::class],
            ReservedPropertyNameException::class => [ReservedPropertyNameException::class, \InvalidArgumentException::class],
            UnsupportedPositionalArgumentException::class => [UnsupportedPositionalArgumentException::class, \InvalidArgumentException::class],
            UnresolvedDirectoryException::class => [UnresolvedDirectoryException::class, \InvalidArgumentException::class],
            ImmutableAttributeBagException::class => [ImmutableAttributeBagException::class, \LogicException::class],
            UiPipelineException::class => [UiPipelineException::class, \LogicException::class],
            DirectoryCreateException::class => [DirectoryCreateException::class, \RuntimeException::class],
            FileCopyException::class => [FileCopyException::class, \RuntimeException::class],
            CompiledViewWriteException::class => [CompiledViewWriteException::class, \RuntimeException::class],
            CompiledTemplateNotFoundException::class => [CompiledTemplateNotFoundException::class, \RuntimeException::class],
            TemplateNotFoundException::class => [TemplateNotFoundException::class, \RuntimeException::class],
            UnknownTemplateNamespaceException::class => [UnknownTemplateNamespaceException::class, \RuntimeException::class],
            MissingDirectoryException::class => [MissingDirectoryException::class, \RuntimeException::class],
            MissingComponentNamespaceException::class => [MissingComponentNamespaceException::class, \RuntimeException::class],
            ComponentNotFoundException::class => [ComponentNotFoundException::class, \RuntimeException::class],
        ];
    }

    /**
     * @param class-string $class
     * @param class-string $base
     */
    #[DataProvider('exceptionBases')]
    public function testEveryCoreExceptionCarriesTheMarkerAndItsSplBase(string $class, string $base): void
    {
        self::assertTrue(is_subclass_of($class, HotUiException::class), $class.' must implement HotUiException');
        self::assertTrue(is_subclass_of($class, $base), $class.' must extend '.$base);
    }

    public function testHotfireExceptionsNarrowThePackageMarker(): void
    {
        self::assertTrue(is_subclass_of(HotfireException::class, HotUiException::class));
        self::assertTrue(is_subclass_of(StubTemplateNotFoundException::class, HotUiException::class));
    }

    public function testEachLayerNarrowsThePackageMarkerWithItsOwnFamily(): void
    {
        self::assertTrue(is_subclass_of(SupportException::class, HotUiException::class));
        self::assertTrue(is_subclass_of(CommandException::class, HotUiException::class));
    }

    /**
     * The Support family is exactly the failures of the layer that renders,
     * compiles and publishes: catching it must cover all of them.
     *
     * @return array<string, array{0: class-string}>
     */
    public static function supportExceptions(): array
    {
        return [
            DirectoryCreateException::class => [DirectoryCreateException::class],
            FileCopyException::class => [FileCopyException::class],
            UnknownGroupException::class => [UnknownGroupException::class],
            CompiledViewWriteException::class => [CompiledViewWriteException::class],
            CompiledTemplateNotFoundException::class => [CompiledTemplateNotFoundException::class],
            TemplateNotFoundException::class => [TemplateNotFoundException::class],
            UnknownTemplateNamespaceException::class => [UnknownTemplateNamespaceException::class],
            UnbalancedTagException::class => [UnbalancedTagException::class],
            IllegalAttributeNameException::class => [IllegalAttributeNameException::class],
            ImmutableAttributeBagException::class => [ImmutableAttributeBagException::class],
        ];
    }

    /** @param class-string $class */
    #[DataProvider('supportExceptions')]
    public function testSupportExceptionsBelongToTheSupportFamily(string $class): void
    {
        self::assertTrue(is_subclass_of($class, SupportException::class), $class.' must implement SupportException');
        self::assertTrue(is_subclass_of($class, HotUiException::class), $class.' must still be a package failure');
    }

    public function testCommandExceptionsBelongToTheCommandFamily(): void
    {
        self::assertTrue(is_subclass_of(InvalidPublishTargetException::class, CommandException::class));
        self::assertTrue(is_subclass_of(InvalidPublishTargetException::class, \InvalidArgumentException::class));
    }

    public function testTheSupportMarkerCatchesWhatTheLayerThrows(): void
    {
        try {
            (new ViewCompiler())->compile('<ui:card>cuerpo');
            self::fail('Unclosed tag compiled.');
        } catch (SupportException $exception) {
            self::assertInstanceOf(UnbalancedTagException::class, $exception);
        }

        try {
            (new AttributeBag(['class' => 'p-2']))->offsetSet('class', 'gone');
            self::fail('The attribute bag accepted a write.');
        } catch (SupportException $exception) {
            self::assertInstanceOf(ImmutableAttributeBagException::class, $exception);
        }
    }

    public function testUnknownGroupsCarryTheirKindGroupAndExpectations(): void
    {
        try {
            Assets::path('images');
            self::fail('Assets::path() accepted an unknown group.');
        } catch (UnknownGroupException $exception) {
            self::assertSame('asset', $exception->getKind());
            self::assertSame('images', $exception->getGroup());
            self::assertSame(['css', 'js'], $exception->getExpected());
            self::assertSame('Unknown asset group [images]; expected css or js.', $exception->getMessage());
        }

        $this->expectException(UnknownGroupException::class);
        $this->expectExceptionMessage('Unknown views group [images]; expected components, layouts or partials.');

        Views::path('images');
    }

    public function testAttributeBagRefusesWritesAndIllegalNames(): void
    {
        $bag = new AttributeBag(['class' => 'p-2']);

        try {
            $bag['class'] = 'gone';
            self::fail('AttributeBag accepted a write.');
        } catch (ImmutableAttributeBagException $exception) {
            self::assertInstanceOf(\LogicException::class, $exception);
        }

        try {
            (string) new AttributeBag(['9bad' => 'x']);
            self::fail('AttributeBag rendered an illegal name.');
        } catch (IllegalAttributeNameException $exception) {
            self::assertSame('9bad', $exception->getName());
        }
    }

    public function testUnbalancedTagsReportTagAndExpectation(): void
    {
        $compiler = new ViewCompiler();

        try {
            $compiler->compile('<ui:card>cuerpo');
            self::fail('Unclosed tag compiled.');
        } catch (UnbalancedTagException $exception) {
            self::assertSame('ui.card', $exception->getTag());
            self::assertNull($exception->getExpected());
            self::assertStringContainsString('Unclosed Hot-UI tag [<ui:card>]', $exception->getMessage());
        }

        try {
            $compiler->compile('<ui:card></ui:button>');
            self::fail('Mismatched tag compiled.');
        } catch (UnbalancedTagException $exception) {
            self::assertSame('ui.button', $exception->getTag());
            self::assertSame('ui.card', $exception->getExpected());
            self::assertSame('Mismatched closing tag [</ui:button>]; expected [</ui:card>].', $exception->getMessage());
        }

        $this->expectException(UnbalancedTagException::class);
        $this->expectExceptionMessage('Unexpected closing tag [</ui:card>] without an opening tag');

        $compiler->compile('</ui:card>');
    }

    public function testMissingViewsTreeIsTyped(): void
    {
        try {
            new Ui($this->tmp.'/absent');
            self::fail('Ui accepted a missing components directory.');
        } catch (MissingDirectoryException $exception) {
            self::assertSame($this->tmp.'/absent/components', $exception->getPath());
        }

        $empty = $this->tmp.'/empty';
        if (! is_dir($empty.'/components') && ! mkdir($empty.'/components', 0o775, true)) {
            throw new DirectoryCreateException($empty.'/components');
        }

        $this->expectException(MissingComponentNamespaceException::class);
        $this->expectExceptionMessage('No component namespaces found inside ['.$empty.'/components].');

        new Ui($empty);
    }

    public function testTemplateResolutionFailuresAreTyped(): void
    {
        $renderer = new TemplateRenderer($this->tmp.'/views', ['ui' => $this->tmp.'/views/components/ui'], new Ui($this->tmp.'/views'));

        try {
            $renderer->render('nope::button');
            self::fail('Renderer accepted an unregistered namespace.');
        } catch (UnknownTemplateNamespaceException $exception) {
            self::assertSame('nope', $exception->getNamespace());
            self::assertSame('nope::button', $exception->getTemplate());
        }

        try {
            $renderer->render('missing');
            self::fail('Renderer accepted a missing template.');
        } catch (TemplateNotFoundException $exception) {
            self::assertSame('missing', $exception->getTemplate());
            self::assertStringContainsString('not found (resolved to [', $exception->getMessage());
        }
    }

    public function testMissingPageViewIsTyped(): void
    {
        $ui = new Ui($this->tmp.'/views');

        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessage('not found (resolved to [');

        $ui->view('absent-page', [], $this->tmp.'/views');
    }

    public function testReservedPropNamesAreTyped(): void
    {
        $this->expectException(ReservedPropertyNameException::class);

        props(new RenderContext([], new Slot()), ['attributes' => 'boom']);
    }

    public function testPipelineMisuseIsTyped(): void
    {
        $ui = new Ui($this->tmp.'/views');

        try {
            $ui->into('header');
            self::fail('into() ran without open().');
        } catch (UiPipelineException $exception) {
            self::assertSame('into', $exception->getMethod());
            self::assertSame('into() called without a matching open().', $exception->getMessage());
        }

        try {
            Ui::publishShared(['tone' => 'dark']);
            self::fail('share() ran outside a render.');
        } catch (UiPipelineException $exception) {
            self::assertSame('share', $exception->getMethod());
        }

        $this->expectException(UiPipelineException::class);
        $this->expectExceptionMessage('close() called without a matching open().');

        $ui->close();
    }

    public function testUnsupportedPositionalArgumentIsTyped(): void
    {
        $ui = new Ui($this->tmp.'/views');

        $this->expectException(UnsupportedPositionalArgumentException::class);
        $this->expectExceptionMessage('Unsupported positional argument #0 of type [int]');

        $ui->renderComponent('ui.button', [42]);
    }

    public function testUnwritableCompileDirectoryIsTyped(): void
    {
        $blocker = $this->tmp.'/not-a-directory';
        if (file_put_contents($blocker, 'x') === false) {
            throw new DirectoryCreateException($blocker);
        }

        $compiler = new TemplateCompiler($blocker);

        $this->expectException(DirectoryCreateException::class);
        $this->expectExceptionMessage('compiled views directory ['.$blocker.']');

        $compiler->compile($this->tmp.'/views/components/ui/button.php');
    }
}
