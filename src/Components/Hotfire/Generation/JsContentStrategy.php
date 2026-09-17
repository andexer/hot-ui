<?php

declare(strict_types=1);

namespace Components\Hotfire\Generation;

use Components\Hotfire\Support\NameTransformer;
use Components\Hotfire\Support\TemplateRenderer;

/**
 * Strategy for generating JavaScript content for Hotfire components.
 * 
 * This strategy follows the Single Responsibility Principle by focusing
 * exclusively on generating JavaScript files with proper Alpine.js integration.
 */
final readonly class JsContentStrategy implements ContentGenerationStrategy
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
        
        $view = $context['view'] ?? $this->nameTransformer->normalizeComponentName($name);

        return $this->templateRenderer->render('component_js.js.stub', [
            'view' => $view,
            'class' => $className,
            'date' => date('Y-m-d'),
        ]);
    }

    public function fileExtension(): string
    {
        return 'js';
    }

    public function canHandle(array $context): bool
    {
        return ($context['type'] ?? 'js') === 'js';
    }
}
