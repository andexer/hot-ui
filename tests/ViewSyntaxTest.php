<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\Filesystem;
use Components\Ui;
use PHPUnit\Framework\TestCase;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ViewSyntaxTest extends TestCase
{
    private string $root;
    private Ui $ui;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().'/hot-ui-syntax-'.uniqid();
        foreach (['ui', 'blocks'] as $ns) {
            $dir = $this->root.'/components/'.$ns;
            if (! Filesystem::ensureDirectory($dir)) {
                throw new \RuntimeException(sprintf('Unable to create [%s].', $dir));
            }
        }

        file_put_contents($this->root.'/components/ui/button.php', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, []));
?><button data-slot="button" <?= $attributes->twMerge('h-9 px-4 rounded-md') ?>><?= $slot ?></button>
PHP);

        file_put_contents($this->root.'/components/ui/card.php', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, []));
?><section data-slot="card" <?= $attributes ?>><?= $header ?? '' ?><?= $title ?? '' ?><?= $slot ?></section>
PHP);

        file_put_contents($this->root.'/components/ui/forward.php', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, []));
$forwarded = $attributes ?? new Components\Support\AttributeBag();
?><ui:button {{ $forwarded }}>ver</ui:button>
PHP);

        file_put_contents($this->root.'/components/blocks/kicker.php', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, ['text' => 'news']));
?><em data-slot="kicker"><?= e($text) ?> · <?= $slot ?></em>
PHP);

        $this->ui = Ui::new($this->root);
    }

    protected function tearDown(): void
    {
        if (! is_dir($this->root)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->root);
    }

    public function testRendersTagSyntaxPage(): void
    {
        file_put_contents($this->root.'/page.php', <<<'HTML'
<ui:card class="w-full">
    <ui:slot name="header"><blocks:kicker>portada</blocks:kicker></ui:slot>
    <ui:button :variant="$btnVariant">Entrar</ui:button>
</ui:card>
HTML);

        $out = $this->ui->view('page', ['btnVariant' => 'destructive']);

        self::assertStringContainsString(
            '<section data-slot="card"  class="w-full">',
            $out,
        );
        self::assertStringContainsString('<em data-slot="kicker">news · portada</em>', $out);
        self::assertStringContainsString(
            '<button data-slot="button" variant="destructive" class="h-9 px-4 rounded-md">',
            $out,
        );
        self::assertStringContainsString('Entrar</button>', $out);
    }

    public function testSecondRenderReusesCompiledCache(): void
    {
        file_put_contents($this->root.'/page.php', '<ui:button data-marca="cache-unica-987">Hola</ui:button>');

        $first = $this->ui->view('page', []);
        $second = $this->ui->view('page', []);

        self::assertSame('<button data-slot="button" data-marca="cache-unica-987" class="h-9 px-4 rounded-md">Hola</button>', $first);
        self::assertSame($first, $second);

        $cacheDir = sys_get_temp_dir().'/hotui-compiled';
        $matching = static function () use ($cacheDir): array {
            $files = glob($cacheDir.'/*.php') ?: [];

            return array_values(array_filter(
                $files,
                static fn (string $file): bool => (bool) str_contains((string) file_get_contents($file), 'cache-unica-987'),
            ));
        };

        $before = $matching();
        $this->ui->view('page', []);
        self::assertSame($before, $matching(), 'Second render must reuse the compiled file without writing a new one.');
        self::assertNotEmpty($before, 'Expected a compiled cache entry for this page source.');
    }

    public function testCacheRefreshesWhenSourceChanges(): void
    {
        file_put_contents($this->root.'/page.php', '<ui:button>primero</ui:button>');
        $first = $this->ui->view('page', []);
        self::assertStringContainsString('primero', $first);

        file_put_contents($this->root.'/page.php', '<ui:button>segundo</ui:button>');
        $second = $this->ui->view('page', []);
        self::assertStringContainsString('segundo', $second);
    }

    public function testMissingViewThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->ui->view('no-existe');
    }

    public function testDynamicAttributesAreEvaluated(): void
    {
        file_put_contents($this->root.'/page.php', '<ui:button :class="$a . \' \' . $b">x</ui:button>');

        $out = $this->ui->view('page', ['a' => 'uno', 'b' => 'dos']);

        self::assertStringContainsString('class="uno dos', $out);
    }

    public function testComponentTemplatesCompileTagSyntax(): void
    {
        file_put_contents($this->root.'/page.php', '<ui:forward class="extra" data-marca="fwd" />');

        $out = $this->ui->view('page', []);

        self::assertStringContainsString('<button data-slot="button" data-marca="fwd" class="extra h-9 px-4 rounded-md">ver</button>', $out);
    }

    public function testClassDirectiveEvaluatesConditionalClasses(): void
    {
        file_put_contents(
            $this->root.'/page.php',
            "<ui:button @class=\"['is-active' => \$active, 'm-2']\">x</ui:button>",
        );

        $on = $this->ui->view('page', ['active' => true]);
        self::assertStringContainsString('is-active', $on);
        self::assertStringContainsString('m-2', $on);

        $off = $this->ui->view('page', ['active' => false]);
        self::assertStringNotContainsString('is-active', $off);
        self::assertStringContainsString('m-2', $off);
    }

    public function testInlineSlotPlacesSelfClosingIntoNamedSlot(): void
    {
        file_put_contents($this->root.'/page.php', '<ui:card><ui:button slot="title" />texto</ui:card>');

        $out = $this->ui->view('page', []);

        self::assertStringContainsString(
            '<section data-slot="card" ><button data-slot="button" class="h-9 px-4 rounded-md"></button>texto</section>',
            $out,
        );
    }
}