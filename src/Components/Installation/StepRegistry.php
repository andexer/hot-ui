<?php

declare(strict_types=1);

namespace Components\Installation;

use Components\Installation\Exception\CircularDependencyException;

/**
 * Registry for installation steps.
 * 
 * This class follows the Dependency Inversion Principle (DIP) by providing
 * a registry that can be injected into the wizard. The wizard depends on
 * this abstraction rather than directly instantiating steps.
 * 
 * This also follows the Open/Closed Principle (OCP) - new steps can be
 * registered without modifying the registry logic.
 */
final class StepRegistry
{
    /**
     * @var array<string, InstallationStep>
     */
    private array $steps = [];

    /**
     * Registers an installation step.
     * 
     * @param InstallationStep $step The step to register
     * @return self For method chaining
     */
    public function register(InstallationStep $step): self
    {
        $this->steps[$step->id()] = $step;
        return $this;
    }

    /**
     * Gets a step by ID.
     * 
     * @param string $id The step ID
     * @return InstallationStep|null The step, or null if not found
     */
    public function get(string $id): ?InstallationStep
    {
        return $this->steps[$id] ?? null;
    }

    /**
     * Gets all registered steps.
     * 
     * @return array<string, InstallationStep>
     */
    public function all(): array
    {
        return $this->steps;
    }

    /**
     * Gets available steps (steps that are available in the current environment).
     * 
     * @return array<int, InstallationStep>
     */
    public function available(): array
    {
        return array_filter($this->steps, fn (InstallationStep $step): bool => $step->isAvailable());
    }

    /**
     * Gets required steps.
     * 
     * @return array<int, InstallationStep>
     */
    public function required(): array
    {
        return array_filter($this->steps, fn (InstallationStep $step): bool => $step->isRequired());
    }

    /**
     * Gets optional steps.
     * 
     * @return array<int, InstallationStep>
     */
    public function optional(): array
    {
        return array_filter($this->steps, fn (InstallationStep $step): bool => ! $step->isRequired());
    }

    /**
     * Gets steps in execution order (respecting dependencies).
     * 
     * @return array<int, InstallationStep>
     */
    public function ordered(): array
    {
        $ordered = [];
        $visited = [];
        $visiting = [];

        foreach ($this->steps as $step) {
            $this->visit($step, $ordered, $visited, $visiting);
        }

        return $ordered;
    }

    /**
     * Visits a step for topological sorting.
     * 
     * @param InstallationStep $step The step to visit
     * @param array<int, InstallationStep> $ordered The ordered list
     * @param array<string, bool> $visited Visited steps
     * @param array<string, bool> $visiting Currently visiting steps
     */
    private function visit(
        InstallationStep $step,
        array &$ordered,
        array &$visited,
        array &$visiting
    ): void {
        $id = $step->id();

        if (isset($visited[$id])) {
            return;
        }

        if (isset($visiting[$id])) {
            throw new CircularDependencyException($id);
        }

        $visiting[$id] = true;

        foreach ($step->dependencies() as $depId) {
            $dep = $this->get($depId);
            if ($dep !== null) {
                $this->visit($dep, $ordered, $visited, $visiting);
            }
        }

        unset($visiting[$id]);
        $visited[$id] = true;
        $ordered[] = $step;
    }
}
