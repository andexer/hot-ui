<?php

declare(strict_types=1);

namespace Components;

use Closure;
use Components\Exception\ComponentNotFoundException;
use Components\Exception\MissingComponentNamespaceException;
use Components\Exception\MissingDirectoryException;
use Components\Support\Exception\TemplateNotFoundException;
use Components\Exception\UiPipelineException;
use Components\Exception\UnsupportedPositionalArgumentException;
use Components\Support\RenderContext;
use Components\Support\Slot;
use Components\Support\TemplateCompiler;
use Components\Support\TemplateRenderer;
use Stringable;

/**
 * Entry point of the Hot-UI component library.
 *
 * Two usage styles hit the exact same engine:
 *
 *   $ui = Ui::new(view_path: __DIR__.'/views');
 *   echo $ui->card(['class' => 'max-w-sm'], 'Sign in');
 *
 *   <?= $this->uiCard(['class' => 'max-w-sm'], 'Sign in') ?>
 */
final class Ui
{
    private static ?self $shared = null;

    /** @var list<RenderContext> Active render stack, outermost first. */
    private static array $stack = [];

    private readonly TemplateRenderer $renderer;
    private readonly ComponentRegistry $registry;

    /** @var list<array{name: string, entry: array{ns: string, template: string}, props: array<string, mixed>, buckets: array<string, string>, current: string}> */
    private array $stream = [];

    private readonly string $viewsRoot;

    private readonly string $cacheDir;

    private readonly TemplateCompiler $compiler;

    /**
     * @param string|null $viewPath Directory containing the components folder.
     */
    public function __construct(?string $viewPath = null)
    {
        $root = rtrim($viewPath ?? dirname(__DIR__, 2).'/views', '/');
        $componentsDir = $root.'/components';

        if (! is_dir($componentsDir)) {
            throw new MissingDirectoryException($componentsDir);
        }

        $namespaces = [];
        foreach ((array) scandir($componentsDir) as $entry) {
            if ($entry !== '.' && $entry !== '..' && $entry !== false && is_dir($componentsDir.'/'.$entry)) {
                $namespaces[$entry] = $componentsDir.'/'.$entry;
            }
        }
        if ($namespaces === []) {
            throw new MissingComponentNamespaceException($componentsDir);
        }

        $this->viewsRoot = $root;
        $this->cacheDir = defined('WRITEPATH') && WRITEPATH !== ''
            ? rtrim((string) WRITEPATH, '/\\').'/cache/hotui'
            : rtrim(sys_get_temp_dir(), '/\\').'/hotui-compiled';
        $this->compiler = new TemplateCompiler($this->cacheDir);
        $this->registry = new ComponentRegistry($namespaces);
        $this->renderer = new TemplateRenderer($root, $namespaces, $this, $this->compiler);
    }

    /**
     * Returns the absolute views root of this instance (contains components/,
     * layouts/, partials/ and any host pages).
     */
    public function viewsPath(): string
    {
        return $this->viewsRoot;
    }

    /**
     * Renders a host page that uses the <ui:…> tag syntax.
     *
     * The source file is located under $basePath (default: this instance's
     * views root), compiled once per change and cached under the app cache
     * directory (WRITEPATH/cache/hotui when running inside CI4, otherwise
     * sys_get_temp_dir()/hotui-compiled). The page runs in the same
     * environment as a regular template: data is extracted into scope, $this
     * is the renderer and both ui(...) and $this->uiXxx(...) work.
     *
     * @param string               $template File name relative to $basePath ("pages/home").
     * @param array<string, mixed> $data     Variables extracted into the page scope.
     * @param string|null          $basePath Directory that contains the page.
     */
    public function view(string $template, array $data = [], ?string $basePath = null): string
    {
        $source = $this->locateSource($template, $basePath ?? $this->viewsRoot);

        self::$stack[] = new RenderContext([], new Slot());
        try {
            return $this->renderer->renderPath($this->compiledPath($source), $data);
        } finally {
            array_pop(self::$stack);
        }
    }

