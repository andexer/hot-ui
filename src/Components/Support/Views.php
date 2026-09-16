<?php

declare(strict_types=1);

namespace Components\Support;

use CallbackFilterIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Publishes the bundled views (components/, layouts/, partials/) into a host
 * application so developers can own, inspect and customize them.
 *
 * This mirrors Assets, but the destination lives in the app's views area (not
 * the web root). Once copied, point the engine at the local copy:
 *
 *   HotUI::shared(['view_path' => APPPATH.'Views/hotui']);
 *
 * From then on components, layouts and partials resolve from the host copy,
 * so a later package update cannot overwrite customizations silently: run
 * publishViews() again and merge what you want.
 */
final class Views
{
    /** @var list<string> Publishable view groups. */
    private const GROUPS = ['components', 'layouts', 'partials'];

    /**
     * Absolute path to a bundled views group.
     *
     * @param string $group "components", "layouts", "partials" or "" for the views root.
     */
    public static function path(string $group = ''): string
    {
        $root = dirname(__DIR__, 3).'/views';
        if ($group === '') {
            return $root;
        }
        if (! in_array($group, self::GROUPS, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown views group [%s]; expected components, layouts or partials.', $group));
        }

        return $root.'/'.$group;
    }

    /**
     * Copies the bundled views into the target directory, preserving structure
     * (components/ui/card.php, layouts/app.php, partials/meta.php). Idempotent:
     * existing files are overwritten with the bundled version.
     *
     * @param string            $targetDir Directory that will hold the views
     *                                     (e.g. APPPATH.'Views/hotui' in CI4).
     * @param list<string>|null $only      Restrict to ["components"], ["layouts"]
     *                                     and/or ["partials"]; null copies all groups.
     *
     * @return array<string, int> Copied file count per group.
     */
    public static function publish(string $targetDir, ?array $only = null): array
    {
        $groups = $only ?? self::GROUPS;
        foreach ($groups as $group) {
            if (! in_array($group, self::GROUPS, true)) {
                throw new \InvalidArgumentException(sprintf('Unknown views group [%s].', $group));
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