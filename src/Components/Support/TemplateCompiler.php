<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Compiles a view file to cached plain PHP and reuses the artifact while the
 * source is unchanged (key = sha1 of path + mtime + size).
 *
 * Shared by pages (Ui::view) and component/layout/partial templates
 * (TemplateRenderer) so the <ui:…>/<blocks:…> tag syntax works everywhere a
 * template runs, not only at the page level.
 *
 * The cache directory must be writable by and private to the application: the
 * artifacts are plain PHP that the renderer includes, so a third party able to
 * write there could inject executables. As defense in depth each artifact is
 * stamped with the exact key it was compiled for and only a matching artifact
 * is reused; artifacts are replaced atomically (temp file + rename) so the
 * include never observes a half-written file.
 *
 * Freshness is keyed on the CONTENT hash (not just filemtime/size, which can
 * collide for same-length edits made in the same mtime tick).
 */
final class TemplateCompiler
{
    public function __construct(private readonly string $cacheDir) {}

    /**
     * Returns the absolute path of the compiled artifact for $source.
     *
     * @param string $source Absolute source path, already realpath()ed.
     *
     * @throws \InvalidArgumentException When the cache directory cannot be created.
     * @throws \RuntimeException          When the compiled file cannot be written.
     */
    public function compile(string $source): string
    {
        $sourceCode = (string) file_get_contents($source);
        $key = sha1($source.':'.hash('sha256', $sourceCode));
        $compiled = $this->cacheDir.'/'.$key.'.php';

        if ($this->isFresh($compiled, $key)) {
            return $compiled;
        }

        $code = $this->sign($key, (new ViewCompiler())->compile($sourceCode));

        if (! Filesystem::ensureDirectory($this->cacheDir)) {
            throw new \InvalidArgumentException(sprintf('Cannot create compiled views directory [%s].', $this->cacheDir));
        }

        $staging = $compiled.'.'.bin2hex(random_bytes(4)).'.new';
        if (file_put_contents($staging, $code, LOCK_EX) === false) {
            throw new \RuntimeException(sprintf('Cannot write compiled view [%s].', $compiled));
        }
        if (! is_writable($this->cacheDir) || ! rename($staging, $compiled)) {
            if (is_file($staging)) {
                unlink($staging);
            }

            throw new \RuntimeException(sprintf('Cannot replace compiled view [%s].', $compiled));
        }

        return $compiled;
    }

    /**
     * Stamps the artifact with the exact compile key; on reuse the stamp is
     * checked so a stale, foreign or truncated artifact is discarded instead
     * of being included. The stamp lives inside the first PHP block (or in a
     * leading output-neutral block for pure-HTML sources), never disturbing the
     * rendered output or a leading declare(strict_types=1).
     */
    private static function sign(string $key, string $code): string
    {
        $position = strpos($code, '<?php');
        if ($position !== false) {
            return substr($code, 0, $position + 5)." /* hotui:{$key} */".substr($code, $position + 5);
        }

        return "<?php /* hotui:{$key} */ ?>\n".$code;
    }

    private function isFresh(string $compiled, string $key): bool
    {
        if (! is_file($compiled)) {
            return false;
        }

        if (! is_readable($compiled)) {
            return false;
        }

        $handle = fopen($compiled, 'rb');
        if ($handle === false) {
            return false;
        }
        $head = fread($handle, 256);
        fclose($handle);

        return is_string($head) && str_contains($head, 'hotui:'.$key);
    }
}