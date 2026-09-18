<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\Config;
use Components\Support\Filesystem;

/**
 * php spark hot-ui:config [options]
 *
 * Without options, shows the active Hotfire configuration in the console:
 * endpoint, view prefix, signing key status and reserved methods.
 *
 *   php spark hot-ui:config           # display active config
 *   php spark hot-ui:config --check   # exit non-zero when HOTUI_SNAPSHOT_KEY is missing
 *   php spark hot-ui:config --publish # scaffold app/Config/HotUI.php in the host app
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 */
final class ConfigCommand extends BaseCommand
{
    use CliOptions;

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'hot-ui:config';

    /**
     * @var string
     */
    protected $description = 'Displays the active Hotfire configuration (endpoint, key, view prefix).';

    /**
     * @var string
     */
    protected $usage = 'hot-ui:config [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--check'   => 'Exit with error code when HOTUI_SNAPSHOT_KEY is not set.',
        '--publish' => 'Scaffold app/Config/HotUI.php in the host application.',
        '--force'   => 'Overwrite the published config file (only with --publish).',
    ];

    /**
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(array $params): int
    {
        $check   = $this->has($params, 'check') || $this->has($params, '--check');
        $publish = $this->has($params, 'publish') || $this->has($params, '--publish');
        $force   = $this->has($params, 'force') || $this->has($params, '--force');

        if ($publish) {
            return $this->publishConfig($force);
        }

        return $this->displayConfig($check);
    }

    // -------------------------------------------------------------------------
    // Display
    // -------------------------------------------------------------------------

    private function displayConfig(bool $check): int
    {
        $config = Config::shared();

        $keyStatus = $config->snapshotKey() !== null
            ? '✓ set (env: HOTUI_SNAPSHOT_KEY)'
            : '✗ MISSING — set HOTUI_SNAPSHOT_KEY in your .env';

        $keyOk = $config->snapshotKey() !== null;

        CLI::write('Hot-UI active configuration', 'green');
        CLI::write(str_repeat('─', 50));
        CLI::newLine();

        CLI::write(sprintf('  %-22s %s', 'Endpoint:', $config->endpoint()));
        CLI::write(sprintf('  %-22s %s', 'View prefix:', $config->viewPrefix()));
        CLI::write(sprintf('  %-22s %s', 'Snapshot key:', $keyStatus), $keyOk ? 'white' : 'red');
        CLI::write(sprintf(
            '  %-22s %s',
            'Reserved methods:',
            implode(', ', $config->reserved()),
        ));

        CLI::newLine();

        if ($check && ! $keyOk) {
            CLI::error('Hot-UI: HOTUI_SNAPSHOT_KEY is not set. Hotfire cannot sign snapshots.');
            CLI::write('Fix: add  HOTUI_SNAPSHOT_KEY=<32-char random string>  to your .env', 'yellow');
            CLI::newLine();

            return defined('EXIT_ERROR') ? EXIT_ERROR : 1;
        }

        return defined('EXIT_SUCCESS') ? EXIT_SUCCESS : 0;
    }

    // -------------------------------------------------------------------------
    // Publish
    // -------------------------------------------------------------------------

    private function publishConfig(bool $force): int
    {
        $appPath = defined('APPPATH') ? APPPATH : (getcwd() . '/app');
        $destination = rtrim($appPath, '/\\') . '/Config/HotUI.php';

        $exitError = defined('EXIT_ERROR') ? EXIT_ERROR : 1;
        $exitSuccess = defined('EXIT_SUCCESS') ? EXIT_SUCCESS : 0;

        if (! $force && is_file($destination)) {
            CLI::write('Skipped (exists): ', 'yellow');
            CLI::write('  ' . clean_path($destination));
            CLI::newLine();
            CLI::write('Use --force to overwrite.', 'yellow');
            CLI::newLine();

            return $exitSuccess;
        }

        $configDir = dirname($destination);
        if (! Filesystem::ensureDirectory($configDir)) {
            CLI::error(sprintf('Hot-UI: cannot create directory %s.', clean_path($configDir)));

            return $exitError;
        }

        if (file_put_contents($destination, $this->configStub(), LOCK_EX) === false) {
            CLI::error(sprintf('Hot-UI: cannot write %s.', clean_path($destination)));

            return $exitError;
        }

        CLI::write('Published: ', 'green');
        CLI::write('  ' . clean_path($destination));
        CLI::newLine();
        CLI::write('Edit the file to customise endpoint, view prefix and snapshot key.', 'blue');
        CLI::newLine();

        return EXIT_SUCCESS;
    }

    private function configStub(): string
    {
        $version = \Components\HotUI::VERSION;

        return <<<PHP
<?php

declare(strict_types=1);

namespace Config;

use Components\Config\HotUI as HotUIConfig;

/**
 * Hot-UI Configuration for this CodeIgniter 4 application.
 * 
 * This configuration file controls the behavior of Hot-UI components.
 * Similar to Livewire's config, it allows you to customize component
 * locations, namespaces, and generation behavior.
 * 
 * Publish this file to your application: php spark hot-ui:config --publish
 * 
 * @version {$version}
 */
class HotUI extends HotUIConfig
{
    public function __construct(?\Psr\Log\LoggerInterface $logger = null, ?\CodeIgniter\CLI\Commands $commands = null)
    {
        if ($logger !== null && $commands !== null) {
            parent::__construct($logger, $commands);
        }
        
        // Override default configuration values here:
        
        // Component locations (root directories for component discovery)
        // $this->componentLocations = [
        //     APPPATH.'Views/components',
        //     APPPATH.'Views/layouts',
        // ];
        
        // Component namespaces (optional custom namespaces)
        // $this->componentNamespaces = [
        //     'layouts' => APPPATH.'Views/layouts',
        //     'pages' => APPPATH.'Views/pages',
        // ];
        
        // Default emoji for component folders (set to false to disable)
        // $this->makeCommand['emoji'] = true;
        // $this->makeCommand['default_emoji'] = '🔥';
        
        // Default file generation options
        // $this->makeCommand['with'] = [
        //     'js' => false,
        //     'css' => false,
        //     'global_css' => false,
        //     'test' => false,
        // ];
        
        // Root class namespace for components
        // $this->classNamespace = 'App\\Components';
        
        // Paths for component generation
        // $this->classPath = APPPATH.'Components';
        // $this->viewPath = APPPATH.'Views/components';
        
        // Snapshot key for security (prefer env('HOTUI_SNAPSHOT_KEY'))
        // $this->snapshotKey = env('HOTUI_SNAPSHOT_KEY', null);
        
        // Development mode (additional debugging info)
        // $this->developmentMode = false;
    }
}
PHP;
    }
}
