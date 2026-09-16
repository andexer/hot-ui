<?php

declare(strict_types=1);

namespace Components\Ci4;

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

    /**
     * Returns the CI4-bound Hot-UI instance (process-wide singleton).
     *
     * @param string|null $viewPath Optional custom views directory.
     */
    public static function boot(?string $viewPath = null): Ui
    {
        return self::$instance ??= HotUI::shared(['view_path' => $viewPath]);
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
     * project so you own and customize them. Defaults to APPPATH.'Views/hotui'.
     * Then use the local copy:
     *
     *   Ci4::boot(APPPATH.'Views/hotui');
     *
     * @param string|null        $viewDir Views target (default: APPPATH.'Views/hotui').
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
            $viewDir = rtrim(APPPATH, '/\\').'/Views/hotui';
        }

        return Views::publish($viewDir, $only);
    }
}