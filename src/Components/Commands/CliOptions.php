<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\CLI;

/**
 * Option parsing for Hot-UI generator commands.
 *
 * CodeIgniter's CLI parser turns "spark ... --opt=value" into the single key
 * "opt=value" and pure flags ("--force") into a null-valued key, so a command
 * must look in three places: the run() params (exact key), params keys that
 * embed "name=value", and the global CLI::getOption() fallback for the
 * space-separated idiom ("--opt value").
 */
trait CliOptions
{
    /**
     * True when the option was present in any supported spelling.
     *
     * @param array<int|string, string|null> $params
     */
    private function has(array $params, string $name): bool
    {
        if (array_key_exists($name, $params)) {
            return true;
        }

        foreach ($params as $key => $value) {
            if (is_string($key) && str_starts_with($key, $name.'=')) {
                return true;
            }
        }

        return (bool) CLI::getOption($name);
    }

    /**
     * Option value in any supported spelling, or null when unset.
     *
     * @param array<int|string, string|null> $params
     */
    private function option(array $params, string $name): ?string
    {
        if (array_key_exists($name, $params)) {
            $value = $params[$name];
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        foreach ($params as $key => $value) {
            if (is_string($key) && str_starts_with($key, $name.'=')) {
                $found = substr($key, strlen($name) + 1);
                if ($found !== '') {
                    return $found;
                }
            }
        }

        $value = CLI::getOption($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}