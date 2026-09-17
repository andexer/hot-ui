<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\ComponentPaths;
use Components\Hotfire\Exception\HotfireException;

/**
 * php spark list:hotfire [options]
 *
 * Discovers every Hotfire component under the configured view prefix — the
 * folders marked with the 🔥 indicator (or the --emoji override) — and reports
 * the component name, its class, template and sidecars.
 *
 *   php spark list:hotfire
 *   php spark list:hotfire --simple
 *   php spark list:hotfire --views="/abs/path" --emoji="⚡"
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 */
final class ListHotfire extends BaseCommand
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
        '--simple' => 'Component names only (no class/template/sidecar detail).',
        '--views'  => 'Views folder. Default: APPPATH.\'Views\'.',
        '--emoji'  => 'Visual marker on the component folder. Default: "🔥".',
    ];

    /**
     * Actually execute the command.
     *
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(array $params): int
    {
        try {
            $paths = new ComponentPaths($this->viewsRoot($params), $this->option($params, 'emoji') ?? '🔥');
            $components = $paths->discover();
        } catch (HotfireException $exception) {
            CLI::error('Hotfire: '.$exception->getMessage());
            CLI::newLine();

            return EXIT_ERROR;
        }

        $root = clean_path($paths->hotfireRoot());
        if ($components === []) {
            CLI::write('No Hotfire components found under '.$root.'.', 'yellow');
            CLI::newLine();

            return EXIT_SUCCESS;
        }

        $detailed = ! $this->has($params, 'simple');
        CLI::write(sprintf(
            '%d Hotfire component%s under %s:',
            count($components),
            count($components) === 1 ? '' : 's',
            $root,
        ), 'green');
        CLI::newLine();

        foreach ($components as $component) {
            $this->render($component, $detailed);
        }

        return EXIT_SUCCESS;
    }

    /**
     * Prints one component: its name plus, unless --simple, the class, template
     * and sidecars it owns.
     *
     * @param array{name: string, folder: string, class: string|null, view: string|null, sidecars: list<string>} $component
     */
    private function render(array $component, bool $detailed): void
    {
        CLI::write($component['name'], 'white');
        if (! $detailed) {
            return;
        }

        CLI::write('  class     '.self::describe($component['class']));
        CLI::write('  template  '.self::describe($component['view']));
        foreach ($component['sidecars'] as $sidecar) {
            CLI::write('  sidecar   '.clean_path($sidecar));
        }
        CLI::newLine();
    }

    /** Absolute path, or a "(missing)" marker when the artifact is absent. */
    private static function describe(?string $file): string
    {
        return $file === null ? '(missing)' : clean_path($file);
    }
}