    /**
     * Renders a template with the scope bound to a given object (used by the
     * Hotfire engine so `$this` in a component view is the component).
     *
     * @param string               $template File name relative to $basePath.
     * @param object               $binding  Object exposed as `$this` in the view.
     * @param array<string, mixed> $data     Variables extracted into the scope.
     * @param string|null          $basePath Directory that contains the template.
     */
    public function viewBound(string $template, object $binding, array $data = [], ?string $basePath = null): string
    {
        $source = $this->locateSource($template, $basePath ?? $this->viewsRoot);

        self::$stack[] = new RenderContext([], new Slot());
        try {
            return $this->renderer->renderPathBound($this->compiledPath($source), $binding, $data);
        } finally {
            array_pop(self::$stack);
        }
    }

    /**
     * Creates a dedicated instance.
     *
     * @param string|null $viewPath Overrides the default project views path.
     */
    public static function new(?string $viewPath = null): self
    {
        return new self($viewPath);
    }

    /**
     * Process-wide instance backing the ui() helper.
     */
    public static function shared(?string $viewPath = null): self
    {
        return self::$shared ??= new self(
            $viewPath !== null
                ? $viewPath
                : (getenv('COMPONENTS_VIEW_PATH') !== false ? getenv('COMPONENTS_VIEW_PATH') : null),
        );
    }

    /**
     * Renders a full page template by plain name ("demo") or namespaced ref.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        self::$stack[] = new RenderContext([], new Slot());
        try {
            return $this->renderer->render($template, $data);
        } finally {
            array_pop(self::$stack);
        }
    }

    /**
     * Dispatches any registered component as a method call.
     *
     * @param list<mixed> $arguments
     */
    public function __call(string $method, array $arguments): string
    {
        return $this->renderComponent($method, $arguments);
    }

    public function has(string $name): bool
    {
        return $this->registry->has($name);
    }

    /** @return list<string> */
    public function componentNames(): array
    {
        return $this->registry->names();
    }

    /**
     * Registry entries keyed by qualified component name.
     *
     * @return array<string, array{ns: string, template: string}>
     */
    public function registryEntries(): array
    {
        return $this->registry->entries();
    }

    /**
     * Renders a component by name using the standard call convention.
     *
     * @param list<mixed> $arguments
     */
    public function renderComponent(string $name, array $arguments): string
    {
        $entry = $this->registry->resolve($name);

        return $this->renderEntry($entry, $arguments);
    }

    /**
     * Renders a pre-resolved registry entry.
     *
     * @param array{ns: string, template: string} $entry
     * @param list<mixed>                         $arguments
     */
    public function renderEntry(array $entry, array $arguments): string
    {
        [$props, $slotParts, $namedSlots] = self::parseArguments($arguments);

        return $this->renderResolved($entry, $props, $slotParts, $namedSlots);
    }

    /**
     * Starts streaming a component: everything echoed from here until the
     * matching close() becomes its slots. Accepts the component name in ANY
     * spelling — ideally the file name itself ("card-content").
     *
     * @param array<string, mixed> $props
     */
    public function open(string $component, array $props = []): void
    {
        $this->stream[] = [
            'name' => $component,
            'entry' => $this->registry->resolve($component),
            'props' => $props,
            'buckets' => ['' => ''],
            'current' => '',
        ];
        ob_start();
    }

    /**
     * Switches the capture target of the OPEN component. Everything echoed
     * after into('leading') lands on that named slot; calling into() with no
     * arguments returns capture to the default slot.
     *
     * @param string|null $slot Slot name, or null for the default slot.
     */
    public function into(?string $slot = null): void
    {
        if ($this->stream === []) {
            throw UiPipelineException::withoutOpen('into');
        }

        $index = count($this->stream) - 1;
        $buffer = (string) ob_get_clean();

        $current = $this->stream[$index]['current'];
        $this->stream[$index]['buckets'][$current] = ($this->stream[$index]['buckets'][$current] ?? '').$buffer;
        $target = $slot ?? '';
        $this->stream[$index]['buckets'][$target] ??= '';
        $this->stream[$index]['current'] = $target;

        ob_start();
    }

