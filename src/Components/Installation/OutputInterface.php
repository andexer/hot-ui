<?php

declare(strict_types=1);

namespace Components\Installation;

/**
 * Interface for CLI output in installation steps.
 * 
 * This abstraction follows the Dependency Inversion Principle (DIP) by
 * allowing steps to depend on this interface rather than directly on
 * CodeIgniter's CLI. This makes steps testable and framework-independent.
 */
interface OutputInterface
{
    /**
     * Writes a message to the console.
     * 
     * @param string $message The message to write
     * @param string $color The color (e.g., 'white', 'green', 'red', 'yellow')
     * @return void
     */
    public function write(string $message, string $color = 'white'): void;

    /**
     * Writes an error message to the console.
     * 
     * @param string $message The error message
     * @return void
     */
    public function error(string $message): void;

    /**
     * Writes a warning message to the console.
     * 
     * @param string $message The warning message
     * @return void
     */
    public function warn(string $message): void;

    /**
     * Writes a new line to the console.
     * 
     * @return void
     */
    public function newLine(): void;

    /**
     * Prompts the user for input.
     * 
     * @param string $question The question to ask
     * @param array<int, string> $options Valid options
     * @param string $default Default value
     * @return string The user's response
     */
    public function prompt(string $question, array $options, string $default): string;
}
