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
        $key = sha1($source.(string) filemtime($source).(string) filesize($source));
        $compiled = $this->cacheDir.'/'.$key.'.php';

        if ($this->isFresh($compiled, $source)) {
            return $compiled;
        }

        $code = (new ViewCompiler())->compile((string) file_get_contents($source));

        if (! is_dir($this->cacheDir) && ! @mkdir($this->cacheDir, 0775, true) && ! is_dir($this->cacheDir)) {
            throw new \InvalidArgumentException(sprintf('Cannot create compiled views directory [%s].', $this->cacheDir));
        }

        if (file_put_contents($compiled, $code, LOCK_EX) === false) {
            throw new \RuntimeException(sprintf('Cannot write compiled view [%s].', $compiled));
        }

        return $compiled;
    }

    private function isFresh(string $compiled, string $source): bool
    {
        return is_file($compiled)
            && filemtime($compiled) >= filemtime($source)
            && filesize($compiled) === filesize($source);
    }
}