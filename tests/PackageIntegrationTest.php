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

        // composer-plugin-api is a virtual metapackage provided by Composer
        // itself, not a real dependency: runtime still needs only PHP.
        self::assertSame(
            ['php' => '^8.2', 'composer-plugin-api' => '^2.6'],
            $composer['require'],
            'Runtime must depend only on the language',
        );
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
        self::assertFileExists($public.'/css/hot-ui.min.css');
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
        self::assertFileExists(HotUI::assets('css').'/hot-ui.min.css');
        self::assertFileExists(HotUI::assets('js').'/app.js');
    }

    public function testShippedCssIsCompiledNotTailwindSource(): void
    {
        $min = (string) file_get_contents(HotUI::assets('css').'/hot-ui.min.css');
        $src = (string) file_get_contents(HotUI::assets('css').'/hot-ui.css');

        self::assertStringContainsString('@theme', $src, 'Source must keep Tailwind v4 directives');
        self::assertStringNotContainsString('@import \'tailwindcss\'', $min, 'Consumers must get compiled CSS, no imports');
        self::assertStringNotContainsString('@theme', $min);
        self::assertStringContainsString('.bg-background', $min, 'Compiled bundle must include component utilities');
    }

    public function testComposerPluginSubscribesToInstallAndUpdateWithoutRootScripts(): void
    {
        $plugin = new \Components\Composer\HotUIPlugin();
        $composer = json_decode((string) file_get_contents(dirname(__DIR__).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertInstanceOf(\Composer\Plugin\PluginInterface::class, $plugin);
        self::assertInstanceOf(\Composer\EventDispatcher\EventSubscriberInterface::class, $plugin);
        self::assertSame('composer-plugin', $composer['type'], 'Package must be a Composer plugin to publish on its own');
        self::assertSame('Components\\Composer\\HotUIPlugin', $composer['extra']['class']);
        self::assertSame(
            [
                \Composer\Script\ScriptEvents::POST_INSTALL_CMD => 'publishAssets',
                \Composer\Script\ScriptEvents::POST_UPDATE_CMD => 'publishAssets',
            ],
            \Components\Composer\HotUIPlugin::getSubscribedEvents(),
        );
    }

    public function testPublishCopiesRuntimeAssetsOnly(): void
    {
        $public = $this->tmp.'/public';
        $copied = HotUI::publish($public);

        self::assertArrayHasKey('css', $copied);
        self::assertArrayHasKey('js', $copied);
        self::assertGreaterThan(0, $copied['css']);
        self::assertGreaterThan(0, $copied['js']);
        self::assertFileExists($public.'/css/hot-ui.min.css');
        self::assertFileExists($public.'/js/app.js');
        self::assertFileDoesNotExist($public.'/js/src/app.js', 'js/src is maintainer source, not host runtime');

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

        self::assertFileExists($public.'/css/hot-ui.min.css');
        self::assertGreaterThan(0, $copied['js']);
    }

    public function testAssetsRejectUnknownGroup(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Assets::path('fonts');
    }

    public function testPublishViewsCopiesComponentsLayoutsAndPartials(): void
    {
        $target = $this->tmp.'/app/Views/hotui';
        $copied = HotUI::publishViews($target);

        self::assertArrayHasKey('components', $copied);
        self::assertArrayHasKey('layouts', $copied);
        self::assertArrayHasKey('partials', $copied);
        self::assertGreaterThan(300, $copied['components']);
        self::assertGreaterThan(0, $copied['layouts']);
        self::assertGreaterThan(0, $copied['partials']);

        self::assertFileExists($target.'/components/ui/card.php');
        self::assertFileExists($target.'/layouts/app.php');
        self::assertFileExists($target.'/partials/meta.php');

        foreach ($this->allFiles($target) as $path) {
            self::assertStringEndsWith('.php', $path, 'publishViews must only copy PHP views');
        }
    }

    public function testPublishedViewsOwnedByAppTakeOverRendering(): void
    {
        $owned = $this->tmp.'/app2/hotui';
        HotUI::publishViews($owned);

        $badgePath = $owned.'/components/ui/badge.php';
        $customized = file_get_contents($badgePath);
        self::assertIsString($customized);
        $customized = str_replace('data-slot="badge"', 'data-slot="badge" data-owned="si"', $customized);
        file_put_contents($badgePath, $customized);

        $ownedUi = HotUI::instance(['view_path' => $owned]);
        $html = $ownedUi->render('layouts/guest', ['content' => $ownedUi->badge([], 'yo')]);

        self::assertStringContainsString('data-slot="badge"', $html);
        self::assertStringContainsString('data-owned="si"', $html, 'Host copy must take over rendering when view_path is switched');
    }

    public function testPublishViewsRejectsUnknownGroup(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        HotUI::publishViews($this->tmp.'/x', ['fonts']);
    }

    public function testPublishViewsDefaultsToNativeAppViewsFolder(): void
    {
        $root = $this->tmp.'/proj';
        mkdir($root, 0o775, true);
        $cwd = getcwd();
        try {
            chdir($root);
            HotUI::publishViews();
        } finally {
            if ($cwd !== false) {
                chdir($cwd);
            }
        }

        self::assertFileExists($root.'/app/Views/components/ui/card.php');
        self::assertFileExists($root.'/app/Views/layouts/app.php');
        self::assertFileExists($root.'/app/Views/partials/meta.php');
        self::assertDirectoryDoesNotExist($root.'/app/Views/hotui', 'No intermediate hotui/ folder');
    }

    public function testSparkCommandRegisteredForCi4Discovery(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['Components\\Commands'], $composer['extra']['codeigniter4']['commands']);

        $file = dirname(__DIR__).'/src/Components/Commands/PublishCommand.php';
        self::assertFileExists($file);
        $source = (string) file_get_contents($file);
        self::assertStringContainsString('class PublishCommand', $source);
        self::assertStringContainsString('extends BaseCommand', $source);
        self::assertStringContainsString("'hot-ui:publish'", $source);
        self::assertStringContainsString('Ci4::publishViews()', $source);
        self::assertStringContainsString('Ci4::publish()', $source);
        self::assertStringNotContainsString('$this->param(', $source, 'BaseCommand in CI4.7 has no param() helper');
        self::assertStringContainsString('str_starts_with($arg, \'-\')', $source);
        self::assertStringContainsString('$arg === null', $source, 'CI4 passes null entries in $params');
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