<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Ci4\Ci4;
use Components\HotUI;
use Components\Support\Assets;
use Components\Ui;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PackageIntegrationTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-test-'.uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmp)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->tmp, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );
            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->tmp);
        }
    }

    public function testZeroDependenciesRequirePhpOnly(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['php' => '^8.2'], $composer['require'], 'Runtime must depend only on the language');
    }

    public function testHelpersAndEntryPointsAreAutoloadable(): void
    {
        require_once dirname(__DIR__).'/src/helpers.php';

        self::assertTrue(function_exists('ui'));
        self::assertTrue(function_exists('e'));
        self::assertTrue(function_exists('js'));
        self::assertTrue(function_exists('safe_url'));
        self::assertNotNull(HotUI::shared());
    }

    public function testSharedAndInstanceRenderTheSamePage(): void
    {
        $page = HotUI::instance()->render('layouts/app', ['content' => 'Hola mundo']);

        self::assertStringContainsString('<html', $page);
        self::assertStringContainsString('Hola mundo', $page);
    }

    public function testHostTemplateCanCallComponentsThroughThis(): void
    {
        $root = $this->tmp.'/views';
        $this->makeComponent($root, 'ui', 'hello', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, ['tone' => 'default']));
$tones = ['default' => 'bg-card', 'success' => 'bg-emerald-500'];
?>
<span data-slot="hello" <?= $attributes->twMerge($tones[$tone]) ?>><?= $slot ?></span>
PHP);
        file_put_contents($root.'/page.php', '<div id="wrap"><?= $this->uiHello(["tone" => "success", "class" => "x"], "¡Hola!") ?></div>');

        $html = (new Ui($root))->render('page');

        self::assertStringContainsString('data-slot="hello"', $html);
        self::assertStringContainsString('x bg-emerald-500', $html, 'twMerge must merge variant + caller class');
        self::assertStringContainsString('¡Hola!', $html);
    }

    public function testHostTemplateCanCallNamespaceComponentsThroughThis(): void
    {
        $root = $this->tmp.'/views';
        $this->makeComponent($root, 'blocks', 'kicker', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, []));
?><b data-slot="kicker"><?= $slot ?></b>
PHP);
        file_put_contents($root.'/page.php', '<?= $this->blocksKicker([], "genial") ?> · <?= $this->blocksKicker([], "otra") ?>');

        $html = (new Ui($root))->render('page');

        self::assertSame('<b data-slot="kicker">genial</b> · <b data-slot="kicker">otra</b>', $html);
    }

    public function testCi4BridgeRendersAndPublishes(): void
    {
        $public = $this->tmp.'/public';

        $html = Ci4::render('layouts/guest', ['content' => Ci4::boot()->uiBadge([], 'fast')]);
        self::assertStringContainsString('<html', $html);
        self::assertStringContainsString('data-slot="badge"', $html);

        $copied = Ci4::publish($public);
        self::assertFileExists($public.'/css/hot-ui.css');
        self::assertGreaterThan(0, $copied['css']);
        self::assertGreaterThan(0, $copied['js']);
    }

    public function testCi4PublishWithoutPublicDirOutsideCi4Throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Ci4::publish();
    }

    public function testViewsAndAssetsPathsExist(): void
    {
        self::assertFileExists(HotUI::views().'/components/ui/card.php');
        self::assertFileExists(HotUI::assets('css').'/hot-ui.css');
        self::assertFileExists(HotUI::assets('js').'/app.js');
    }

    public function testPublishCopiesRuntimeAssetsOnly(): void
    {
        $public = $this->tmp.'/public';
        $copied = HotUI::publish($public);

        self::assertArrayHasKey('css', $copied);
        self::assertArrayHasKey('js', $copied);
        self::assertGreaterThan(0, $copied['css']);
        self::assertGreaterThan(0, $copied['js']);
        self::assertFileExists($public.'/css/hot-ui.css');
        self::assertFileExists($public.'/js/app.js');
        self::assertFileExists($public.'/js/src/app.js');

        foreach ($this->allFiles($public) as $path) {
            self::assertStringEndsNotWith('.ts', $path, 'TypeScript sources must not ship to public/');
            self::assertStringEndsNotWith('.map', $path, 'Source maps must not ship to public/');
        }
    }

    public function testAutoPublishDetectsPublicUnderCwd(): void
    {
        $root = $this->tmp.'/app';
        mkdir($root.'/public', 0o775, true);
        $cwd = getcwd();
        try {
            chdir($root);
            $copied = HotUI::autoPublish();
        } finally {
            if ($cwd !== false) {
                chdir($cwd);
            }
        }

        self::assertFileExists($root.'/public/css/hot-ui.css');
        self::assertFileExists($root.'/public/js/app.js');
        self::assertGreaterThan(0, $copied['css']);
        self::assertGreaterThan(0, $copied['js']);
    }

    public function testAutoPublishIgnoresComposerEventObject(): void
    {
        $root = $this->tmp.'/app';
        mkdir($root.'/public', 0o775, true);
        $cwd = getcwd();
        try {
            chdir($root);
            $copied = HotUI::autoPublish(new \stdClass());
        } finally {
            if ($cwd !== false) {
                chdir($cwd);
            }
        }

        self::assertFileExists($root.'/public/css/hot-ui.css');
        self::assertGreaterThan(0, $copied['js']);
    }

    public function testAutoPublishWithExplicitDirStillWorks(): void
    {
        $public = $this->tmp.'/public_html';
        $copied = HotUI::autoPublish($public);

        self::assertFileExists($public.'/css/hot-ui.css');
        self::assertGreaterThan(0, $copied['js']);
    }

    public function testAssetsRejectUnknownGroup(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Assets::path('fonts');
    }

    /** @return list<string> */
    private function allFiles(string $dir): array
    {
        $out = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }

    /**
     * Writes a minimal component template under $root/components/<ns>/<name>.php.
     */
    private function makeComponent(string $root, string $ns, string $name, string $body): void
    {
        $dir = $root.'/components/'.$ns;
        if (! is_dir($dir) && ! mkdir($dir, 0o775, true) && ! is_dir($dir)) {
            throw new \RuntimeException("Unable to create [$dir]");
        }
        file_put_contents($dir.'/'.$name.'.php', $body);
    }
}