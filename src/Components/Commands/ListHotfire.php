<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\ComponentPaths;

/**
 * php spark list:hotfire [options]
 *
 * Discovers every Hotfire component under "<views>/components/hotfire" —
 * the folders marked with the 🔥 indicator (or the --emoji override) — and
 * reports the component name, its class, template and sidecars.
 *
 *   php spark list:hotfire
 *   php spark list:hotfire --simple
 *   php spark list:hotfire --views="/abs/path" --emoji="⚡"
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 */
class ListHotfire extends BaseCommand
{
    use CliOptions;

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'list:hotfire';

    /**
     * @var string
     */
    protected $description = 'Lists every Hotfire component (class, template and sidecars).';

    /**
     * @var string
     */
    protected $usage = 'list:hotfire [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--simple' => 'Component names only (no class/template/sidecar table).',
        '--views'  => 'Views folder. Default: APPPATH.\'Views\'.',
        '--emoji'  => 'Visual marker on the component folder. Default: "🔥".',
    ];

    /**
     * Actually execute the command.
     *
     * @param array<int|string, string|null> $params
     */
    public function run(array $params)
    {
        $views = $this->option($params, 'views');
        if ($views === null) {
            if (! defined('APPPATH')) {
                CLI::error('Hotfire: cannot resolve APPPATH; run from a CodeIgniter 4 application or pass --views.');
                CLI::newLine();

                return EXIT_ERROR;
            }
            $views = rtrim((string) APPPATH, '/\\').'/Views';
        }

        $paths = new ComponentPaths(rtrim((string) $views, '/\\'), $this->option($params, 'emoji') ?? '🔥');
        $components = $paths->discover(! $this->has($params, 'simple'));

        if ($components === []) {
            CLI::write('No Hotfire components found under '.clean_path($paths->hotfireRoot()).'.', 'yellow');
            CLI::newLine();

            return EXIT_SUCCESS;
        }

        CLI::write(count($components).' Hotfire component'.(count($components) === 1 ? '' : 's').' under '.clean_path($paths->hotfireRoot()).':', 'green');
        CLI::newLine();

        foreach ($components as $component) {
            CLI::write($component['name'], 'white');
            if (! array_key_exists('class', $component)) {
                continue;
            }

            /** @var string|null $class */
            $class = $component['class'];
            /** @var string|null $view */
            $view = $component['view'];
            /** @var list<string> $sidecars */
            $sidecars = $component['sidecars'];

            CLI::write('  class     '.($class !== null ? clean_path($class) : '(missing)'));
            CLI::write('  template  '.($view !== null ? clean_path($view) : '(missing)'));
            foreach ($sidecars as $sidecar) {
                CLI::write('  sidecar   '.clean_path($sidecar));
            }
            CLI::newLine();
        }

        return EXIT_SUCCESS;
    }
}
