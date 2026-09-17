<?php

declare(strict_types=1);

namespace Components\Hotfire\Generation;

/**
 * Strategy interface for generating different types of component content.
 * 
 * This interface implements the Open/Closed Principle (OCP) by allowing
 * new content generation strategies to be added without modifying existing
 * code. Each strategy handles a specific type of content generation.
 * 
 * Examples of strategies:
 * - ClassContentStrategy: Generates PHP class files
 * - ViewContentStrategy: Generates template files
 * - JsContentStrategy: Generates JavaScript files
 * - CssContentStrategy: Generates CSS files
 * - TestContentStrategy: Generates PHPUnit test files
 */
interface ContentGenerationStrategy
{
    /**
     * Generates content for a specific type of component artifact.
     * 
     * @param string $name The component name
     * @param array<string, mixed> $context Context data for generation
     * @return string The generated content
     */
    public function generate(string $name, array $context): string;

    /**
     * Gets the file extension for this content type.
     * 
     * @return string The file extension (e.g., 'php', 'js', 'css')
     */
    public function fileExtension(): string;

    /**
     * Checks if this strategy can handle the given context.
     * 
     * @param array<string, mixed> $context The generation context
     * @return bool True if this strategy can handle the context
     */
    public function canHandle(array $context): bool;
}
