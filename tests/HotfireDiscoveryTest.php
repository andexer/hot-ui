<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\ComponentPaths;
use Components\Hotfire\Config;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class HotfireDiscoveryTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-discovery-'.uniqid();
    }

    protected function tearDown(): void
    {
        Config::setShared(null);

        if (! is_dir($this->tmp)) {
            return;
        }

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

    public function testDiscoversNestedComponentsWithClassViewAndSidecars(): void
    {
        $paths = new ComponentPaths($this->tmp);
        $folder = $this->tmp.'/components/hotfire/post/🔥create';
        mkdir($folder, 0777, true);
        file_put_contents($folder.'/create.php', '<?php ');
        file_put_contents($folder.'/create.view.php', '<form></form>');
        file_put_contents($folder.'/create.js', '// js');
        file_put_contents($folder.'/create.css', '.x{}');

        $components = $paths->discover();

        self::assertSame(['post.create'], array_column($components, 'name'));
        self::assertSame($folder, $components[0]['folder']);
        self::assertSame($folder.'/create.php', $components[0]['class']);
        self::assertSame($folder.'/create.view.php', $components[0]['view']);
        self::assertSame(
            [$folder.'/create.css', $folder.'/create.js'],
            $components[0]['sidecars'],
        );
    }

    public function testDiscoverFindsFirstLevelAndDeeplyNestedComponents(): void
    {
        $paths = new ComponentPaths($this->tmp);
        mkdir($this->tmp.'/components/hotfire/🔥counter', 0777, true);
        file_put_contents($this->tmp.'/components/hotfire/🔥counter/counter.php', '<?php ');
        mkdir($this->tmp.'/components/hotfire/admin/blog/🔥publish', 0777, true);
        file_put_contents($this->tmp.'/components/hotfire/admin/blog/🔥publish/publish.view.php', '<p></p>');

        $components = $paths->discover(false);

        self::assertSame(['admin.blog.publish', 'counter'], array_column($components, 'name'));
        self::assertArrayNotHasKey('class', $components[0]);
        self::assertArrayNotHasKey('sidecars', $components[0]);
    }

    public function testDiscoverMissingRootReturnsEmptyList(): void
    {
        self::assertSame([], (new ComponentPaths($this->tmp))->discover());
    }

    public function testUnmarkedAndMalformedFoldersAreIgnored(): void
    {
        $paths = new ComponentPaths($this->tmp);
        mkdir($this->tmp.'/components/hotfire/post/plain-create', 0777, true);
        file_put_contents($this->tmp.'/components/hotfire/post/plain-create/create.php', '<?php ');
        mkdir($this->tmp.'/components/hotfire/🔥', 0777, true);

        self::assertSame([], $paths->discover(false));
    }

    public function testRespectsCustomViewPrefixAndEmoji(): void
    {
        Config::setShared(new Config(viewPrefix: 'sections'));
        $paths = new ComponentPaths($this->tmp, '⚡');
        mkdir($this->tmp.'/sections/blog/⚡draft', 0777, true);
        file_put_contents($this->tmp.'/sections/blog/⚡draft/draft.php', '<?php ');

        $components = $paths->discover();

        self::assertSame(['blog.draft'], array_column($components, 'name'));
        self::assertSame($this->tmp.'/sections/blog/⚡draft/draft.php', $components[0]['class']);
    }

    public function testDiscoversGeneratedScaffoldBottomLogout(): void
    {
        $paths = new ComponentPaths($this->tmp);
        $folder = $this->tmp.'/components/hotfire/🔥bottom-logout';
        mkdir($folder, 0777, true);
        foreach (['bottom-logout.php', 'bottom-logout.view.php', 'bottom-logout.test.php'] as $file) {
            file_put_contents($folder.'/'.$file, 'x');
        }

        $components = $paths->discover();

        self::assertSame(['bottom-logout'], array_column($components, 'name'));
        self::assertSame($folder.'/bottom-logout.php', $components[0]['class']);
        self::assertSame($folder.'/bottom-logout.view.php', $components[0]['view']);
        self::assertSame([$folder.'/bottom-logout.test.php'], $components[0]['sidecars']);
    }
}
