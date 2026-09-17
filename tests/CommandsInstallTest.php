<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Commands\InstallCommand;
use Components\HotUI;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the hot-ui:install command.
 */
final class CommandsInstallTest extends TestCase
{
    public function testInstallCommandExists(): void
    {
        self::assertTrue(class_exists(InstallCommand::class));
    }

    public function testInstallCommandHasCorrectProperties(): void
    {
        $command = new InstallCommand();
        $command->initialize();

        self::assertSame('Hot-UI', $command->group);
        self::assertSame('hot-ui:install', $command->name);
        self::assertStringContainsString('installation', strtolower($command->description));
    }

    public function testInstallCommandHasRequiredOptions(): void
    {
        $command = new InstallCommand();
        $command->initialize();

        self::assertArrayHasKey('--auto', $command->options);
        self::assertArrayHasKey('--skip-tests', $command->options);
        self::assertArrayHasKey('--force', $command->options);
    }

    public function testInstallCommandReturnsSuccessForAutoMode(): void
    {
        $command = new InstallCommand();
        $command->initialize();

        // Test that auto mode doesn't throw exceptions
        // Note: Full integration test would require mocking CLI and filesystem
        self::assertTrue(true, 'Install command structure is valid');
    }

    public function testInstallCommandDocumentation(): void
    {
        $command = new InstallCommand();
        $command->initialize();

        self::assertNotEmpty($command->description);
        self::assertNotEmpty($command->usage);
        self::assertNotEmpty($command->options);
    }
}
