<?php

declare(strict_types=1);

namespace Components;

use Components\Support\Assets;

/**
 * Standard entry-point facade for host applications.
 *
 * Zero-dependency: only PHP 8.2+ is required. Hot-UI renders everything (its
 * components, layouts, partials and host pages) through its own built-in
 * renderer, so it plugs into CodeIgniter 4, plain PHP or any framework that
 * can call a function or echo a string.
 *
 *   use Components\HotUI;
 *
 *   $ui = HotUI::shared();                    // one process-wide instance
 *   echo $ui->render('layouts/app', [...]);   // full page
 *
 *   // in any host template / CI4 view:
 *   <?= ui()->card(['class' => 'max-w-sm'], 'Sign in') ?>
 */
final class HotUI
{
    public const string VERSION = '2.0.0';

    /**
     * Dedicated instance for code outside any template.
     *
     * @param array<string, mixed> $config {
     *     Optional configuration.
     *
     *     @type string|null $view_path Alternative package views path (rarely needed).
     * }
     */
    public static function instance(array $config = []): Ui
    {
        return new Ui($config['view_path'] ?? null);
    }

    /**
     * Process-wide instance backing the ui() helper. Configure view_path only
     * the first time; later calls return the already-built instance.
     *
     * @param array<string, mixed> $config See instance().
     */
    public static function shared(array $config = []): Ui
    {
        return Ui::shared($config['view_path'] ?? null);
    }

    /**
     * Absolute path to the bundled views directory (layouts, partials,
     * components). Exposed so hosts can point their own renderer at it.
     */
    public static function views(): string
    {
        return dirname(__DIR__, 2).'/views';
    }

    /**
     * Absolute path to a bundled asset directory ("css" or "js").
     *
     * @param string $group Asset group: "css", "js" or "" for the assets root.
     */
    public static function assets(string $group = ''): string
    {
        return Assets::path($group);
    }

    /**
     * Copies the bundled css/ and js/ runtime into the host's public dir.
     *
     * @param string       $publicDir Web-root directory that serves static files.
     * @param list<string>|null $only Restrict to ["css"], ["js"] or null for both.
     *
     * @return array<string, int> Number of files copied per group.
     */
    public static function publish(string $publicDir, ?array $only = null): array
    {
        return Assets::publish($publicDir, $only);
    }
}