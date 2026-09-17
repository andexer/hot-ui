<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Ci4\Ci4;
use Components\Hotfire\Config;

/**
 * php spark hot-ui:install [options]
 *
 * Interactive installation wizard for Hot-UI after composer require.
 * Guides through the complete setup process step by step.
 *
 *   php spark hot-ui:install              # interactive guided installation
 *   php spark hot-ui:install --auto       # non-interactive, use defaults
 *   php spark hot-ui:install --skip-tests # skip test verification
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
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
        '--auto'       => 'Non-interactive mode, use defaults for all prompts.',
        '--skip-tests' => 'Skip PHPUnit test verification step.',
        '--force'      => 'Force overwrite existing files during installation.',
    ];

    /**
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(array $params): int
    {
        $auto = in_array('--auto', $params, true);
        $skipTests = in_array('--skip-tests', $params, true);
        $force = in_array('--force', $params, true);

        CLI::write('╔════════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║          Hot-UI Installation Wizard v'.HotUI::VERSION.'               ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();

        // Step 1: Verify installation
        if (! $this->verifyInstallation()) {
            return EXIT_ERROR;
        }

        // Step 2: Configuration
        if (! $this->setupConfiguration($auto, $force)) {
            return EXIT_ERROR;
        }

        // Step 3: Publish assets and views
        if (! $this->publishAssetsAndViews($auto)) {
            return EXIT_ERROR;
        }

        // Step 4: Route configuration
        if (! $this->configureRoutes($auto, $force)) {
            CLI::write('⚠ Route configuration skipped — you may need to add it manually', 'yellow');
        }

        // Step 5: Optional stubs
        if ($this->publishStubs($auto, $force)) {
            CLI::write('✓ Stubs published for customisation', 'green');
        }

        // Step 6: Optional tests
        if (! $skipTests && ! $this->runTests($auto)) {
            CLI::write('⚠ Tests skipped or failed — installation may still work', 'yellow');
        }

        // Step 6: Final summary
        $this->showSummary();

        return EXIT_SUCCESS;
    }

    /**
     * Verifies that Hot-UI is properly installed via Composer.
     */
    private function verifyInstallation(): bool
    {
        CLI::write('Step 1: Verifying Hot-UI installation...', 'blue');
        CLI::newLine();

        if (! class_exists(HotUI::class)) {
            CLI::error('✗ Hot-UI class not found. Please run: composer require hot-ui/hot-ui');
            CLI::newLine();

            return false;
        }

        CLI::write('✓ Hot-UI v'.HotUI::VERSION.' is installed', 'green');
        CLI::newLine();

        return true;
    }

    /**
     * Sets up Hotfire configuration.
     */
    private function setupConfiguration(bool $auto, bool $force): bool
    {
        CLI::write('Step 2: Hotfire Configuration', 'blue');
        CLI::newLine();

        $configFile = rtrim(APPPATH, '/\\').'/Config/HotUI.php';
        $configExists = is_file($configFile);

        if ($configExists && ! $force) {
            if ($auto) {
                CLI::write('✓ Configuration file already exists', 'green');
                CLI::newLine();

                return true;
            }

            $overwrite = CLI::prompt('Configuration file exists. Overwrite?', ['y', 'n'], 'n');
            if ($overwrite !== 'y') {
                CLI::write('✓ Using existing configuration', 'green');
                CLI::newLine();

                return true;
            }
        }

        // Publish config
        $configCommand = new ConfigCommand();
        $configCommand->initialize();

        $configParams = $force ? ['--publish', '--force'] : ['--publish'];
        $result = $configCommand->run($configParams);

        if ($result !== EXIT_SUCCESS) {
            CLI::error('✗ Failed to publish configuration');
            CLI::newLine();

            return false;
        }

        // Check for snapshot key
        $config = Config::shared();
        if ($config->snapshotKey() === null) {
            CLI::write('⚠ HOTUI_SNAPSHOT_KEY is not set in .env', 'yellow');
            CLI::write('  Hotfire components will not work without it.', 'yellow');
            CLI::newLine();

            if (! $auto) {
                $generateKey = CLI::prompt('Generate a random snapshot key now?', ['y', 'n'], 'y');
                if ($generateKey === 'y') {
                    $this->generateSnapshotKey();
                }
            }
        } else {
            CLI::write('✓ HOTUI_SNAPSHOT_KEY is configured', 'green');
        }

        CLI::newLine();

        return true;
    }

    /**
     * Generates and adds a random snapshot key to .env file.
     */
    private function generateSnapshotKey(): void
    {
        $key = bin2hex(random_bytes(16)); // 32 characters
        $envFile = ROOTPATH.'.env';

        if (is_file($envFile)) {
            $envContent = file_get_contents($envFile);
            if ($envContent === false) {
                CLI::error('✗ Could not read .env file');

                return;
            }

            // Check if key already exists
            if (str_contains($envContent, 'HOTUI_SNAPSHOT_KEY=')) {
                CLI::write('✓ HOTUI_SNAPSHOT_KEY already exists in .env', 'green');

                return;
            }

            // Append the key
            $envContent .= PHP_EOL.'HOTUI_SNAPSHOT_KEY='.$key.PHP_EOL;
            if (file_put_contents($envFile, $envContent) === false) {
                CLI::error('✗ Could not write to .env file');

                return;
            }

            CLI::write('✓ Added HOTUI_SNAPSHOT_KEY to .env', 'green');
            CLI::write('  Key: '.$key, 'light_gray');
        } else {
            CLI::error('✗ .env file not found. Please create it and add:');
            CLI::write('  HOTUI_SNAPSHOT_KEY='.$key, 'yellow');
        }

        CLI::newLine();
    }

    /**
     * Publishes assets and views.
     */
    private function publishAssetsAndViews(bool $auto): bool
    {
        CLI::write('Step 3: Publishing Assets and Views', 'blue');
        CLI::newLine();

        $publishCommand = new PublishCommand();
        $publishCommand->initialize();

        $result = $publishCommand->run(['both']);

        if ($result !== EXIT_SUCCESS) {
            CLI::error('✗ Failed to publish assets and/or views');
            CLI::newLine();

            return false;
        }

        CLI::newLine();

        return true;
    }

    /**
     * Configures the Hotfire route in CodeIgniter 4 Routes.php.
     */
    private function configureRoutes(bool $auto, bool $force): bool
    {
        CLI::write('Step 4: Route Configuration', 'blue');
        CLI::newLine();

        $routesFile = $this->findRoutesFile();
        if ($routesFile === null) {
            CLI::write('⊘ Could not find Routes.php file', 'light_gray');
            CLI::write('  You will need to add the route manually:', 'light_gray');
            $config = Config::shared();
            CLI::write('  $routes->post(\''.$config->endpoint().'\', \Components\Ci4\Http\HotfireController::class);', 'white');
            CLI::newLine();

            return false;
        }

        CLI::write('✓ Found Routes.php: '.clean_path($routesFile), 'green');

        // Check if route already exists
        $config = Config::shared();
        $routePattern = $config->endpoint();
        $routesContent = file_get_contents($routesFile);
        if ($routesContent === false) {
            CLI::error('✗ Could not read Routes.php');
            CLI::newLine();

            return false;
        }

        if ($this->routeExists($routesContent, $routePattern)) {
            CLI::write('✓ Hotfire route already configured', 'green');
            CLI::newLine();

            return true;
        }

        // Ask user if they want to add the route
        if (! $auto) {
            $addRoute = CLI::prompt('Add Hotfire route to Routes.php?', ['y', 'n'], 'y');
            if ($addRoute !== 'y') {
                CLI::write('⊘ Route not added — you will need to add it manually', 'light_gray');
                CLI::write('  $routes->post(\''.$routePattern.'\', \Components\Ci4\Http\HotfireController::class);', 'white');
                CLI::newLine();

                return false;
            }
        }

        // Add the route
        $routeLine = "\$routes->post('{$routePattern}', \\Components\\Ci4\\Http\\HotfireController::class);";

        if (! $this->addRouteToFile($routesFile, $routesContent, $routeLine)) {
            CLI::error('✗ Failed to add route to Routes.php');
            CLI::newLine();

            return false;
        }

        CLI::write('✓ Hotfire route added to Routes.php', 'green');
        CLI::newLine();

        return true;
    }

    /**
     * Finds the CodeIgniter 4 Routes.php file.
     */
    private function findRoutesFile(): ?string
    {
        $candidates = [
            APPPATH.'Config/Routes.php',
            ROOTPATH.'app/Config/Routes.php',
            ROOTPATH.'Config/Routes.php',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Checks if the Hotfire route already exists in the routes file.
     */
    private function routeExists(string $content, string $endpoint): bool
    {
        // Check for various patterns that might indicate the route exists
        $patterns = [
            "'{$endpoint}'",
            '"'.$endpoint.'"',
            'HotfireController',
            'hot-ui/update',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($content, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds the route line to the Routes.php file.
     */
    private function addRouteToFile(string $file, string $content, string $routeLine): bool
    {
        // Try to add the route after the existing routes section
        // Look for common patterns like "Routes" or just add at the end
        $lines = explode("\n", $content);
        $insertPosition = count($lines);

        // Try to find a good insertion point (after existing routes)
        foreach ($lines as $i => $line) {
            if (str_contains($line, '$routes->') && ! str_contains($line, '//')) {
                $insertPosition = $i + 1;
            }
        }

        // Insert the route with proper indentation
        $indentation = '    ';
        array_splice($lines, $insertPosition, 0, [$indentation.$routeLine]);

        $newContent = implode("\n", $lines);

        return file_put_contents($file, $newContent, LOCK_EX) !== false;
    }

    /**
     * Optionally publishes stubs for customisation.
     */
    private function publishStubs(bool $auto, bool $force): bool
    {
        CLI::write('Step 4: Customisation Stubs (Optional)', 'blue');
        CLI::newLine();

        if ($auto) {
            CLI::write('⊘ Skipping stubs in auto mode', 'light_gray');
            CLI::newLine();

            return false;
        }

        $publishStubs = CLI::prompt('Publish scaffold stubs for customisation?', ['y', 'n'], 'n');
        if ($publishStubs !== 'y') {
            CLI::write('⊘ Stubs not published (run hot-ui:stubs later if needed)', 'light_gray');
            CLI::newLine();

            return false;
        }

        $stubsCommand = new StubsCommand();
        $stubsCommand->initialize();

        $stubsParams = $force ? ['--force'] : [];
        $result = $stubsCommand->run($stubsParams);

        if ($result !== EXIT_SUCCESS) {
            CLI::error('✗ Failed to publish stubs');
            CLI::newLine();

            return false;
        }

        CLI::newLine();

        return true;
    }

    /**
     * Runs PHPUnit tests to verify installation.
     */
    private function runTests(bool $auto): bool
    {
        CLI::write('Step 5: Installation Verification (Optional)', 'blue');
        CLI::newLine();

        if ($auto) {
            CLI::write('⊘ Skipping tests in auto mode', 'light_gray');
            CLI::newLine();

            return true;
        }

        $runTests = CLI::prompt('Run PHPUnit tests to verify installation?', ['y', 'n'], 'n');
        if ($runTests !== 'y') {
            CLI::write('⊘ Tests skipped', 'light_gray');
            CLI::newLine();

            return true;
        }

        CLI::write('Running tests...', 'light_gray');

        $testCommand = 'phpunit';
        if (is_file(ROOTPATH.'vendor/bin/phpunit')) {
            $testCommand = ROOTPATH.'vendor/bin/phpunit';
        }

        $output = [];
        $returnCode = 0;
        exec($testCommand.' --testdox', $output, $returnCode);

        CLI::newLine();

        if ($returnCode === 0) {
            CLI::write('✓ All tests passed', 'green');
            CLI::newLine();

            return true;
        }

        CLI::error('✗ Some tests failed');
        foreach (array_slice($output, -5) as $line) {
            CLI::write('  '.$line, 'light_gray');
        }
        CLI::newLine();

        return false;
    }

    /**
     * Shows installation summary and next steps.
     */
    private function showSummary(): void
    {
        CLI::write('╔════════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║              Installation Complete!                       ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();

        CLI::write('Next Steps:', 'blue');
        CLI::write('1. Register Config in BaseController::initController():', 'light_gray');
        CLI::write('   Config::setShared(new \Config\HotUI());', 'white');
        CLI::newLine();

        CLI::write('2. Create your first component:', 'light_gray');
        CLI::write('   php spark make:hotfire welcome --mfc', 'white');
        CLI::newLine();

        CLI::write('3. List existing components:', 'light_gray');
        CLI::write('   php spark hot-ui:list', 'white');
        CLI::newLine();

        CLI::write('Documentation: https://github.com/andexer/hot-ui', 'light_gray');
        CLI::newLine();
    }
}
