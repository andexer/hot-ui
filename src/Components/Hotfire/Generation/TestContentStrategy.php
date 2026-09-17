<?php

declare(strict_types=1);

namespace Components\Hotfire\Generation;

use Components\Hotfire\Support\NameTransformer;
use Components\Hotfire\Support\TemplateRenderer;

/**
 * Strategy for generating PHPUnit test content for Hotfire components.
 * 
 * This strategy follows the Single Responsibility Principle by focusing
 * exclusively on generating PHP test files with proper test structure.
 */
final readonly class TestContentStrategy implements ContentGenerationStrategy
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly NameTransformer $nameTransformer,
    ) {}

    public function generate(string $name, array $context): string
    {
        $className = $this->nameTransformer->toPascalCase(
            $this->nameTransformer->splitComponentName($name)[count($this->nameTransformer->splitComponentName($name)) - 1] ?? $name
        );
        
        $namespace = $context['namespace'] ?? 'App\\Components';
        
        // Test namespace mirrors the class namespace under "Tests":
        // post.create → App\Components\Post\Create → Tests\Components\Post.
        $parts = explode('\\', $namespace);
        $parts[0] = 'Tests';
        $testNs = implode('\\', $parts);

        return $this->templateRenderer->render('component_test.php.stub', [
            'testNamespace' => $testNs,
            'classFqcn' => $namespace.'\\'.$className,
            'class' => $className,
            'date' => date('Y-m-d'),
        ]);
    }

    public function fileExtension(): string
    {
        return 'php';
    }

    public function canHandle(array $context): bool
    {
        return ($context['type'] ?? 'test') === 'test';
    }
}
