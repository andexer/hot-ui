<?php

declare(strict_types=1);

namespace Components\Installation\Steps;

use Components\Hotfire\Config;
use Components\Installation\OutputInterface;

/**
 * Installation step to configure Hotfire route in CodeIgniter 4.
 * 
 * Single Responsibility: Handles route configuration.
 * 
 * Follows DIP by receiving OutputInterface via constructor.
 */
final readonly class RoutesStep implements InstallationStep
{
    public function __construct(
        private readonly OutputInterface $output,
    ) {
    }

    public function id(): string
    {
        return 'routes';
    }

    public function name(): string
    {
        return 'Routes';
    }

    public function description(): string
    {
        return 'Configures Hotfire POST route in Routes.php';
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function isAvailable(): bool
    {
        return $this->findRoutesFile() !== null;
    }

    public function execute(array $context): bool
    {
        $auto = $context['auto'] ?? false;
        
        $routesFile = $this->findRoutesFile();
        if ($routesFile === null) {
            $this->output->write('⊘ Could not find Routes.php file', 'light_gray');
            $this->output->write('  You will need to add the route manually:', 'light_gray');
            $config = Config::shared();
            $this->output->write('  $routes->post(\''.$config->endpoint().'\', \Components\Ci4\Http\HotfireController::class);', 'white');
            $this->output->newLine();
            return false;
        }

        $this->output->write('✓ Found Routes.php: '.clean_path($routesFile), 'green');

        $config = Config::shared();
        $routePattern = $config->endpoint();
        $routesContent = file_get_contents($routesFile);
        if ($routesContent === false) {
            $this->output->error('✗ Could not read Routes.php');
            $this->output->newLine();
            return false;
        }

        if ($this->routeExists($routesContent, $routePattern)) {
            $this->output->write('✓ Hotfire route already configured', 'green');
            $this->output->newLine();
            return true;
        }

        if (! $auto) {
            $addRoute = $this->output->prompt('Add Hotfire route to Routes.php?', ['y', 'n'], 'y');
            if ($addRoute !== 'y') {
                $this->output->write('⊘ Route not added — you will need to add it manually', 'light_gray');
                $this->output->write('  $routes->post(\''.$routePattern.'\', \Components\Ci4\Http\HotfireController::class);', 'white');
                $this->output->newLine();
                return false;
            }
        }

        $routeLine = "\$routes->post('{$routePattern}', \\Components\\Ci4\\Http\\HotfireController::class);";

        if (! $this->addRouteToFile($routesFile, $routesContent, $routeLine)) {
            $this->output->error('✗ Failed to add route to Routes.php');
            $this->output->newLine();
            return false;
        }

        $this->output->write('✓ Hotfire route added to Routes.php', 'green');
        $this->output->newLine();

        return true;
    }

    public function dependencies(): array
    {
        return [];
    }

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

    private function routeExists(string $content, string $pattern): bool
    {
        return str_contains($content, $pattern) ||
               str_contains($content, 'HotfireController') ||
               str_contains($content, 'hot-ui/update');
    }

    private function addRouteToFile(string $file, string $content, string $line): bool
    {
        $newContent = $content.PHP_EOL.$line.PHP_EOL;
        return file_put_contents($file, $newContent) !== false;
    }
}
