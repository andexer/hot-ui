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

        if (mkdir($directory, 0o775, true)) {
            return true;
        }

        // A concurrent caller may have created it in the meantime.
        return is_dir($directory);
    }
}
