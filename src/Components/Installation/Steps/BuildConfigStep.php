<?php

declare(strict_types=1);

namespace Components\Installation\Steps;

use Components\Commands\PublishCommand;
use Components\Installation\OutputInterface;

/**
 * Installation step to publish build configuration files (Tailwind, PostCSS, Vite).
 * 
 * Single Responsibility: Handles build configuration publication.
 * 
 * Follows DIP by receiving PublishCommand and OutputInterface via constructor.
 */
final readonly class BuildConfigStep implements InstallationStep
{
    public function __construct(
        private readonly PublishCommand $publishCommand,
        private readonly OutputInterface $output,
    ) {
    }

    public function id(): string
    {
        return 'build';
    }

    public function name(): string
    {
        return 'Build Configuration';
    }

    public function description(): string
    {
        return 'Publishes Tailwind CSS v4, PostCSS, and Vite configs';
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function isAvailable(): bool
    {
        return defined('ROOTPATH');
    }

    public function execute(array $context): bool
    {
        $this->publishCommand->initialize();
        
        $result = $this->publishCommand->run(['build']);
        
        if ($result !== 0) {
            $this->output->error('✗ Failed to publish build configs');
            return false;
        }

        $this->output->write('✓ Build configs published to project root', 'green');
        $this->output->write('  Run: npm install -D tailwindcss@next postcss autoprefixer vite', 'yellow');
        $this->output->write('  Run: npm run dev (development) or npm run build (production)', 'yellow');
        $this->output->newLine();

        return true;
    }

    public function dependencies(): array
    {
        return [];
    }
}
