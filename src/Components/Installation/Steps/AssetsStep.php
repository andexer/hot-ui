<?php

declare(strict_types=1);

namespace Components\Installation\Steps;

use Components\Commands\PublishCommand;
use Components\Installation\OutputInterface;

/**
 * Installation step to publish runtime assets (CSS/JS).
 * 
 * Single Responsibility: Handles asset publication.
 * 
 * Follows DIP by receiving PublishCommand and OutputInterface via constructor.
 */
final readonly class AssetsStep implements InstallationStep
{
    public function __construct(
        private readonly PublishCommand $publishCommand,
        private readonly OutputInterface $output,
    ) {
    }

    public function id(): string
    {
        return 'assets';
    }

    public function name(): string
    {
        return 'Assets';
    }

    public function description(): string
    {
        return 'Publishes CSS and JS assets to public/';
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function isAvailable(): bool
    {
        return defined('FCPATH');
    }

    public function execute(array $context): bool
    {
        $this->publishCommand->initialize();
        
        $result = $this->publishCommand->run(['assets']);
        
        if ($result !== 0) {
            $this->output->error('✗ Failed to publish assets');
            return false;
        }

        $this->output->write('✓ Assets published to public/', 'green');
        $this->output->newLine();

        return true;
    }

    public function dependencies(): array
    {
        return [];
    }
}
