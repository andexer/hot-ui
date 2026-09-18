<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\Config;
use Components\HotUI;

/**
 * php spark hot-ui:doctor
 *
 * Checks the host application for the pieces Hotfire needs in production:
 * signing key, route registration, published assets, view path and writable
 * component directories. It intentionally reports actionable warnings instead
 * of trying to mutate the app.
 */
final class DoctorCommand extends BaseCommand
{
    use CliOptions;

    protected $group = 'Hot-UI';

    protected $name = 'hot-ui:doctor';

    protected $description = 'Checks Hot-UI/Hotfire setup: route, assets, snapshot key, views and bundle.';

    protected $usage = 'hot-ui:doctor [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--strict' => 'Return a non-zero exit code when warnings are found.',
    ];

    /**
     * @param array<int|string, string|null> $params
     */
    public function run(array $params): int
    {
        $strict = $this->has($params, 'strict') || $this->has($params, '--strict');
        $checks = $this->checks();
        $warnings = 0;

        CLI::write('Hot-UI doctor', 'green');
        CLI::write(str_repeat('-', 50));

        foreach ($checks as $check) {
            [$ok, $label, $detail] = $check;
            CLI::write(sprintf('%s %s', $ok ? '[ok]' : '[warn]', $label), $ok ? 'green' : 'yellow');
            if ($detail !== '') {
                CLI::write('     '.$detail);
            }
            if (! $ok) {
                $warnings++;
            }
        }

        CLI::newLine();
        CLI::write($warnings === 0 ? 'Hot-UI doctor found no issues.' : sprintf('Hot-UI doctor found %d warning(s).', $warnings), $warnings === 0 ? 'green' : 'yellow');

        $exitError = defined('EXIT_ERROR') ? EXIT_ERROR : 1;
        $exitSuccess = defined('EXIT_SUCCESS') ? EXIT_SUCCESS : 0;

        return $strict && $warnings > 0 ? $exitError : $exitSuccess;
    }

    /**
     * @return list<array{bool, string, string}>
     */
    private function checks(): array
    {
        $config = Config::shared();
        $public = defined('FCPATH') ? rtrim((string) FCPATH, '/\\') : getcwd();
        $views = defined('APPPATH') ? rtrim((string) APPPATH, '/\\').'/Views' : HotUI::views();
        $routes = defined('APPPATH') ? rtrim((string) APPPATH, '/\\').'/Config/Routes.php' : null;

        return [
            [
                $config->snapshotKey() !== null,
                'Snapshot key',
                $config->snapshotKey() !== null ? 'HOTUI_SNAPSHOT_KEY is available.' : 'Set HOTUI_SNAPSHOT_KEY in .env.',
            ],
            [
                $routes !== null && is_file($routes) && str_contains((string) file_get_contents($routes), $config->endpoint()),
                'Hotfire route',
                $routes === null ? 'APPPATH is not defined; skipping host route lookup.' : 'Expected endpoint: '.$config->endpoint(),
            ],
            [
                is_file($public.'/js/app.js'),
                'JavaScript bundle',
                'Expected '.$public.'/js/app.js. Run php spark hot-ui:publish assets or Composer publish.',
            ],
            [
                is_file($public.'/css/hot-ui.min.css'),
                'CSS bundle',
                'Expected '.$public.'/css/hot-ui.min.css. Run php spark hot-ui:publish assets or Composer publish.',
            ],
            [
                is_dir($views),
                'Views directory',
                'Using '.$views.'.',
            ],
            [
                is_dir($views) && is_writable($views),
                'Views writable',
                'Required for published/scaffolded components under app/Views.',
            ],
            [
                is_file(HotUI::assets('js')),
                'Package bundle',
                'Bundled runtime: '.HotUI::assets('js'),
            ],
        ];
    }
}
