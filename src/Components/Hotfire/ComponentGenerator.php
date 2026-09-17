<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Exception\InvalidNamespaceException;
use Components\Hotfire\Generation\ContentGenerationStrategy;
use Components\Hotfire\Generation\ClassContentStrategy;
use Components\Hotfire\Generation\ViewContentStrategy;
use Components\Hotfire\Generation\JsContentStrategy;
use Components\Hotfire\Generation\CssContentStrategy;
use Components\Hotfire\Generation\TestContentStrategy;
use Components\Hotfire\Support\FilesystemInterface;
use Components\Hotfire\Support\LocalFilesystem;
use Components\Hotfire\Support\NameTransformer;
use Components\Hotfire\Support\PathGenerator;
use Components\Hotfire\Support\PropertyValidator;
use Components\Hotfire\Support\TemplateRenderer;

/**
 * Scaffolds Hotfire reactive components for CodeIgniter 4 apps.
 *
 * Refactored to follow SOLID principles:
 * - SRP: Delegates responsibilities to specialized classes
 * - OCP: Uses Strategy pattern for content generation
 * - DIP: Depends on FilesystemInterface abstraction
 * - ISP: Uses focused interfaces for each responsibility
 *
 * Turns a dotted/slashed component name into the Livewire 4 "voltage" layout,
 * adapted for Hotfire — one self-contained folder per component, marked with
 * the 🔥 indicator (default; change via --emoji / the constructor):
 *
 *   php spark make:hotfire post.create --mfc --js --css --test
 *
 *   app/Views/components/post/🔥create/
 *   ├── create.php            # class extends Components\Hotfire\Component
 *   ├── create.view.php       # template ($component + hot:* directives)
 *   ├── create.js             # optional (--js)
 *   ├── create.css            # optional scoped (--css)
 *   ├── create.global.css     # optional (--global-css)
 *   └── create.test.php       # optional PHPUnit (--test)
 *
 * This class is framework-free: the command wrappers (Components\Commands)
 * only handle CLI plumbing and delegate all content/path logic here.
 *
 * Configure component locations via config/hot-ui.php (similar to Livewire).
 */
