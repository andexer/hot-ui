<?php

declare(strict_types=1);

namespace Components\Hotfire\Generation;

use Components\Hotfire\Support\NameTransformer;
use Components\Hotfire\Support\TemplateRenderer;

/**
 * Strategy for generating PHP class content for Hotfire components.
 * 
 * This strategy follows the Single Responsibility Principle by focusing
 * exclusively on generating PHP class files with proper namespace,
 * properties, and method scaffolding.
 */
final readonly class ClassContentStrategy implements ContentGenerationStrategy
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
        $view = $context['view'] ?? $this->nameTransformer->normalizeComponentName($name);
        $props = $context['props'] ?? [];
        $propsAndSave = $this->generatePropsAndSaveBlock($props);

        return $this->templateRenderer->render('component_class.php.stub', [
            'namespace' => $namespace,
            'class' => $className,
            'view' => $view,
            'propsAndSave' => $propsAndSave,
            'date' => date('Y-m-d'),
        ]);
    }

    public function fileExtension(): string
    {
        return 'php';
    }

    public function canHandle(array $context): bool
    {
        return ($context['type'] ?? 'class') === 'class';
    }

    /**
     * Generates the properties and save action block for the class.
     * 
     * @param array<int, string> $props The property names
     * @return string The generated block
     */
    private function generatePropsAndSaveBlock(array $props): string
    {
        if ($props === []) {
            return '';
        }

        $properties = implode("\n\n", array_map(
            static fn (string $prop): string => "    public string \${$prop} = '';",
            $props,
        ));
        
        $save = rtrim($this->templateRenderer->render('component_action_save.php.stub', []))."\n";

        return $properties."\n\n".$save;
    }
}
