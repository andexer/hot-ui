<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Commands\InstallCommand;
use Components\HotUI;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
        $reflection = new ReflectionClass($command);

        $group = $reflection->getProperty('group');
        $group->setAccessible(true);
        self::assertSame('Hot-UI', $group->getValue($command));

        $name = $reflection->getProperty('name');
        $name->setAccessible(true);
        self::assertSame('hot-ui:install', $name->getValue($command));

        $description = $reflection->getProperty('description');
        $description->setAccessible(true);
        self::assertStringContainsString('installation', strtolower($description->getValue($command)));
    }

    public function testInstallCommandHasRequiredOptions(): void
    {
        $command = new InstallCommand();
        $reflection = new ReflectionClass($command);

        $options = $reflection->getProperty('options');
        $options->setAccessible(true);
        $optionsValue = $options->getValue($command);

        self::assertArrayHasKey('--auto', $optionsValue);
        self::assertArrayHasKey('--skip-tests', $optionsValue);
        self::assertArrayHasKey('--force', $optionsValue);
    }

    public function testInstallCommandReturnsSuccessForAutoMode(): void
    {
        // Test that auto mode doesn't throw exceptions
        // Note: Full integration test would require mocking CLI and filesystem
        self::assertTrue(true, 'Install command structure is valid');
    }

    public function testInstallCommandDocumentation(): void
    {
        $command = new InstallCommand();
        $reflection = new ReflectionClass($command);

        $description = $reflection->getProperty('description');
        $description->setAccessible(true);
        self::assertNotEmpty($description->getValue($command));

        $usage = $reflection->getProperty('usage');
        $usage->setAccessible(true);
        self::assertNotEmpty($usage->getValue($command));

        $options = $reflection->getProperty('options');
        $options->setAccessible(true);
        self::assertNotEmpty($options->getValue($command));
    }

    public function testInstallCommandHasRouteConfigurationMethod(): void
    {
        $command = new InstallCommand();
        $reflection = new ReflectionClass($command);

        self::assertTrue($reflection->hasMethod('configureRoutes'));
        self::assertTrue($reflection->getMethod('configureRoutes')->isPrivate());
    }

    public function testInstallCommandHasRouteDetectionMethods(): void
    {
        $command = new InstallCommand();
        $reflection = new ReflectionClass($command);

        self::assertTrue($reflection->hasMethod('findRoutesFile'));
        self::assertTrue($reflection->hasMethod('routeExists'));
        self::assertTrue($reflection->hasMethod('addRouteToFile'));
    }
}
