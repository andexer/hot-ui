<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Support\Filesystem;

/**
 * php spark hot-ui:stubs [options]
 *
 * Publishes the internal scaffold templates (*.stub) used by `make:hotfire`
 * into the host application so developers can customise them freely without
 * touching vendor files.
 *
 * Published path: APPPATH . 'Components/stubs/hot-ui/'
 *
 * Once stubs are published, `make:hotfire` and `make:hotfire-view` will
 * detect and prefer the local copies over the built-in ones.
 *
 *   php spark hot-ui:stubs
 *   php spark hot-ui:stubs --force   # overwrite existing local stubs
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 */
final class StubsCommand extends BaseCommand
{
    use CliOptions;

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'hot-ui:stubs';

    /**
     * @var string
     */
    protected $description = 'Publishes Hot-UI scaffold stubs into your app for customisation.';

    /**
     * @var string
     */
    protected $usage = 'hot-ui:stubs [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--force' => 'Overwrite existing stub files.',
    ];

    /** Internal templates directory. */
    private const STUBS_SOURCE = __DIR__.'/../Hotfire/templates';

    /**
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(array $params): int
    {
        $force = $this->has($params, 'force') || $this->has($params, '--force');
        $appPath = defined('APPPATH') ? APPPATH : (getcwd() . '/app');
        $destination = rtrim($appPath, '/\\') . '/Components/stubs/hot-ui';

        $exitError = defined('EXIT_ERROR') ? EXIT_ERROR : 1;
        $exitSuccess = defined('EXIT_SUCCESS') ? EXIT_SUCCESS : 0;

        if (! Filesystem::ensureDirectory($destination)) {
            CLI::error(sprintf('Hot-UI: cannot create stubs directory %s.', $destination));

            return $exitError;
        }

        $published = 0;
        $skipped   = 0;

        foreach (glob(self::STUBS_SOURCE . '/*.stub') ?: [] as $source) {
            $filename = basename($source);
            $target   = $destination . '/' . $filename;

            if (! $force && is_file($target)) {
                CLI::write('Skipped (exists): ', 'yellow');
                CLI::write('  ' . clean_path($target));
                ++$skipped;
                continue;
            }

            if (copy($source, $target) === false) {
                CLI::error(sprintf('Hot-UI: cannot copy %s → %s.', $filename, clean_path($target)));
                continue;
            }

            CLI::write('Published: ', 'green');
            CLI::write('  ' . clean_path($target));
            ++$published;
        }

        CLI::newLine();
        CLI::write(sprintf(
            'Hot-UI: stubs published to %s (%d written, %d skipped).',
            clean_path($destination),
            $published,
            $skipped,
        ), 'green');

        if ($published > 0) {
            CLI::newLine();
            CLI::write('`make:hotfire` will now use your local stubs automatically.', 'blue');
        }

        CLI::newLine();

        return $exitSuccess;
    }
}
