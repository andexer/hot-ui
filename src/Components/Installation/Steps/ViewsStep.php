<?php

declare(strict_types=1);

namespace Components\Installation\Steps;

use Components\Commands\PublishCommand;
use Components\Installation\OutputInterface;

/**
 * Installation step to publish component views.
 * 
 * Single Responsibility: Handles view publication.
 * 
 * Follows DIP by receiving PublishCommand and OutputInterface via constructor.
 */
final readonly class ViewsStep implements InstallationStep
{
    public function __construct(
        private readonly PublishCommand $publishCommand,
        private readonly OutputInterface $output,
    ) {
    }

    public function id(): string
    {
        return 'views';
    }

    public function name(): string
    {
        return 'Views';
    }

    public function description(): string
    {
        return 'Publishes component views (components/, layouts/, partials/)';
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
        $this->publishCommand->initialize();
        
        $result = $this->publishCommand->run(['views']);
        
        if ($result !== 0) {
            $this->output->error('✗ Failed to publish views');
            return false;
        }

        $this->output->write('✓ Views published to app/Views/', 'green');
        $this->output->newLine();

        return true;
    }

    public function dependencies(): array
    {
        return [];
    }
}
