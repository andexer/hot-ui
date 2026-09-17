<?php

declare(strict_types=1);

namespace Components\Hotfire\Generation;

use Components\Hotfire\Support\NameTransformer;
use Components\Hotfire\Support\TemplateRenderer;

/**
 * Strategy for generating template content for Hotfire components.
 * 
 * This strategy follows the Single Responsibility Principle by focusing
 * exclusively on generating PHP template files with proper hot:* directives
 * and form structures.
 */
final readonly class ViewContentStrategy implements ContentGenerationStrategy
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly NameTransformer $nameTransformer,
    ) {}

    public function generate(string $name, array $context): string
    {
        $props = $context['props'] ?? [];
        $className = $this->nameTransformer->toPascalCase(
            $this->nameTransformer->splitComponentName($name)[count($this->nameTransformer->splitComponentName($name)) - 1] ?? $name
        );

        if ($props === []) {
            return $this->templateRenderer->render('component_view_plain.php.stub', [
                'label' => $className,
                'class' => $className,
                'props' => $props,
                'date' => date('Y-m-d'),
            ]);
        }

        $fields = '';
        foreach ($props as $prop) {
            $fields .= $this->templateRenderer->render('component_view_field.php.stub', [
                'title' => ucfirst(str_replace(['_', '-'], ' ', $prop)),
                'prop' => $prop,
            ]);
        }

        return $this->templateRenderer->render('component_view.php.stub', [
            'fields' => $fields,
            'class' => $className,
            'props' => $props,
            'date' => date('Y-m-d'),
        ]);
    }

    public function fileExtension(): string
    {
        return 'php';
    }

    public function canHandle(array $context): bool
    {
        return ($context['type'] ?? 'view') === 'view';
    }
}
