<?php

declare(strict_types=1);

namespace Components\Installation;

use Components\Commands\ConfigCommand;
use Components\Commands\PublishCommand;
use Components\Installation\Steps\AssetsStep;
use Components\Installation\Steps\BuildConfigStep;
use Components\Installation\Steps\ConfigurationStep;
use Components\Installation\Steps\RoutesStep;
use Components\Installation\Steps\VerifyInstallationStep;
use Components\Installation\Steps\ViewsStep;

/**
 * Provides default installation steps.
 * 
 * This class follows the Single Responsibility Principle (SRP) by only
 * being responsible for registering default steps. It also follows
 * the Dependency Inversion Principle (DIP) by receiving commands via
 * constructor injection rather than instantiating them directly.
 * 
 * Users can create their own providers to customize the installation process.
 */
final readonly class DefaultStepProvider
{
    public function __construct(
        private readonly ConfigCommand $configCommand,
        private readonly PublishCommand $publishCommand,
    ) {
    }

    /**
     * Registers default installation steps.
     * 
     * @param StepRegistry $registry The registry to populate
     * @param OutputInterface $output The output interface for CLI operations
     * @return StepRegistry The populated registry
     */
    public function register(StepRegistry $registry, OutputInterface $output): StepRegistry
    {
        $registry
            ->register(new VerifyInstallationStep($output))
            ->register(new ConfigurationStep($this->configCommand, $output))
            ->register(new AssetsStep($this->publishCommand, $output))
            ->register(new ViewsStep($this->publishCommand, $output))
            ->register(new RoutesStep($output))
            ->register(new BuildConfigStep($this->publishCommand, $output));

        return $registry;
    }
}
