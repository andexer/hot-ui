<?php

declare(strict_types=1);

namespace Components\Installation;

/**
 * Interface for installation steps in the Hot-UI installation wizard.
 * 
 * This interface follows the Single Responsibility Principle (SRP) by
 * defining a contract for each installation step. Each step represents
 * a single responsibility in the installation process.
 * 
 * This enables the Open/Closed Principle (OCP) - new steps can be
 * added without modifying the wizard logic.
 * 
 * This also follows the Interface Segregation Principle (ISP) by being
 * a small, focused interface with only the essential methods.
 */
interface InstallationStep
{
    /**
     * Gets the step identifier (e.g., 'config', 'assets', 'views').
     * 
     * @return string The step identifier
     */
    public function id(): string;

    /**
     * Gets the step name for display (e.g., 'Configuration', 'Assets').
     * 
     * @return string The step name
     */
    public function name(): string;

    /**
     * Gets the step description for the user.
     * 
     * @return string The step description
     */
    public function description(): string;

    /**
     * Checks if this step is required (cannot be skipped).
     * 
     * @return bool True if required, false if optional
     */
    public function isRequired(): bool;

    /**
     * Checks if this step is available in the current environment.
     * 
     * @return bool True if available, false otherwise
     */
    public function isAvailable(): bool;

    /**
     * Executes the installation step.
     * 
     * @param array<string, mixed> $context Context data for the step
     * @return bool True if successful, false otherwise
     */
    public function execute(array $context): bool;

    /**
     * Gets dependencies - steps that must run before this one.
     * 
     * @return array<int, string> Array of step IDs that must run first
     */
    public function dependencies(): array;
}
