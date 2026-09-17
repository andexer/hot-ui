<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Filesystem primitives shared by the scaffold generators, the publishers and
 * the template cache, so directory creation is handled the same way — without
 * the error-suppression operator — everywhere.
 */
final class Filesystem
{
    /**
     * Creates $directory (and any missing parent) when it does not exist yet.
     *
     * Returns true when the directory exists afterwards and false when it
     * could not be created: the caller owns the error contract (the generators
     * warn and skip, the publishers throw).
     */
    public static function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        // A file occupies the target path: mkdir() would only warn.
        if (file_exists($directory)) {
            return false;
        }

        // mkdir() also warns when a file blocks the chain or the parent is not
        // writable, and the @ operator is banned, so both preconditions are
        // checked here instead of being silenced.
        $ancestor = self::nearestExistingAncestor($directory);
        if ($ancestor === null || ! is_writable($ancestor)) {
            return false;
        }

        if (mkdir($directory, 0o775, true)) {
            return true;
        }

        // A concurrent caller may have created it in the meantime.
        return is_dir($directory);
    }

    /**
     * Nearest parent directory that already exists, or null when the chain is
     * blocked by a file or the filesystem root is reached without one.
     */
    private static function nearestExistingAncestor(string $directory): ?string
    {
        for ($ancestor = dirname($directory); ; $ancestor = dirname($ancestor)) {
            if (is_dir($ancestor)) {
                return $ancestor;
            }

            if (file_exists($ancestor) || $ancestor === dirname($ancestor)) {
                return null;
            }
        }
    }
}
