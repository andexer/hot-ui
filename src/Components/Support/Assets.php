<?php

declare(strict_types=1);

namespace Components\Support;

use CallbackFilterIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Locates and publishes the bundled frontend runtime (css/ + js/) into a
 * host application's public directory.
 */
final class Assets
{
    /** @var list<string> Publishable asset groups. */
    private const GROUPS = ['css', 'js'];

    /**
     * Absolute path to a bundled asset group.
     *
     * @param string $group "css", "js" or "" for the assets root.
     */
    public static function path(string $group = ''): string
    {
        $root = dirname(__DIR__, 3).'/';
        $group = strtolower($group);

        if ($group === '') {
            return rtrim($root, '/');
        }
        if (! in_array($group, self::GROUPS, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown asset group [%s]; expected css or js.', $group));
        }

        return rtrim($root.$group, '/');
    }

    /**
     * Copies bundled assets into the target directory, preserving structure
     * (css/hot-ui.css, js/app.js, js/src/**). Idempotent: existing files are
     * overwritten with the bundled version.
     *
     * @param string            $targetDir Web-root directory serving static files.
     * @param list<string>|null $only      Restrict to ["css"] and/or ["js"]; null copies both.
     *
     * @return array<string, int> Copied file count per group.
     */
    public static function publish(string $targetDir, ?array $only = null): array
    {
        $groups = $only ?? self::GROUPS;
        foreach ($groups as $group) {
            if (! in_array($group, self::GROUPS, true)) {
                throw new \InvalidArgumentException(sprintf('Unknown asset group [%s].', $group));
            }
        }

        $copied = [];
        foreach ($groups as $group) {
            $sourceDir = self::path($group);
            $destinationDir = rtrim($targetDir, '/\\').'/'.$group;
            self::ensureDirectory($destinationDir);

            $count = 0;
            $files = new CallbackFilterIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
                ),
                static fn (SplFileInfo $file): bool => $file->isFile(),
            );

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $relative = substr($file->getPathname(), strlen($sourceDir) + 1);
                $relative = strtr($relative, '\\', '/');
                // Sources and maps are development artefacts; only ship the
                // compiled ESM output the page actually loads.
                if (str_ends_with($relative, '.ts') || str_ends_with($relative, '.map')) {
                    continue;
                }

                $destination = $destinationDir.'/'.$relative;
                self::ensureDirectory(dirname($destination));
                copy($file->getPathname(), $destination);
                ++$count;
            }

            $copied[$group] = $count;
        }

        return $copied;
    }

    private static function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($concreteDirectory = $directory, 0o775, true) && ! is_dir($concreteDirectory)) {
            throw new \RuntimeException(sprintf('Unable to create directory [%s].', $concreteDirectory));
        }
    }
}
