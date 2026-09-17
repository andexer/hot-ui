<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\Exception\DirectoryCreateException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The pre-commit hook is the first gate a violation meets, so it is exercised
 * end-to-end: a throwaway repository gets the real hook and the real linter,
 * and commits are run against it. Nothing here touches the global git config —
 * every invocation points GIT_CONFIG_GLOBAL at an empty file inside the temp
 * directory, and identity comes from env vars.
 */
final class HookScriptTest extends TestCase
{
    private const CLEAN_SOURCE = <<<'PHP'
    <?php

    declare(strict_types=1);

    namespace Components\Tests\Fixtures;

    final class Clean
    {
        public function run(string $value): string
        {
            return $value;
        }
    }
    PHP;

    private const VIOLATING_SOURCE = <<<'PHP'
    <?php

    declare(strict_types=1);

    namespace Components\Tests\Fixtures;

    final class Violating
    {
        public function run($untyped): string
        {
            return (string) $untyped;
        }
    }
    PHP;

    private string $tmp;

    protected function setUp(): void
    {
        $root = dirname(__DIR__);
        $this->tmp = sys_get_temp_dir().'/hot-ui-hooks-'.uniqid();

        foreach (['bin', '.githooks', 'fixtures'] as $directory) {
            $path = $this->tmp.'/'.$directory;
            if (! is_dir($path) && ! mkdir($path, 0o775, true) && ! is_dir($path)) {
                throw new DirectoryCreateException($path);
            }
        }

        $this->write('bin/lint.php', (string) file_get_contents($root.'/bin/lint.php'));
        $this->write('bin/install-hooks.php', (string) file_get_contents($root.'/bin/install-hooks.php'));
        $this->write('.githooks/pre-commit', (string) file_get_contents($root.'/.githooks/pre-commit'));
        chmod($this->tmp.'/.githooks/pre-commit', 0o755);

        // An empty global config keeps the suite hermetic: no user identity, no
        // commit.gpgsign, no templates leaking in from the machine running it.
        $this->write('gitconfig', '');

        self::assertSame(0, $this->git(['init', '-q'])['code']);
        self::assertSame(0, $this->execute([PHP_BINARY, 'bin/install-hooks.php'])['code']);
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

    public function testInstallerPointsTheCloneAtTheVersionedHooks(): void
    {
        $config = $this->git(['config', '--local', '--get', 'core.hooksPath']);
        self::assertSame(0, $config['code']);
        self::assertSame('.githooks', $config['output']);

        $status = $this->execute([PHP_BINARY, 'bin/install-hooks.php', '--status']);
        self::assertSame(0, $status['code']);
        self::assertStringContainsString('core.hooksPath: .githooks', $status['output']);
        self::assertStringContainsString('executable', $status['output']);
        self::assertTrue(is_executable($this->tmp.'/.githooks/pre-commit'));
    }

    public function testInstallerIsIdempotent(): void
    {
        $again = $this->execute([PHP_BINARY, 'bin/install-hooks.php']);

        self::assertSame(0, $again['code'], $again['output']);
        self::assertSame('.githooks', $this->git(['config', '--local', '--get', 'core.hooksPath'])['output']);
    }

    public function testViolatingCommitIsBlockedBeforeItExists(): void
    {
        $this->write('fixtures/Violating.php', self::VIOLATING_SOURCE);
        $this->git(['add', 'fixtures/Violating.php']);

        $commit = $this->commit('feat: untyped parameter');

        self::assertNotSame(0, $commit['code']);
        self::assertStringContainsString('parameter $untyped declares no type (rule 1.1)', $commit['output']);
        self::assertStringContainsString('git commit --no-verify', $commit['output']);
        self::assertNotSame(0, $this->git(['rev-parse', '--verify', 'HEAD'])['code'], 'the commit must not exist');
    }

    public function testCleanPhpCommitsNormally(): void
    {
        $this->write('fixtures/Clean.php', self::CLEAN_SOURCE);
        $this->git(['add', 'fixtures/Clean.php']);

        $commit = $this->commit('feat: typed parameter');

        self::assertSame(0, $commit['code'], $commit['output']);
        self::assertSame(0, $this->git(['rev-parse', '--verify', 'HEAD'])['code']);
    }

    public function testCommitWithoutStagedPhpSkipsTheLint(): void
    {
        $this->write('fixtures/notes.md', "# notas\n");
        $this->git(['add', 'fixtures/notes.md']);

        $commit = $this->commit('docs: notes only');

        self::assertSame(0, $commit['code'], $commit['output']);
        self::assertStringNotContainsString('rule', $commit['output']);
    }

    public function testBypassWorksOnPurpose(): void
    {
        $this->write('fixtures/Violating.php', self::VIOLATING_SOURCE);
        $this->git(['add', 'fixtures/Violating.php']);

        $commit = $this->commit('feat: deliberate bypass', ['--no-verify']);

        self::assertSame(0, $commit['code'], $commit['output']);
        self::assertSame(0, $this->git(['rev-parse', '--verify', 'HEAD'])['code']);
    }

    public function testMissingLinterSkipsInsteadOfBlocking(): void
    {
        unlink($this->tmp.'/bin/lint.php');
        $this->write('fixtures/Violating.php', self::VIOLATING_SOURCE);
        $this->git(['add', 'fixtures/Violating.php']);

        $commit = $this->commit('chore: host project without the linter');

        self::assertSame(0, $commit['code'], $commit['output']);
        self::assertStringContainsString('skipping the Hot-UI lint', $commit['output']);
    }

    public function testUninstallOnlyTouchesItsOwnHooksPath(): void
    {
        $this->git(['config', '--local', 'core.hooksPath', '.my-hooks']);
        $untouched = $this->execute([PHP_BINARY, 'bin/install-hooks.php', '--uninstall']);

        self::assertSame(0, $untouched['code']);
        self::assertStringContainsString('left untouched', $untouched['output']);
        self::assertSame('.my-hooks', $this->git(['config', '--local', '--get', 'core.hooksPath'])['output']);

        self::assertSame(0, $this->execute([PHP_BINARY, 'bin/install-hooks.php'])['code']);
        $removed = $this->execute([PHP_BINARY, 'bin/install-hooks.php', '--uninstall']);

        self::assertSame(0, $removed['code']);
        self::assertStringContainsString('unset', $removed['output']);
        self::assertNotSame(0, $this->git(['config', '--local', '--get', 'core.hooksPath'])['code']);
    }

    private function write(string $relative, string $contents): void
    {
        $path = $this->tmp.'/'.$relative;
        if (file_put_contents($path, $contents) === false) {
            throw new DirectoryCreateException($path);
        }
    }

    /**
     * @param list<string> $extra
     *
     * @return array{code: int, output: string}
     */
    private function commit(string $message, array $extra = []): array
    {
        return $this->git(['commit', '-q', '-m', $message, ...$extra]);
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{code: int, output: string}
     */
    private function git(array $arguments): array
    {
        return $this->execute(['git', ...$arguments]);
    }

    /**
     * @param list<string> $command
     *
     * @return array{code: int, output: string}
     */
    private function execute(array $command): array
    {
        $pipes = [];
        $environment = array_merge(getenv(), [
            'GIT_CONFIG_GLOBAL' => $this->tmp.'/gitconfig',
            'GIT_CONFIG_NOSYSTEM' => '1',
            'GIT_TERMINAL_PROMPT' => '0',
            'GIT_AUTHOR_NAME' => 'Hot-UI Suite',
            'GIT_AUTHOR_EMAIL' => 'suite@example.test',
            'GIT_COMMITTER_NAME' => 'Hot-UI Suite',
            'GIT_COMMITTER_EMAIL' => 'suite@example.test',
        ]);

        $process = proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->tmp,
            $environment,
        );
        if (! is_resource($process)) {
            throw new DirectoryCreateException(implode(' ', $command));
        }

        $output = (string) stream_get_contents($pipes[1]).(string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return ['code' => proc_close($process), 'output' => trim($output)];
    }
}