final readonly class ComponentGenerator
{
    /**
     * @param string      $viewsRoot      Root of the app views folder (Hotfire prefix lives under it).
     * @param string      $namespace      Root namespace for generated classes (default "App\Components").
     * @param string      $templatesDir   Folder with the *.stub scaffolds consumed by this generator.
     * @param string      $emoji          Visual indicator prefix on each component folder (default "🔥").
     * @param string|null $customStubsDir Optional directory with user-published stubs (via hot-ui:stubs).
     *                                    When set, stubs found there take precedence over $templatesDir.
     * @param FilesystemInterface|null $filesystem Optional filesystem implementation (default: LocalFilesystem).
     */
    public function __construct(
        private readonly string $viewsRoot,
        private readonly string $namespace = 'App\\Components',
        private readonly string $templatesDir = __DIR__.'/templates',
        string $emoji = '🔥',
        private readonly ?string $customStubsDir = null,
        ?FilesystemInterface $filesystem = null,
    ) {
        if (! $this->nameTransformer->isValidNamespace($namespace)) {
            throw new InvalidNamespaceException($namespace);
        }

        // ComponentPaths validates the emoji, so both share one rule.
        $this->paths = new ComponentPaths(null, $emoji);
        
        // Initialize specialized classes
        $this->filesystem ??= new LocalFilesystem();
        $this->nameTransformer = new NameTransformer();
        $this->propertyValidator = new PropertyValidator();
        $this->pathGenerator = new PathGenerator($viewsRoot, $this->paths);
        $this->templateRenderer = new TemplateRenderer($this->filesystem, $templatesDir, $customStubsDir);
        
        // Initialize content strategies
        $this->contentStrategies = [
            'class' => new ClassContentStrategy($this->templateRenderer, $this->nameTransformer),
            'view' => new ViewContentStrategy($this->templateRenderer, $this->nameTransformer),
            'js' => new JsContentStrategy($this->templateRenderer, $this->nameTransformer),
            'css' => new CssContentStrategy($this->templateRenderer, $this->nameTransformer),
            'global-css' => new CssContentStrategy($this->templateRenderer, $this->nameTransformer),
            'test' => new TestContentStrategy($this->templateRenderer, $this->nameTransformer),
        ];
    }

    private readonly ComponentPaths $paths;
    private readonly FilesystemInterface $filesystem;
    private readonly NameTransformer $nameTransformer;
    private readonly PropertyValidator $propertyValidator;
    private readonly PathGenerator $pathGenerator;
    private readonly TemplateRenderer $templateRenderer;
    /** @var array<string, ContentGenerationStrategy> */
    private readonly array $contentStrategies;

    /**
     * Splits a component name into path segments.
     *
     * @return array<int, string>
     */
    public function segments(string $name): array
    {
        return $this->paths->segments($name);
    }

    /** Engine view name of the template ("...create.view", ".php" appended at render time). */
    public function viewRelative(string $name): string
    {
        return $this->pathGenerator->viewRelative($name);
    }

    /** Absolute path of the component template (".view.php" appended). */
    public function viewPath(string $name): string
    {
        return $this->pathGenerator->viewPath($name);
    }

    /** Class name for the last segment (kebab → PascalCase). */
    public function className(string $name): string
    {
        $segments = $this->nameTransformer->splitComponentName($name);
        $lastSegment = array_pop($segments) ?? $name;
        return $this->nameTransformer->toPascalCase($lastSegment);
    }

    /**
     * Full class namespace for a name below the configured root namespace.
     * "post.create" with root "App\Components" → "App\Components\Post".
     */
    public function classNamespace(string $name): string
    {
        $segments = $this->nameTransformer->splitComponentName($name);
        array_pop($segments);

        $sub = array_map(
            fn (string $segment): string => $this->nameTransformer->toPascalCase($segment),
            $segments
        );

        $ns = trim($this->namespace, '\\');
        if ($sub !== []) {
            $ns .= '\\'.implode('\\', $sub);
        }

        return $ns;
    }

    /** Absolute path of the component class, collocated in its own folder. */
    public function classPath(string $name): string
    {
        return $this->pathGenerator->classPath($name);
    }

    /**
     * Absolute path of an optional sidecar (js, css, global.css) sitting next
     * to the template in the component folder.
     */
    public function sidecarPath(string $name, string $suffix): string
    {
        return $this->pathGenerator->sidecarPath($name, $suffix);
    }

    /** Absolute path of the (optional) PHPUnit test, collocated with the component. */
    public function testPath(string $name): string
    {
        return $this->pathGenerator->testPath($name);
    }

    /**
     * Parses --props into a list of valid public property identifiers.
     *
     * @return array<int, string>
     */
    public function props(string $raw): array
    {
        return $this->propertyValidator->parseProperties($raw);
    }

    /**
     * Component class source.
     *
     * @param array<int, string> $props
     */
    public function classContent(string $name, array $props): string
    {
        return $this->contentStrategies['class']->generate($name, [
            'namespace' => $this->classNamespace($name),
            'view' => $this->viewRelative($name),
            'props' => $props,
            'type' => 'class',
        ]);
    }

    /**
     * Component template source. With declared props a two-field form is
     * scaffolded (title + content inputs via hot:model and a hot:click save
     * button); without props a plain placeholder root is generated.
     *
     * @param array<int, string> $props
     */
    public function viewContent(string $name, array $props): string
    {
        return $this->contentStrategies['view']->generate($name, [
            'props' => $props,
            'type' => 'view',
        ]);
    }

    /** Optional scoped JavaScript next to the component template. */
    public function jsContent(string $name): string
    {
        return $this->contentStrategies['js']->generate($name, [
            'view' => $this->viewRelative($name),
            'type' => 'js',
        ]);
    }

    /** Optional scoped stylesheet next to the component template. */
    public function cssContent(string $name): string
    {
        return $this->contentStrategies['css']->generate($name, [
            'global' => false,
            'type' => 'css',
        ]);
    }

    /** Optional global stylesheet (unscoped) next to the component template. */
    public function globalCssContent(string $name): string
    {
        return $this->contentStrategies['global-css']->generate($name, [
            'global' => true,
            'type' => 'global-css',
        ]);
    }

    /** Optional PHPUnit test source for the component. */
    public function testContent(string $name): string
    {
        return $this->contentStrategies['test']->generate($name, [
            'namespace' => $this->classNamespace($name),
            'type' => 'test',
        ]);
    }
}