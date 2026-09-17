<?php

declare(strict_types=1);

namespace Components\Installation;

use CodeIgniter\CLI\CLI;

/**
 * CodeIgniter CLI implementation of OutputInterface.
 * 
 * This class follows the Adapter pattern by adapting CodeIgniter's CLI
 * to the OutputInterface abstraction. This allows the installation
 * steps to use CodeIgniter's CLI through the abstraction.
 */
final readonly class CodeIgniterOutput implements OutputInterface
{
    public function write(string $message, string $color = 'white'): void
    {
        CLI::write($message, $color);
    }

    public function error(string $message): void
    {
        CLI::error($message);
    }

    public function warn(string $message): void
    {
        CLI::write($message, 'yellow');
    }

    public function newLine(): void
    {
        CLI::newLine();
    }

    public function prompt(string $question, array $options, string $default): string
    {
        return CLI::prompt($question, $options, $default);
    }
}
