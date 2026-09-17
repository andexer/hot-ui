<?php

declare(strict_types=1);

namespace Components\Tests\Support;

use Components\Support\Exception\DirectoryCreateException;
use Components\Support\TemplateCompiler;
use PHPUnit\Framework\TestCase;

final class TemplateCompilerTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-compiler-'.uniqid();
        if (! is_dir($this->tmp) && ! mkdir($this->tmp, 0o775, true) && ! is_dir($this->tmp)) {
            throw new DirectoryCreateException($this->tmp);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmp)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tmp, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($iterator as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->tmp);
        }
    }

    private function sourceDir(): string
    {
        return $this->tmp.'/source';
    }

    private function cacheDir(): string
    {
        return $this->tmp.'/cache';
    }

    public function testReusesArtifactWhileSourceIsUnchanged(): void
    {
        if (! is_dir($this->sourceDir()) && ! mkdir($this->sourceDir(), 0o775, true)) {
            throw new DirectoryCreateException($this->sourceDir());
        }
        $source = $this->sourceDir().'/page.php';
        $compiler = new TemplateCompiler($this->cacheDir());

        file_put_contents($source, '<ui:button>ver</ui:button>');
        $first = $compiler->compile($source);
        $before = filemtime($first);
        usleep(20000);

        $again = $compiler->compile($source);

        self::assertSame($first, $again, 'A cached artifact for the same source state is reused.');
        self::assertSame($before, filemtime($again), 'Reuse must not rewrite the artifact.');
        self::assertMatchesRegularExpression('/<?php \/\* hotui:[0-9a-f]{40} \*\//', (string) file_get_contents($again));
    }

    public function testChangedSourceProducesADifferentArtifact(): void
    {
        if (! is_dir($this->sourceDir()) && ! mkdir($this->sourceDir(), 0o775, true)) {
            throw new DirectoryCreateException($this->sourceDir());
        }
        $source = $this->sourceDir().'/page.php';
        $compiler = new TemplateCompiler($this->cacheDir());

        file_put_contents($source, 'un');

        $first = $compiler->compile($source);
        clearstatcache(true, $source);
        sleep(1);
        file_put_contents($source, 'deux');

        self::assertNotSame($first, $compiler->compile($source));
    }

    public function testTamperedOrForeignArtifactIsDiscardedAndRecompiled(): void
    {
        if (! is_dir($this->sourceDir()) && ! mkdir($this->sourceDir(), 0o775, true)) {
            throw new DirectoryCreateException($this->sourceDir());
        }
        $source = $this->sourceDir().'/page.php';
        $compiler = new TemplateCompiler($this->cacheDir());

        file_put_contents($source, '<ui:button />');
        $path = $compiler->compile($source);

        file_put_contents($path, "<?php /* hotui:deadbeef */ ?>\n<?php echo 'OWNED'; ?>");

        $recompiled = $compiler->compile($source);
        $content = (string) file_get_contents($recompiled);

        self::assertSame($path, $recompiled);
        self::assertStringNotContainsString('deadbeef', $content);
        self::assertStringNotContainsString('OWNED', $content);
        self::assertStringContainsString('renderComponent', $content);
    }

    public function testPureHtmlSourceGetsNeutralStamp(): void
    {
        if (! is_dir($this->sourceDir()) && ! mkdir($this->sourceDir(), 0o775, true)) {
            throw new DirectoryCreateException($this->sourceDir());
        }
        $source = $this->sourceDir().'/bare.html.php';
        $compiler = new TemplateCompiler($this->cacheDir());

        file_put_contents($source, '<!DOCTYPE html><p>hola</p>');

        $path = $compiler->compile($source);
        $content = (string) file_get_contents($path);

        self::assertMatchesRegularExpression('/<\?php \/\* hotui:[0-9a-f]{40} \*\/ \?>/', $content);
        self::assertStringContainsString('<!DOCTYPE html>', $content);
    }

    public function testCompileThrowsTemplateNotFoundExceptionWhenSourceMissing(): void
    {
        $compiler = new TemplateCompiler($this->cacheDir());

        $this->expectException(\Components\Support\Exception\TemplateNotFoundException::class);
        $compiler->compile('/nonexistent/path/source.php');
    }
}