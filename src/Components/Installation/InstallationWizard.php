<?php

declare(strict_types=1);

namespace Components\Installation;

/**
 * Installation wizard that orchestrates installation steps.
 * 
 * This class follows SOLID principles:
 * - SRP: Only orchestrates step execution, doesn't implement step logic
 * - DIP: Depends on StepRegistry and OutputInterface abstractions
 * - OCP: New steps can be added without modifying wizard logic
 * - ISP: Only depends on abstractions, not concrete implementations
 * 
 * The wizard allows users to select which steps to execute in an
 * interactive, step-by-step manner.
 */
final class InstallationWizard
{
    /**
     * @param StepRegistry $registry The step registry
     * @param OutputInterface $output The output interface for CLI operations
     * @param array<string, mixed> $context Global context for all steps
     */
    public function __construct(
        private readonly StepRegistry $registry,
        private readonly OutputInterface $output,
        private array $context = [],
    ) {
    }

    /**
     * Sets a context value.
     * 
     * @param string $key The context key
     * @param mixed $value The context value
     * @return self For method chaining
     */
    public function setContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Runs the wizard interactively.
     * 
     * @return bool True if all selected steps succeeded, false otherwise
     */
    public function runInteractive(): bool
    {
        $this->displayHeader();

        $availableSteps = $this->registry->available();
        if (empty($availableSteps)) {
            $this->output->error('No installation steps available in current environment');
            return false;
        }

        $selectedSteps = $this->selectSteps($availableSteps);
        if (empty($selectedSteps)) {
            $this->output->write('No steps selected. Installation cancelled.', 'yellow');
            return false;
        }

        return $this->executeSteps($selectedSteps);
    }

    /**
     * Runs the wizard in auto mode (execute all available steps).
     * 
     * @return bool True if all steps succeeded, false otherwise
     */
    public function runAuto(): bool
    {
        $this->displayHeader();

        $availableSteps = $this->registry->available();
        if (empty($availableSteps)) {
            $this->output->error('No installation steps available in current environment');
            return false;
        }

        $this->output->write('Auto mode: executing all available steps', 'light_gray');
        $this->output->newLine();

        return $this->executeSteps($availableSteps);
    }

    /**
     * Displays the wizard header.
     */
    private function displayHeader(): void
    {
        $this->output->write('╔════════════════════════════════════════════════════════════╗', 'green');
        $this->output->write('║          Hot-UI Installation Wizard                        ║', 'green');
        $this->output->write('╚════════════════════════════════════════════════════════════╝', 'green');
        $this->output->newLine();
    }

    /**
     * Allows user to select which steps to execute.
     * 
     * @param array<int, InstallationStep> $steps Available steps
     * @return array<int, InstallationStep> Selected steps
     */
    private function selectSteps(array $steps): array
    {
        $ordered = $this->sortStepsByDependencies($steps);
        $selected = [];

        foreach ($ordered as $step) {
            $required = $step->isRequired() ? ' [REQUIRED]' : ' [OPTIONAL]';
            $status = $step->isRequired() ? '(auto-selected)' : '(choose below)';
            
            $this->output->write(sprintf(
                '%s %s %s',
                str_pad('•', 2),
                $step->name().$required,
                $status
            ), 'light_gray');
            $this->output->write(sprintf('  %s', $step->description()), 'light_gray');
            $this->output->newLine();

            if ($step->isRequired()) {
                $selected[] = $step;
            } else {
                $choice = $this->output->prompt(
                    sprintf('Include "%s" step?', $step->name()),
                    ['y', 'n'],
                    'n'
                );

                if ($choice === 'y') {
                    $selected[] = $step;
                }
            }
        }

        return $selected;
    }

    /**
     * Executes the selected steps in order.
     * 
     * @param array<int, InstallationStep> $steps Steps to execute
     * @return bool True if all steps succeeded, false otherwise
     */
    private function executeSteps(array $steps): bool
    {
        $ordered = $this->sortStepsByDependencies($steps);
        $total = count($ordered);
        $succeeded = 0;
        $failed = [];

        foreach ($ordered as $index => $step) {
            $stepNumber = $index + 1;
            $this->output->write(sprintf('Step %d/%d: %s', $stepNumber, $total, $step->name()), 'blue');
            $this->output->newLine();

            if ($step->execute($this->context)) {
                $succeeded++;
            } else {
                $failed[] = $step->id();
                $this->output->error(sprintf('✗ Step "%s" failed', $step->name()));
                $this->output->newLine();

                $continue = $this->output->prompt('Continue with remaining steps?', ['y', 'n'], 'y');
                if ($continue !== 'y') {
                    break;
                }
            }
        }

        $this->displaySummary($total, $succeeded, $failed);

        return empty($failed);
    }

    /**
     * Sorts steps by their dependencies using StepRegistry's ordered method.
     * 
     * @param array<int, InstallationStep> $steps Steps to sort
     * @return array<int, InstallationStep> Sorted steps
     */
    private function sortStepsByDependencies(array $steps): array
    {
        // Create a temporary registry with only these steps
        $tempRegistry = new StepRegistry();
        foreach ($steps as $step) {
            $tempRegistry->register($step);
        }
        return $tempRegistry->ordered();
    }

    /**
     * Displays the installation summary.
     * 
     * @param int $total Total steps
     * @param int $succeeded Succeeded steps
     * @param array<int, string> $failed Failed step IDs
     */
    private function displaySummary(int $total, int $succeeded, array $failed): void
    {
        $this->output->newLine();
        $this->output->write('╔════════════════════════════════════════════════════════════╗', 'green');
        $this->output->write('║                    Installation Summary                     ║', 'green');
        $this->output->write('╚════════════════════════════════════════════════════════════╝', 'green');
        $this->output->newLine();
        $this->output->write(sprintf('Total steps:  %d', $total), 'white');
        $this->output->write(sprintf('Succeeded:   %d', $succeeded), 'green');
        $this->output->write(sprintf('Failed:      %d', count($failed)), count($failed) > 0 ? 'red' : 'white');
        $this->output->newLine();

        if (! empty($failed)) {
            $this->output->write('Failed steps:', 'red');
            foreach ($failed as $failedId) {
                $this->output->write(sprintf('  - %s', $failedId), 'red');
            }
            $this->output->newLine();
        }

        if (empty($failed)) {
            $this->output->write('✓ Installation completed successfully!', 'green');
        } else {
            $this->output->warn('⚠ Installation completed with errors. Some steps may need manual intervention.');
        }
        $this->output->newLine();
    }
}
