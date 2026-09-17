<?php

declare(strict_types=1);

namespace Components\Ci4;

use Components\Hotfire\Component;
use Components\Hotfire\ComponentPaths;
use Components\Hotfire\Config;
use Components\Hotfire\Engine;
use Components\HotUI;
use Components\Support\Assets;
use Components\Support\Views;
use Components\Ui;

/**
 * CodeIgniter 4 bootstrap bridge.
 *
 * Zero hard dependency on CI4: it only reads FCPATH when that constant is
 * already defined by the running application and falls back to explicit
 * arguments otherwise. Composer autoload (PSR-4 + files) makes Ui and the
 * global helpers callable from any CI4 controller, view or helper.
 *
 * Typical usage in a controller:
 *
 *   $page = Components\Ci4\Ci4::render('layouts/app', [...]);
 *   return $this->response->setBody($page);
 *
 * And inside any CI4 view — the global helpers just work:
 *
 *   <?= ui()->card(['class' => 'max-w-sm'], 'Bienvenido') ?>
 *   <?php ui()->open('button'); echo 'Entrar'; echo ui()->close(); ?>
 */
final class Ci4
{
    private static ?Ui $instance = null;

    private static bool $componentAutoloaderRegistered = false;

    /**
     * Returns the CI4-bound Hot-UI instance (process-wide singleton).
     *
     * @param string|null $viewPath Optional custom views directory.
     */
    public static function boot(?string $viewPath = null): Ui
    {
        self::registerComponentClassAutoloader($viewPath);

        return self::$instance ??= HotUI::shared(['view_path' => $viewPath]);
    }

    /**
     * Registers a resolution fallback for collocated component classes
     * (the Livewire 4 "voltage" layout): App\Components\Post\Create lives at
     * components/hotfire/post/🔥create/create.php. Composer's loader stays
     * authoritative — this only kicks in when the class is not already
     * autoloadable, mirroring the derivation ComponentGenerator uses.
     */
    private static function registerComponentClassAutoloader(?string $viewPath): void
    {
        if (self::$componentAutoloaderRegistered) {
            return;
        }
        self::$componentAutoloaderRegistered = true;

        $root = $viewPath;
        if ($root === null) {
            if (! defined('APPPATH')) {
                return;
            }
            $root = rtrim((string) APPPATH, '/\\').'/Views';
        }

        $paths = new ComponentPaths();

        spl_autoload_register(static function (string $class) use ($root, $paths): void {
            $prefix = 'App\\Components\\';
            if (! str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));        // e.g. "Post\Create"
            $path = rtrim($root, '/\\').'/'.$paths->folder($relative).'/'.$paths->leafKebab($relative).'.php';

            if (is_file($path)) {
                require $path;
            }
        });
    }

    /**
     * Renders a Hot-UI page/partial to a string (layout, component, example…).
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): string
    {
        return self::boot()->render($template, $data);
    }

    /**
     * Renders a CodeIgniter 4 view that uses the <ui:…> tag syntax.
     *
     * Looks the file up under the framework views directory (APPPATH.'Views')
     * and renders it through Hot-UI's own renderer: data is extracted into
     * scope and both ui(...) and $this->uiXxx(...) work inside the page. The
     * compiled output is cached under WRITEPATH/cache/hotui.
     *
     *   return $this->response->setBody(Ci4::view('home/dashboard', [
     *       'title' => 'Dashboard',
     *   ]));
     *
     * @param array<string, mixed> $data
     * @param string|null          $basePath Views folder override (default APPPATH.'Views').
     */
    public static function view(string $template, array $data = [], ?string $basePath = null): string
    {
        $basePath ??= defined('APPPATH') ? APPPATH.'Views' : null;

        return self::boot()->view($template, $data, $basePath);
    }

    /**
     * Copies the bundled css/ + js/ runtime into the web root. Defaults to
     * FCPATH when running inside CodeIgniter 4; pass $publicDir otherwise.
     *
     * @param string|null        $publicDir Public directory (default: FCPATH).
     * @param list<string>|null  $only      Restrict to ["css"] and/or ["js"].
     *
     * @return array<string, int> Copied file count per group.
     */
    public static function publish(?string $publicDir = null, ?array $only = null): array
    {
        $publicDir ??= defined('FCPATH') ? rtrim(FCPATH, '/\\') : null;
        if ($publicDir === null) {
            throw new \InvalidArgumentException(
                'Ci4::publish() needs a public directory; pass it explicitly or run inside CodeIgniter 4 (FCPATH).',
            );
        }

        return Assets::publish($publicDir, $only);
    }

    /**
     * Copies the bundled views (components/, layouts/, partials/) into your
     * project so you own and customize them. Defaults to APPPATH.'Views' — the
     * native CodeIgniter 4 views folder — so Hot-UI views live alongside the
     * rest of your app (no intermediate hotui/ folder). Then use the local
     * copy:
     *
     *   Ci4::boot(APPPATH.'Views');
     *
     * @param string|null        $viewDir Views target (default: APPPATH.'Views').
     * @param list<string>|null  $only    Restrict to ["components"], ["layouts"]
     *                                    and/or ["partials"]; null copies all.
     *
     * @return array<string, int> Copied file count per group.
     */
    public static function publishViews(?string $viewDir = null, ?array $only = null): array
    {
        if ($viewDir === null) {
            if (! defined('APPPATH')) {
                throw new \InvalidArgumentException(
                    'Ci4::publishViews() needs a views directory; pass it explicitly or run inside CodeIgniter 4 (APPPATH).',
                );
            }
            $viewDir = rtrim(APPPATH, '/\\').'/Views';
        }

        return Views::publish($viewDir, $only);
    }

    /**
     * Server-renders a Hotfire component as a ready-to-use DOM fragment. The
     * returned HTML carries the signed snapshot and the action endpoint; the
     * JS driver turns data-hot-* into round-trips.
     *
     *   $counter = new \App\Components\Counter();
     *   return $this->response->setBody(Ci4::hotfire($counter));
     *
     * The endpoint is configured through Hotfire Config (default
     * "hot-ui/update") and must be routed to HotfireController::update. See
     * docs/hotfire.md.
     *
     * @param string|null $actionUrl Endpoint override (default: Config endpoint).
     */
    public static function hotfire(Component $component, ?string $actionUrl = null): string
    {
        $actionUrl ??= self::hotfireEndpoint();

        return Engine::render($component, $actionUrl)['html'];
    }

    /** Resolves the configured Hotfire endpoint to a usable URL. */
    private static function hotfireEndpoint(): string
    {
        $endpoint = Config::shared()->endpoint();

        if (preg_match('#^(https?:)?//#', $endpoint) === 1 || str_starts_with($endpoint, '/')) {
            return $endpoint;
        }

        return function_exists('site_url') ? (string) site_url($endpoint) : $endpoint;
    }
}