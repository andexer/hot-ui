<?php

declare(strict_types=1);

namespace Components\Support;

use Components\Support\Exception\CompiledTemplateNotFoundException;
use Components\Support\Exception\TemplateNotFoundException;
use Components\Support\Exception\UnknownTemplateNamespaceException;
use Components\Ui;

/**
 * Minimal zero-dependency template renderer (replaces League Plates).
 *
 * Resolves "ns::template" (components) and plain names relative to the root
 * (layouts, partials, host pages), extracts the render data into scope and
 * renders the file with PHP's native include.
 *
 * The template runs as the body of an instance method, so $this is the
 * renderer: every $this->uiXxx(...) / $this->blocksXxx(...) call is forwarded
 * to the backing Ui instance through __call(). insert() and fetch() keep the
 * Plates include-parity for layouts and partials.
 */
final class TemplateRenderer
{
    /** @var array<string, string> namespace => absolute folder path */
    private readonly array $folders;

    /**
     * @param string                 $root     Directory containing views (layouts, partials, pages).
     * @param array<string, string>  $folders  Component namespaces: folder name => absolute path.
     * @param TemplateCompiler|null  $compiler Optional tag-syntax compiler; skips compilation when null.
     */
    public function __construct(
        private readonly string $root,
        array $folders,
        private readonly Ui $ui,
        private readonly ?TemplateCompiler $compiler = null,
    ) {
        $this->folders = array_map(static fn (string $p): string => rtrim($p, '/\\'), $folders);
    }

    /**
     * Renders a template and returns its output.
     *
     * The template file is compiled through the tag-syntax compiler (cached by
     * source hash); every component/layout/partial can therefore use the same
     * <ui:…>/<blocks:…> grammar as pages. $__ui is bound to THIS Ui instance
     * so nested tags resolve against the correct registry.
     *
     * @param string               $name "ns::template" (component) or a plain name
     *                                   relative to the root ("layouts/app", "partials/meta").
     * @param array<string, mixed> $data Variables extracted into the template scope.
     */
    public function render(string $name, array $data = []): string
    {
        $includePath = $this->compiler === null
            ? $this->resolve($name)
            : $this->compiler->compile($this->resolve($name));
        unset($name);

        extract($data, EXTR_OVERWRITE);
        unset($data);

        $__ui = $this->ui;

        ob_start();
        try {
            include $includePath;

            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }

    /**
     * Renders a pre-compiled (already existing) PHP source file.
     *
     * Used by Ui::view() to execute compiled pages while keeping the same
     * environment as regular templates: data is extracted into scope and
     * $this is the renderer, so $this->uiXxx(...), ui(...) and the helpers
     * all work inside the page.
     *
     * @param string               $path Absolute path to the compiled PHP file.
     * @param array<string, mixed> $data Variables extracted into the template scope.
     */
    public function renderPath(string $path, array $data = []): string
    {
        $real = realpath($path);
        if ($real === false || ! is_file($real)) {
            throw new CompiledTemplateNotFoundException($path);
        }
        unset($path);

        extract($data, EXTR_OVERWRITE);
        unset($data);

        // Compiled pages reference the module via $__ui; bind it to THIS
        // instance so tests/dedicated apps resolve against the right registry.
        $__ui = $this->ui;

        ob_start();
        try {
            include $real;

            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }

    /**
     * Renders a pre-compiled source file with the template scope bound to a
     * given object (the Hotfire component), so `$this` inside the view refers
     * to the component while $__ui still points at the backing Ui instance.
     *
     * @param string               $path    Absolute path to the compiled PHP file.
     * @param object               $binding Object to expose as `$this` in the view.
     * @param array<string, mixed> $data    Variables extracted into the template scope.
     */
    public function renderPathBound(string $path, object $binding, array $data = []): string
    {
        $real = realpath($path);
        if ($real === false || ! is_file($real)) {
            throw new CompiledTemplateNotFoundException($path);
        }
        unset($path);

        $__ui = $this->ui;

        $render = function (string $__file, array $__data) use ($__ui): string {
            extract($__data, EXTR_OVERWRITE);
            unset($__data);

            ob_start();
            try {
                include $__file;

                return (string) ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();

                throw $exception;
            }
        };

        return $render->call($binding, $real, $data);
    }

    /**
     * Echoes a rendered template. Plates insert() parity for partial include.
     *
     * @param array<string, mixed> $data
     */
    public function insert(string $name, array $data = []): void
    {
        echo $this->render($name, $data);
    }

    /**
     * Returns a rendered template. Plates fetch() parity for partial include.
     *
     * @param array<string, mixed> $data
     */
    public function fetch(string $name, array $data = []): string
    {
        return $this->render($name, $data);
    }

    /**
     * Forwards every component method ($this->uiCard, $this->blocksNavMain…)
     * to the backing Ui instance, keeping the $this->uiXxx(...) call style.
     *
     * @param list<mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->ui->{$method}(...$arguments);
    }

    /**
     * Turns a template reference into an existing file path, refusing anything
     * that escapes its origin folder.
     */
    private function resolve(string $name): string
    {
        $separator = strpos($name, '::');
        if ($separator !== false) {
            $ns = substr($name, 0, $separator);
            $base = $this->folders[$ns] ?? null;
            if ($base === null) {
                throw new UnknownTemplateNamespaceException($ns, $name);
            }
            $relative = substr($name, $separator + 2);
        } else {
            $base = $this->root;
            $relative = $name;
        }

        $path = $base.'/'.trim($relative, '/\\');
        if (! str_ends_with($path, '.php')) {
            $path .= '.php';
        }

        $realBase = realpath($base);
        $realPath = realpath($path);
        if ($realPath === false || $realBase === false || ! str_starts_with($realPath, $realBase.DIRECTORY_SEPARATOR)) {
            throw TemplateNotFoundException::forTemplate($name, $path);
        }

        return $realPath;
    }
}