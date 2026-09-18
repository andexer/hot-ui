<?php

declare(strict_types=1);

namespace Components;

use Components\Support\Exception\UnknownGroupException;
use Components\Exception\UnresolvedDirectoryException;
use Components\Support\Assets;
use Components\Support\Views;

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
    public const VERSION = '0.24.5';

    /** @var list<string> Groups publishViews() can copy on their own. */
    private const VIEW_GROUPS = ['components', 'layouts', 'partials'];

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

    /**
     * Publishes css/ + js/ to an auto-detected web root. Safe to call from a
     * Composer post-install/post-update script (`"post-install-cmd":
     * ["Components\\HotUI::autoPublish"]`), where no framework bootstrap (and
     * therefore no FCPATH) exists. Compose passes its Event object as the first
     * argument, which is accepted and ignored.
     *
     * Resolution order:
     *   1. $publicDir when given explicitly;
     *   2. FCPATH when running inside a booted CodeIgniter 4 app;
     *   3. the first existing public/web directory under the process cwd
     *      (Composer runs event scripts from the host project root).
     *
     * @param mixed             $publicDir Explicit public directory (optional string),
     *                            or the Composer Event object injected by scripts.
     * @param list<string>|null $only      Restrict to ["css"] and/or ["js"].
     *
     * @return array<string, int> Number of files copied per group.
     */
    public static function autoPublish(mixed $publicDir = null, ?array $only = null): array
    {
        if (is_object($publicDir)) {
            $publicDir = null; // Composer event handler, not a directory.
        }
        $publicDir ??= defined('FCPATH') ? rtrim(FCPATH, '/\\') : self::detectPublicDir();

        return Assets::publish($publicDir, $only);
    }

    private static function detectPublicDir(): string
    {
        $cwd = getcwd();
        if ($cwd === false) {
            throw new UnresolvedDirectoryException('working directory', 'Unable to resolve the working directory.');
        }

        $candidates = ['public', 'public_html', 'web', 'www', 'html'];
        foreach ($candidates as $candidate) {
            $directory = $cwd.'/'.$candidate;
            if (is_dir($directory)) {
                return $directory;
            }
        }

        throw new UnresolvedDirectoryException('web root', sprintf(
            'HotUI::autoPublish() could not find a web root. Looked for %s under [%s]; pass the directory explicitly.',
            implode(', ', array_map(static fn (string $candidate): string => '/'.$candidate, $candidates)),
            $cwd,
        ));
    }

    /**
     * Copies the bundled views (components/, layouts/, partials/) into a host
     * project so developers own and customize them. Automatic when used inside
     * CodeIgniter 4 (defaults to APPPATH.'Views' — the native views folder);
     * otherwise defaults to getcwd().'/app/Views' and can be overridden
     * explicitly.
     *
     * After publishing, point the engine at the local copy:
     *
     *   HotUI::shared(['view_path' => APPPATH.'Views']);
     *
     * @param string|null        $viewDir Target views directory.
     * @param list<string>|null  $only    Restrict to ["components"], ["layouts"]
     *                                    and/or ["partials"]; null copies all.
     *
     * @return array<string, int> Copied file count per group.
     */
    public static function publishViews(?string $viewDir = null, ?array $only = null): array
    {
        if ($only !== null) {
            foreach ($only as $group) {
                if (! in_array($group, self::VIEW_GROUPS, true)) {
                    throw new UnknownGroupException('views', $group);
                }
            }
        }

        $viewDir ??= defined('APPPATH')
            ? rtrim(APPPATH, '/\\').'/Views'
            : (getcwd() !== false ? getcwd().'/app/Views' : null);
        if ($viewDir === null) {
            throw new UnresolvedDirectoryException('views directory', 'HotUI::publishViews() could not resolve a views directory; pass it explicitly.');
        }

        return Views::publish($viewDir, $only);
    }
}