    /**
     * Ends the innermost open component and returns its rendered HTML.
     */
    public function close(): string
    {
        if ($this->stream === []) {
            throw UiPipelineException::withoutOpen('close');
        }

        /** @var array{name: string, entry: array{ns: string, template: string}, props: array<string, mixed>, buckets: array<string, string>, current: string} $frame */
        $frame = array_pop($this->stream);
        $frame['buckets'][$frame['current']] .= (string) ob_get_clean();

        $default = new Slot($frame['buckets'][''] ?? '');
        $named = [];
        foreach ($frame['buckets'] as $name => $content) {
            if ($name !== '') {
                $named[$name] = new Slot($content);
            }
        }

        return $this->renderResolved($frame['entry'], $frame['props'], [$default], $named);
    }

    /**
     * Aborts every open frame (exception recovery); buffers are discarded and
     * the output-buffer level is restored to what it was before any open().
     */
    public function discard(): void
    {
        while ($this->stream !== []) {
            array_pop($this->stream);
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
    }

    /**
     * Publishes values visible to descendant components (the aware channel).
     *
     * @param array<string, mixed> $values
     */
    public static function publishShared(array $values): void
    {
        if (self::$stack === []) {
            throw UiPipelineException::outsideRender('share');
        }

        self::$stack[count(self::$stack) - 1]->shared += $values;
    }

    /**
     * Merged shared() values from all active ancestor contexts.
     *
     * @return array<string, mixed>
     */
    public static function inheritedShared(): array
    {
        $out = [];
        foreach (self::$stack as $context) {
            $out += $context->shared;
        }

        return $out;
    }

    /** Returns the innermost active render context, or null outside renders. */
    public static function currentContext(): ?RenderContext
    {
        return self::$stack === [] ? null : self::$stack[count(self::$stack) - 1];
    }

    /**
     * Locates a view file under a base directory, refusing paths that escape it.
     *
     * @return string Absolute, real path to the source template file.
     */
    private function locateSource(string $template, string $basePath): string
    {
        $base = rtrim($basePath, '/\\');
        $path = $base.'/'.trim($template, '/\\');
        if (! str_ends_with($path, '.php')) {
            $path .= '.php';
        }

        $realBase = realpath($base);
        $realPath = realpath($path);
        if ($realPath === false || $realBase === false || ! str_starts_with($realPath, $realBase.DIRECTORY_SEPARATOR)) {
            throw TemplateNotFoundException::forView($template, $path);
        }

        return $realPath;
    }

    /**
     * Compiles a view file to cached PHP, reusing it while the source is unchanged.
     *
     * @param string $source Absolute source path (already realpath()ed).
     */
    private function compiledPath(string $source): string
    {
        return $this->compiler->compile($source);
    }

    /**
     * Renders a resolved entry from pre-built slot parts (shared by both call
     * styles: argument-based and open/close streaming).
     *
     * @param array{ns: string, template: string}                            $entry
     * @param array<string, mixed>                                           $props
     * @param list<Closure|Slot|string|Stringable|null>                      $slotParts
     * @param array<string, Closure|Slot|string|Stringable|null>|array<string, Slot> $namedSlots
     */
    private function renderResolved(
        array $entry,
        array $props,
        array $slotParts,
        array $namedSlots,
    ): string {
        $context = new RenderContext(
            props: $props,
            slot: new Slot(...$slotParts),
            slots: array_map(Slot::make(...), $namedSlots),
            shared: self::inheritedShared(),
        );

        self::$stack[] = $context;
        try {
            return $this->renderer->render("{$entry['ns']}::{$entry['template']}", ['__ctx' => $context]);
        } finally {
            array_pop(self::$stack);
        }
    }

    /**
     * Splits positional/named arguments into props, slot parts and named slots.
     *
     * @param list<mixed> $arguments
     * @return array{0: array<string, mixed>, 1: list<Closure|Slot|string|Stringable|null>, 2: array<string, mixed>}
     */
    private static function parseArguments(array $arguments): array
    {
        $props = [];
        $slotParts = [];
        $named = [];

        foreach ($arguments as $key => $value) {
            if (is_string($key)) {
                $named[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $props = array_merge($props, $value);
                continue;
            }
            if ($value === null || is_string($value) || $value instanceof Closure || $value instanceof Stringable) {
                $slotParts[] = $value;
                continue;
            }

            throw new UnsupportedPositionalArgumentException($key, get_debug_type($value));
        }

        return [$props, $slotParts, $named];
    }
}
