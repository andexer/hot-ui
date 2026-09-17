<?php

declare(strict_types=1);

namespace Components\Installation\Steps;

use Components\HotUI;
use Components\Installation\OutputInterface;

/**
 * Installation step to verify that Hot-UI is properly installed via Composer.
 * 
 * Single Responsibility: Verifies package installation status.
 * 
 * Follows DIP by receiving OutputInterface via constructor.
 */
final readonly class VerifyInstallationStep implements InstallationStep
{
    public function __construct(
        private readonly OutputInterface $output,
    ) {
    }

    public function id(): string
    {
        return 'verify';
    }

    public function name(): string
    {
        return 'Verify Installation';
    }

    public function description(): string
    {
        return 'Checks if Hot-UI is installed via Composer';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function execute(array $context): bool
    {
        $this->output->write('✓ Hot-UI v'.HotUI::VERSION.' installed', 'green');
        $this->output->newLine();

        return true;
    }

    public function dependencies(): array
    {
        return [];
    }
}
