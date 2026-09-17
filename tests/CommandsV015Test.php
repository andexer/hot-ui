<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\ComponentGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the CLI commands introduced in v0.15.0:
 *   - hot-ui:list     (renamed from list:hotfire, alias preserved)
 *   - hot-ui:stubs    (new)
 *   - hot-ui:config   (new)
 *   - ComponentGenerator custom stubs override
 *
 * Commands extend CodeIgniter\CLI\BaseCommand which is only available in CI4;
 * tests that relate to command metadata inspect the source text to stay
 * framework-free (same pattern used in PackageIntegrationTest).
 */
final class CommandsV015Test extends TestCase
{
    // -------------------------------------------------------------------------
    // hot-ui:list rename — source inspection (no CI4 required)
    // -------------------------------------------------------------------------

    public function testListHotfireCommandHasNewName(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ListHotfire.php');
        self::assertStringContainsString("protected \$name = 'hot-ui:list';", $src);
    }

    public function testListHotfireCommandKeepsLegacyAlias(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ListHotfire.php');
        self::assertStringContainsString("'list:hotfire'", $src);
        self::assertStringContainsString('$aliases', $src);
    }

    public function testListHotfireCommandEmitsDeprecationWhenInvokedViaAlias(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ListHotfire.php');
        self::assertStringContainsString('DEPRECATED', $src);
        self::assertStringContainsString('list:hotfire', $src);
    }

    // -------------------------------------------------------------------------
    // hot-ui:stubs — source inspection
    // -------------------------------------------------------------------------

    public function testStubsCommandExists(): void
    {
        self::assertFileExists(__DIR__ . '/../src/Components/Commands/StubsCommand.php');
    }

    public function testStubsCommandHasCorrectName(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/StubsCommand.php');
        self::assertStringContainsString("protected \$name = 'hot-ui:stubs';", $src);
    }

    public function testStubsCommandGroupIsHotUi(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/StubsCommand.php');
        self::assertStringContainsString("protected \$group = 'Hot-UI';", $src);
    }

    public function testStubsCommandPublishesStubsToAppPath(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/StubsCommand.php');
        // Should reference APPPATH as publication target.
        self::assertStringContainsString('APPPATH', $src);
        self::assertStringContainsString('stubs/hot-ui', $src);
    }

    // -------------------------------------------------------------------------
    // hot-ui:config — source inspection
    // -------------------------------------------------------------------------

    public function testConfigCommandExists(): void
    {
        self::assertFileExists(__DIR__ . '/../src/Components/Commands/ConfigCommand.php');
    }

    public function testConfigCommandHasCorrectName(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ConfigCommand.php');
        self::assertStringContainsString("protected \$name = 'hot-ui:config';", $src);
    }

    public function testConfigCommandGroupIsHotUi(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ConfigCommand.php');
        self::assertStringContainsString("protected \$group = 'Hot-UI';", $src);
    }

    public function testConfigCommandSupportsCheckOption(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ConfigCommand.php');
        self::assertStringContainsString('--check', $src);
        self::assertStringContainsString('HOTUI_SNAPSHOT_KEY', $src);
    }

    public function testConfigCommandSupportsPublishOption(): void
    {
        $src = (string) file_get_contents(__DIR__ . '/../src/Components/Commands/ConfigCommand.php');
        self::assertStringContainsString('--publish', $src);
        self::assertStringContainsString('Config/HotUI.php', $src);
    }

    // -------------------------------------------------------------------------
    // ComponentGenerator custom stubs dir
    // -------------------------------------------------------------------------

    public function testGeneratorUsesBuiltInStubsWhenNoCustomDir(): void
    {
        $tmp = sys_get_temp_dir() . '/hot-ui-gen-' . uniqid();
        mkdir($tmp, 0o775, true);

        try {
            $gen = new ComponentGenerator(
                $tmp,
                'App\\Components',
                __DIR__ . '/../src/Components/Hotfire/templates',
            );

            $content = $gen->classContent('sample', []);
            self::assertStringContainsString('extends Component', $content);
        } finally {
            $this->rmdir($tmp);
        }
    }

    public function testGeneratorPrefersCustomStubsWhenDirExists(): void
    {
        $tmp      = sys_get_temp_dir() . '/hot-ui-gen-' . uniqid();
        $stubsDir = $tmp . '/stubs';
        $viewsDir = $tmp . '/views';
        mkdir($stubsDir, 0o775, true);
        mkdir($viewsDir, 0o775, true);

        try {
            // Place a custom component_view_plain.stub in the custom dir.
            file_put_contents(
                $stubsDir . '/component_view_plain.stub',
                '<div class="custom-scaffold">{{label}}</div>' . "\n",
            );

            $gen = new ComponentGenerator(
                $viewsDir,
                'App\\Components',
                __DIR__ . '/../src/Components/Hotfire/templates',
                '🔥',
                $stubsDir,
            );

            $content = $gen->viewContent('sample', []);
            self::assertStringContainsString('custom-scaffold', $content);
        } finally {
            $this->rmdir($tmp);
        }
    }

    public function testGeneratorFallsBackToBuiltInWhenStubNotInCustomDir(): void
    {
        $tmp      = sys_get_temp_dir() . '/hot-ui-gen-' . uniqid();
        $stubsDir = $tmp . '/stubs';
        $viewsDir = $tmp . '/views';
        mkdir($stubsDir, 0o775, true);
        mkdir($viewsDir, 0o775, true);

        try {
            // Custom dir exists but does NOT contain component_class.stub.
            $gen = new ComponentGenerator(
                $viewsDir,
                'App\\Components',
                __DIR__ . '/../src/Components/Hotfire/templates',
                '🔥',
                $stubsDir,
            );

            $content = $gen->classContent('sample', []);
            self::assertStringContainsString('extends Component', $content);
        } finally {
            $this->rmdir($tmp);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function rmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }
}
