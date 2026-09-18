<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Commands\ConfigCommand;
use Components\Commands\PublishCommand;
use Components\HotUI;
use Components\Installation\CodeIgniterOutput;
use Components\Installation\DefaultStepProvider;
use Components\Installation\InstallationWizard;
use Components\Installation\OutputInterface;
use Components\Installation\StepRegistry;

/**
 * php spark hot-ui:install [options]
 *
 * Interactive installation wizard for Hot-UI after composer require.
 * Guides through the complete setup process step by step using SOLID principles.
 *
 *   php spark hot-ui:install              # interactive guided installation
 *   php spark hot-ui:install --auto       # non-interactive, execute all available steps
 *   php spark hot-ui:install --force      # force overwrite existing files
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 * 
 * This command follows SOLID principles:
 * - SRP: Only handles CLI interface, delegates to wizard
 * - DIP: Depends on InstallationWizard and OutputInterface abstractions
 * - OCP: New steps can be added without modifying this command
 */
final class InstallCommand extends BaseCommand
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

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'hot-ui:install';

    /**
     * @var string
     */
    protected $description = 'Interactive installation wizard for Hot-UI after composer require.';

    /**
     * @var string
     */
    protected $usage = 'hot-ui:install [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--auto'       => 'Non-interactive mode, execute all available steps.',
        '--force'      => 'Force overwrite existing files during installation.',
        '--skip-tests' => 'Do not verify or run the PHPUnit suite as part of installation.',
    ];

    /**
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(array $params): int
    {
        $auto = in_array('--auto', $params, true);
        $force = in_array('--force', $params, true);
        $skipTests = in_array('--skip-tests', $params, true);

        // Create output interface (CodeIgniter adapter)
        $output = new CodeIgniterOutput();

        // Create commands
        $configCommand = new ConfigCommand();
        $publishCommand = new PublishCommand();

        // Create step registry with default steps
        $registry = new StepRegistry();
        $provider = new DefaultStepProvider($configCommand, $publishCommand);
        $provider->register($registry, $output);

        // Create wizard with context and output
        $wizard = new InstallationWizard($registry, $output, [
            'auto' => $auto,
            'force' => $force,
            'skipTests' => $skipTests,
        ]);

        // Run wizard
        $success = $auto ? $wizard->runAuto() : $wizard->runInteractive();

        if (! $success) {
            return EXIT_ERROR;
        }

        // Display next steps
        $this->displayNextSteps();

        return EXIT_SUCCESS;
    }

    /**
     * Registers the Hotfire POST round-trip route in the app's Routes.php when
     * none exists yet.
     *
     * Idempotent: it never touches a Routes.php that already binds the
     * configured endpoint. The interactive wizard routes through RoutesStep;
     * this is the same capability exposed directly on the command.
     */
    private function configureRoutes(): void
    {
        $file = $this->findRoutesFile();
        if ($file === null) {
            CLI::newLine();
            CLI::write('Route configuration skipped: app/Config/Routes.php not found.', 'yellow');
            return;
        }

        $endpoint = Config::shared()->endpoint();
        if ($this->routeExists($file, $endpoint)) {
            return;
        }

        if ($this->addRouteToFile($file, $endpoint)) {
            CLI::write('Hotfire POST route added to '.$file, 'green');
        }
    }

    /**
     * Locates the app's route file (CodeIgniter 4 keeps it under
     * app/Config/Routes.php).
     */
    private function findRoutesFile(): ?string
    {
        if (is_file('app/Config/Routes.php')) {
            return 'app/Config/Routes.php';
        }

        return is_file('app/Config/routes.php') ? 'app/Config/routes.php' : null;
    }

    /**
     * A route already binds the endpoint when a POST definition matches it.
     */
    private function routeExists(string $file, string $endpoint): bool
    {
        $content = file_get_contents($file);
        if (! is_string($content)) {
            return false;
        }

        return preg_match('/\$routes->post\s*\(\s*[\'"]'.preg_quote($endpoint, '/').'/is', $content) === 1;
    }

    /**
     * Inserts the Hotfire POST route just before the closing brace of Routes.php.
     */
    private function addRouteToFile(string $file, string $endpoint): bool
    {
        $content = file_get_contents($file);
        if (! is_string($content)) {
            return false;
        }

        $route = sprintf(
            "\n\$routes->post('%s', \\Components\\Ci4\\Http\\HotfireController::class);",
            $endpoint,
        );

        $pos = strrpos($content, '}');
        if ($pos === false) {
            $content .= $route."\n";
        } else {
            $content = substr($content, 0, $pos).$route."\n".substr($content, $pos);
        }

        return file_put_contents($file, $content) !== false;
    }

    /**
     * Displays next steps after successful installation.
     */
    private function displayNextSteps(): void
    {
        CLI::newLine();
        CLI::write('╔════════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║                  Installation Complete                     ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();
        CLI::write('Next steps:', 'blue');
        CLI::write('  1. Review your configuration in app/Config/HotUI.php', 'white');
        CLI::write('  2. Generate your first component: php spark make:hotfire hello', 'white');
        CLI::write('  3. Add HOTUI_SNAPSHOT_KEY to your .env for security', 'white');
        CLI::newLine();
    }
}
