<?php

declare(strict_types=1);

namespace Components\Hotfire\Generation;

use Components\Hotfire\Support\NameTransformer;
use Components\Hotfire\Support\TemplateRenderer;

/**
 * Strategy for generating CSS content for Hotfire components.
 * 
 * This strategy follows the Single Responsibility Principle by focusing
 * exclusively on generating CSS files with proper scoping and styling.
 */
final readonly class CssContentStrategy implements ContentGenerationStrategy
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
        
        $isGlobal = ($context['global'] ?? false) === true;
        $stub = $isGlobal ? 'component_global_css.css.stub' : 'component_css.css.stub';

        return $this->templateRenderer->render($stub, [
            'class' => $className,
            'date' => date('Y-m-d'),
        ]);
    }

    public function fileExtension(): string
    {
        return 'css';
    }

    public function canHandle(array $context): bool
    {
        $type = $context['type'] ?? 'css';
        return $type === 'css' || $type === 'global-css';
    }
}
