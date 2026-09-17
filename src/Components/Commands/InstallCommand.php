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
        '--auto'  => 'Non-interactive mode, execute all available steps.',
        '--force' => 'Force overwrite existing files during installation.',
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
