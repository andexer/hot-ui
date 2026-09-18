<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\ComponentPaths;
use Components\Hotfire\Exception\HotfireException;

/**
 * php spark hot-ui:list [options]
 *
 * Discovers every Hotfire component under the configured view prefix — the
 * folders marked with the 🔥 indicator (or the --emoji override) — and reports
 * the component name, its class, template and sidecars.
 *
 *   php spark hot-ui:list
 *   php spark hot-ui:list --simple
 *   php spark hot-ui:list --views="/abs/path" --emoji="⚡"
 *
 * The alias `list:hotfire` is kept for backwards compatibility and will be
 * removed in a future major version.
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 */
final class ListHotfire extends BaseCommand
{
    /**
     * CI4.7 forwards the logger and the command locator; standalone use (tests,
     * package tooling) can build the command without them.
     */
    public function __construct(?\Psr\Log\LoggerInterface $logger = null, ?\CodeIgniter\CLI\Commands $commands = null)
    {
        if ($logger !== null && $commands !== null) {
            parent::__construct($logger, $commands);
        }
    }
    use CliOptions;

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'hot-ui:list';

    /**
     * Backwards-compatible alias. Emits a deprecation notice when used.
     *
     * @var list<string>
     */
    protected $aliases = ['list:hotfire'];

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
        // Emit a deprecation notice when the old alias is used directly.
        if (($this->name ?? '') !== 'hot-ui:list') {
            CLI::write('[DEPRECATED] `list:hotfire` has been renamed to `hot-ui:list`. The alias will be removed in a future major version.', 'yellow');
            CLI::newLine();
        }

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
