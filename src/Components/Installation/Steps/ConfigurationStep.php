<?php

declare(strict_types=1);

namespace Components\Installation\Steps;

use Components\Commands\ConfigCommand;
use Components\Installation\OutputInterface;

/**
 * Installation step to publish and configure Hot-UI configuration.
 * 
 * Single Responsibility: Handles configuration file setup.
 * 
 * Follows DIP by receiving ConfigCommand and OutputInterface via constructor.
 */
final readonly class ConfigurationStep implements InstallationStep
{
    public function __construct(
        private readonly ConfigCommand $configCommand,
        private readonly OutputInterface $output,
    ) {
    }

    public function id(): string
    {
        return 'config';
    }

    public function name(): string
    {
        return 'Configuration';
    }

    public function description(): string
    {
        return 'Publishes and configures app/Config/HotUI.php';
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function isAvailable(): bool
    {
        return defined('APPPATH');
    }

    public function execute(array $context): bool
    {
        $force = $context['force'] ?? false;
        
        $this->configCommand->initialize();
        
        $result = $this->configCommand->run(['--publish', '--force' => $force]);
        
        if ($result !== 0) {
            $this->output->error('✗ Failed to publish configuration');
            return false;
        }

        $this->output->write('✓ Configuration published to app/Config/HotUI.php', 'green');
        $this->output->newLine();

        return true;
    }

    public function dependencies(): array
    {
        return ['verify'];
    }
}
