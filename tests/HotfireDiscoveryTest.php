<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\ComponentGenerator;
use Components\Hotfire\ComponentPaths;
use Components\Hotfire\Config;
use InvalidArgumentException;
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

    public function testDiscoversNestedComponentWithClassTemplateAndSidecars(): void
    {
        $folder = $this->scaffold('components/hotfire/post/🔥create', [
            'create.php',
            'create.view.php',
            'create.js',
            'create.css',
        ]);

        $components = (new ComponentPaths($this->tmp))->discover();

        self::assertCount(1, $components);
        self::assertSame('post.create', $components[0]['name']);
        self::assertSame($folder, $components[0]['folder']);
        self::assertSame($folder.'/create.php', $components[0]['class']);
        self::assertSame($folder.'/create.view.php', $components[0]['view']);
        self::assertSame([$folder.'/create.css', $folder.'/create.js'], $components[0]['sidecars']);
    }

    public function testDiscoversFirstLevelAndDeeplyNestedComponentsSortedByName(): void
    {
        $this->scaffold('components/hotfire/🔥counter', ['counter.php']);
        $this->scaffold('components/hotfire/admin/blog/🔥publish', ['publish.view.php']);
        $this->scaffold('components/hotfire/🔥bottom-logout', ['bottom-logout.php']);

        $components = (new ComponentPaths($this->tmp))->discover();

        self::assertSame(['admin.blog.publish', 'bottom-logout', 'counter'], array_column($components, 'name'));

        // Every entry keeps the same shape, with null/empty values for the
        // artifacts a component does not have.
        self::assertSame(['name', 'folder', 'class', 'view', 'sidecars'], array_keys($components[0]));
        self::assertNull($components[0]['class']);
        self::assertSame([], $components[0]['sidecars']);
        self::assertSame($components[0]['folder'].'/publish.view.php', $components[0]['view']);
    }

    public function testMissingRootReturnsEmptyList(): void
    {
        self::assertSame([], (new ComponentPaths($this->tmp))->discover());
    }

    public function testDiscoveryRequiresAViewsRoot(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ComponentPaths())->discover();
    }

    public function testUnmarkedAndMalformedFoldersAreIgnored(): void
    {
        $this->scaffold('components/hotfire/post/plain-create', ['create.php']);
        $this->scaffold('components/hotfire/🔥', []);
        $this->scaffold('components/hotfire/post/🔥Create', ['create.php']);

        self::assertSame([], (new ComponentPaths($this->tmp))->discover());
    }

    public function testStrayFilesAreNotReportedAsSidecars(): void
    {
        $folder = $this->scaffold('components/hotfire/🔥counter', [
            'counter.php',
            'counter.view.php',
            'counter.js',
            '.DS_Store',
            'notes.txt',
            'Thumbs.db',
        ]);

        $components = (new ComponentPaths($this->tmp))->discover();

        self::assertSame([$folder.'/counter.js'], $components[0]['sidecars']);
    }

    public function testCustomPrefixAndEmojiAreHonored(): void
    {
        Config::setShared(new Config(viewPrefix: 'sections'));
        $folder = $this->scaffold('sections/blog/⚡draft', ['draft.php', 'draft.css']);

        $paths = new ComponentPaths($this->tmp, '⚡');
        $components = $paths->discover();

        self::assertSame(['blog.draft'], array_column($components, 'name'));
        self::assertSame($folder.'/draft.php', $components[0]['class']);
        self::assertSame([$folder.'/draft.css'], $components[0]['sidecars']);
        self::assertSame($this->tmp.'/sections', $paths->hotfireRoot());
        self::assertSame('sections/blog/⚡draft', $paths->folder('blog.draft'));
        self::assertSame('sections/blog/⚡draft/draft.php', $paths->classRelative('blog.draft'));
    }

    public function testEmojiMustBeDirectorySafe(): void
    {
        foreach (['', '🔥/', 'a.b', 'a\\b'] as $unsafe) {
            try {
                new ComponentPaths($this->tmp, $unsafe);
                self::fail('Expected InvalidArgumentException for emoji ['.$unsafe.'].');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testDiscoveryAgreesWithTheGenerator(): void
    {
        $generator = new ComponentGenerator($this->tmp, 'App\\Components');
        $classPath = $generator->classPath('bottom-logout');
        mkdir(dirname($classPath), 0777, true);
        file_put_contents($classPath, $generator->classContent('bottom-logout', []));
        file_put_contents($generator->viewPath('bottom-logout'), $generator->viewContent('bottom-logout', []));

        $components = (new ComponentPaths($this->tmp))->discover();

        self::assertCount(1, $components);
        self::assertSame('bottom-logout', $components[0]['name']);
        self::assertSame(dirname($classPath), $components[0]['folder']);
        self::assertSame($classPath, $components[0]['class']);
        self::assertSame($generator->viewPath('bottom-logout'), $components[0]['view']);
        self::assertSame('components/hotfire/🔥bottom-logout/bottom-logout.view', $generator->viewRelative('bottom-logout'));
    }

    /**
     * Creates a folder below the temp root and touches the given file names.
     *
     * @param list<string> $files
     *
     * @return string Absolute folder path.
     */
    private function scaffold(string $relative, array $files): string
    {
        $folder = $this->tmp.'/'.$relative;
        mkdir($folder, 0777, true);
        foreach ($files as $file) {
            file_put_contents($folder.'/'.$file, 'x');
        }

        return $folder;
    }
}
