<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\Exception\DirectoryCreateException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * bin/lint.php is the CI gate for the machine-checkable house rules, so the
 * gate itself is tested: fixtures prove it flags what it should and, just as
 * importantly, that it leaves typed signatures and domain exceptions alone.
 */
final class LintScriptTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-lint-'.uniqid();
        if (! is_dir($this->tmp) && ! mkdir($this->tmp, 0o775, true) && ! is_dir($this->tmp)) {
            throw new DirectoryCreateException($this->tmp);
        }
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

    public function testPackageItselfIsClean(): void
    {
        [$code, $output] = $this->lint(dirname(__DIR__));

        self::assertSame(0, $code, $output);
        self::assertStringContainsString('clean', $output);
    }

    public function testTypedSignaturesAndDomainThrowsPass(): void
    {
        $file = $this->fixture('clean.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Components\Tests\Fixtures;

        use Components\Support\Exception\UnknownGroupException;

        final class Clean
        {
            /** Signatures the checker must NOT flag. */
            public function __construct(
                public private(set) string $visible,
                public readonly ?array $nullable = null,
                (self&A)|null $dnf = null,
                array &$byRef = [],
            ) {
                unset($visible, $nullable, $dnf, $byRef);
            }

            /**
             * @param array<string, string> $map
             */
            public function run(array $map, string $first, int ...$rest): string
            {

                $closure = static function (?string $name): string {
                    return (string) $name;
                };

                $arrow = static fn (int $value): int => $value;

                if ($map === []) {
                    throw new UnknownGroupException('asset', $first, ['css']);
                }

                return $closure($first).$arrow(count($rest));
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(0, $code, $output);
    }

    public function testUntypedParametersAreFlagged(): void
    {
        $file = $this->fixture('untyped.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        function helper($untyped): void
        {
        }

        final class PartlyTyped
        {
            public function run(string $typed, $bare = null): void
            {
            }

            public function closures(): void
            {
                $arrow = static fn ($captured) => $captured;
                $arrow(1);
            }

            public function promoted(#[Attribute] $attributeOnly): void
            {
            }

            public function visibility(private(set) $asymmetric): void
            {
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(1, $code, $output);
        self::assertStringContainsString('parameter $untyped declares no type (rule 1.1)', $output);
        self::assertStringContainsString('parameter $bare declares no type (rule 1.1)', $output);
        self::assertStringContainsString('parameter $captured declares no type (rule 1.1)', $output);
        self::assertStringContainsString('parameter $attributeOnly declares no type (rule 1.1)', $output);
        self::assertStringContainsString('parameter $asymmetric declares no type (rule 1.1)', $output);
        self::assertSame(0, substr_count($output, 'rule 5.1'), $output);
        self::assertSame(5, substr_count($output, 'declares no type'), $output);
    }

    public function testGenericThrowsAreFlaggedButDomainAndNamespacedOnesAreNot(): void
    {
        $file = $this->fixture('throws.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Components\Tests\Fixtures;

        use Components\Support\Exception\TemplateNotFoundException;
        use RuntimeException;

        final class Throws
        {
            public function run(string $name, ?RuntimeException $previous): void
            {
                if ($name === 'qualified') {
                    throw new \RuntimeException('nope');
                }
                if ($name === 'imported') {
                    throw new RuntimeException('nope');
                }
                if ($name === 'logic') {
                    throw new \LogicException('nope');
                }
                if ($name === 'domain') {
                    throw TemplateNotFoundException::forTemplate('a', 'b');
                }
                if ($name === 'namespaced') {
                    throw new My\RuntimeException('custom subclass, allowed');
                }

                throw $previous;
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(1, $code, $output);
        self::assertSame(3, substr_count($output, 'rule 5.1'), $output);
        self::assertStringContainsString('throwing \\RuntimeException is banned', $output);
        self::assertStringContainsString('throwing \\LogicException is banned', $output);
        self::assertSame(0, substr_count($output, 'rule 1.1'), $output);
    }

    public function testGenericConstructionInPackageSourceIsFlaggedEvenWithoutAThrow(): void
    {
        $file = $this->fixture('src/Components/Probe/Built.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Components\Tests\Fixtures;

        final class Built
        {
            public function deferred(): void
            {
                $failure = new \RuntimeException('thrown a line later');
                throw $failure;
            }

            public function returned(): \Throwable
            {
                return new \InvalidArgumentException('returned, not thrown');
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(1, $code, $output);
        self::assertSame(2, substr_count($output, 'constructing \\'), $output);
        self::assertStringContainsString('constructing \RuntimeException is banned; use a domain exception (rule 5.1)', $output);
        self::assertStringContainsString('constructing \InvalidArgumentException is banned', $output);
    }

    public function testGenericConstructionOutsidePackageSourceIsAllowed(): void
    {
        // The suite builds a generic SPL exception on purpose to prove how the
        // package reacts to one it does not know; only a direct throw is banned.
        $file = $this->fixture('Probe/Fixture.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Components\Tests\Fixtures;

        final class Fixture
        {
            public function probe(): \Throwable
            {
                return new \RuntimeException('boom');
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(0, $code, $output);
    }

    public function testExceptionClassesMustJoinAFamily(): void
    {
        $file = $this->fixture('src/Components/Probe/Loose.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Components\Tests\Fixtures;

        final class Loose extends \RuntimeException
        {
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(1, $code, $output);
        self::assertStringContainsString('class Loose extends \RuntimeException without implementing a family marker', $output);
    }

    public function testAMarkerComesFromItsNameAndAnonymousClassesAreLeftAlone(): void
    {
        $file = $this->fixture('src/Components/Probe/Family.php', <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Components\Tests\Fixtures;

        use Components\Exception\HotUiException;

        final class Family extends \RuntimeException implements HotUiException
        {
        }

        final class Alien extends \RuntimeException implements \JsonSerializable
        {
            public function jsonSerialize(): array
            {
                return [];
            }
        }

        final class Factory
        {
            public function oneOff(): \RuntimeException
            {
                return new class extends \RuntimeException {
                };
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(1, $code, $output);
        self::assertStringContainsString('class Alien extends', $output);
        self::assertStringNotContainsString('class Family', $output);
        self::assertStringNotContainsString('Factory', $output);
    }

    public function testSuppressionAndStrictTypesAreStillChecked(): void
    {
        $file = $this->fixture('suppressed.php', <<<'PHP'
        <?php

        final class Suppressed
        {
            public function run(string $path): string
            {
                return (string) @file_get_contents($path);
            }
        }
        PHP);

        [$code, $output] = $this->lintFile($file);

        self::assertSame(1, $code, $output);
        self::assertStringContainsString('the @ error-suppression operator is banned (rule 5.3)', $output);
        self::assertStringContainsString('missing declare(strict_types=1) (rule 1.1)', $output);
    }

    private function fixture(string $name, string $contents): string
    {
        $path = $this->tmp.'/'.$name;
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0o775, true) && ! is_dir($directory)) {
            throw new DirectoryCreateException($directory);
        }

        if (file_put_contents($path, $contents."\n") === false) {
            throw new DirectoryCreateException($path);
        }

        return $path;
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function lintFile(string $file): array
    {
        return $this->lint(dirname(__DIR__), $file);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function lint(string $cwd, ?string $file = null): array
    {
        $command = sprintf(
            'cd %s && %s %s%s 2>&1',
            escapeshellarg($cwd),
            escapeshellarg(PHP_BINARY),
            escapeshellarg($cwd.'/bin/lint.php'),
            $file === null ? '' : ' '.escapeshellarg($file),
        );

        $lines = [];
        exec($command, $lines, $code);

        return [$code, implode("\n", $lines)];
    }
}
