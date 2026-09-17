<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Exception\InvalidComponentMarkerException;
use Components\Hotfire\Exception\InvalidComponentNameException;
use Components\Hotfire\Exception\MissingViewsRootException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Path/naming logic shared by the scaffold generator, the discovery command
 * and the CI4 boot autoloader, so class files, view files and sidecars always
 * resolve identically.
 *
 * A Hotfire component owns one folder below the configured view prefix — the
 * Livewire 4 "voltage" pattern, with 🔥 as Hot-UI's indicator — where its
 * class, template and sidecars cohabit:
 *
 *   post.create  →  components/post/🔥create/create.php
 *                   components/post/🔥create/create.view.php
 *                   components/post/🔥create/create.js
 *                   components/post/🔥create/create.css
 *                   components/post/🔥create/create.global.css
 *                   components/post/🔥create/create.test.php
 *
 * The 🔥 prefix is a visual affordance only (like Livewire 4's ⚡) and comes
 * from the constructor; the folder prefix is always Config::viewPrefix(), so
 * generation, discovery and autoloading can never disagree about where a
 * component lives.
 *
 * Configure the view prefix via config/hot-ui.php or Config::setShared().
 */
final class ComponentPaths
{
    public function __construct(
        private readonly ?string $viewsRoot = null,
        private readonly string $emoji = '🔥',
    ) {
        if ($this->emoji === '' || strpbrk($this->emoji, '/\\.') !== false) {
            throw new InvalidComponentMarkerException($this->emoji);
        }
    }

    /**
     * Splits a component name into path segments, validating safety.
     *
     * Accepts "post.create", "post/create", "Post/Create", "BottomLogout" or
     * "bottom-logout". Traversal ("..") and empty/garbage names are rejected.
     *
     * @return list<string>
     *
     * @throws \InvalidArgumentException On malformed or unsafe names.
     */
    public function segments(string $name): array
    {
        $name = trim($name, " \t\n\r\0\x0B./\\");
        if ($name === '' || preg_match('/\.\./', $name) || ! preg_match('#^[A-Za-z0-9_\-./\\\\]+$#', $name)) {
            throw new InvalidComponentNameException($name, 'use dotted or slashed segments (post.create).');
        }

        $segments = preg_split('/[.\/\\\\]+/', $name) ?: [];
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidComponentNameException($name, 'empty or traversal segments are not allowed.');
            }
        }

        return array_values($segments);
    }

    /** kebab-cases a segment: Post → post, BottomLogout → bottom-logout. */
    public static function kebab(string $segment): string
    {
        $segment = str_replace(['_', ' '], '-', $segment);
        $segment = preg_replace('/(?<!^)[A-Z]/', '-$0', $segment) ?? $segment;

        return strtolower($segment);
    }

    /**
     * Pascal-cases a segment: create → Create, create-form → CreateForm.
     * Existing camel humps are preserved: BottomLogout → BottomLogout.
     */
    public static function pascal(string $segment): string
    {
        $out = '';
        foreach (preg_split('/[^A-Za-z0-9]+/', $segment) ?: [] as $word) {
            if ($word === '') {
                continue;
            }
            foreach (preg_split('/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $word) ?: [$word] as $part) {
                $out .= ucfirst(strtolower($part));
            }
        }

        return $out;
    }

    /** kebab name of the leaf segment (create.post → "post"). */
    public function leafKebab(string $name): string
    {
        $segments = $this->segments($name);

        return self::kebab(array_pop($segments));
    }

    /** kebab dirs for every segment except the leaf. */
    public function parentDirs(string $name): array
    {
        $segments = $this->segments($name);
        array_pop($segments);

        return array_map(self::kebab(...), $segments);
    }

    /**
     * Special folder owning every artifact of a component: the configured view
     * prefix, its kebab parent dirs and the emoji-named leaf
     * (components/hotfire/post/🔥create).
     */
    public function folder(string $name): string
    {
        $parts = array_values(array_filter(explode('/', $this->prefix()), 'strlen'));
        foreach ($this->parentDirs($name) as $dir) {
            $parts[] = $dir;
        }
        $parts[] = $this->emoji.$this->leafKebab($name);

        return implode('/', $parts);
    }

    /** Class file of a component, relative to the views root (collocated). */
    public function classRelative(string $name): string
    {
        return $this->folder($name).'/'.$this->leafKebab($name).'.php';
    }

    /** Engine view name of the template ("...create.view", ".php" appended at render time). */
    public function viewRelative(string $name): string
    {
        return $this->folder($name).'/'.$this->leafKebab($name).'.view';
    }

    /**
     * Absolute path of the configured view prefix — the folder holding every
     * emoji-marked component folder (the walk root of discover()).
     */
    public function hotfireRoot(): string
    {
        $prefix = $this->prefix();

        return rtrim((string) $this->viewsRoot, '/\\').($prefix === '' ? '' : '/'.$prefix);
    }

    /**
     * Every component below the hotfire root, with the artifacts each one owns.
     *
     * Filesystem-only (nothing is autoloaded or rendered) so the CLI can call it
     * without the app booted. Entries are sorted by name and always share one
     * shape: "class" and "view" are null when the artifact is missing, and
     * "sidecars" lists the component's other "<leaf>.<suffix>" files in
     * alphabetical order.
     *
     *   ['name' => 'post.create',
     *    'folder' => '/views/components/hotfire/post/🔥create',
     *    'class' => '/views/…/create.php',
     *    'view' => '/views/…/create.view.php',
     *    'sidecars' => ['/views/…/create.js']]
     *
     * @return list<array{name: string, folder: string, class: string|null, view: string|null, sidecars: list<string>}>
     *
     * @throws \InvalidArgumentException Without a views root there is nothing to walk.
     */
    public function discover(): array
    {
        if ($this->viewsRoot === null) {
            throw MissingViewsRootException::forDiscovery();
        }

        $root = strtr($this->hotfireRoot(), '\\', '/');
        if (! is_dir($root)) {
            return [];
        }

        $components = [];
        foreach ($this->componentDirs($root) as $dir) {
            $name = $this->nameForFolder($root, $dir);
            if ($name !== null) {
                $components[] = $this->artifacts($name, $dir);
            }
        }

        usort($components, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $components;
    }

    /**
     * Artifacts owned by one component folder, as discover() reports them.
     *
     * @return array{name: string, folder: string, class: string|null, view: string|null, sidecars: list<string>}
     */
    private function artifacts(string $name, string $dir): array
    {
        $leaf = $this->leafKebab($name);
        $class = $dir.'/'.$leaf.'.php';
        $view = $dir.'/'.$leaf.'.view.php';

        return [
            'name'     => $name,
            'folder'   => $dir,
            'class'    => is_file($class) ? $class : null,
            'view'     => is_file($view) ? $view : null,
            'sidecars' => $this->sidecarFiles($dir, $leaf, [$class, $view]),
        ];
    }

    /**
     * Component-owned files other than the class and the template: every
     * "<leaf>.<suffix>" entry (js, css, global.css, test.php, …) in
     * alphabetical order, so unrelated or stray files never masquerade as
     * sidecars.
     *
     * @param list<string> $excluded
     *
     * @return list<string>
     */
    private function sidecarFiles(string $dir, string $leaf, array $excluded): array
    {
        $files = [];
        foreach (glob($dir.'/'.$leaf.'.*') ?: [] as $file) {
            if (is_file($file) && ! in_array($file, $excluded, true)) {
                $files[] = $file;
            }
        }
        sort($files);

        return $files;
    }

    /**
     * Directories below $root whose leaf segment carries the emoji marker —
     * the component folders themselves, at any depth.
     *
     * @return list<string>
     */
    private function componentDirs(string $root): array
    {
        $entries = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD,
        );

        $dirs = [];
        /** @var SplFileInfo $entry */
        foreach ($entries as $entry) {
            if ($entry->isDir() && str_starts_with($entry->getBasename(), $this->emoji)) {
                $dirs[] = strtr($entry->getPathname(), '\\', '/');
            }
        }
        sort($dirs);

        return $dirs;
    }

    /**
     * Component name owning $dir: the un-emoji'd, kebab-cased leaf segment
     * dot-joined with its kebab parent dirs below the hotfire root. Null when
     * the name does not round-trip back to the exact same folder (hand-made,
     * non-kebab or irregular folders are skipped).
     */
    private function nameForFolder(string $root, string $dir): ?string
    {
        $leaf = basename($dir);
        if (! str_starts_with($leaf, $this->emoji)) {
            return null;
        }

        $parent = dirname($dir);
        $relative = trim(substr($parent, strlen(rtrim($root, '/\\'))), '/');
        $segments = $relative === '' ? [] : explode('/', $relative);
        $segments[] = self::kebab(substr($leaf, strlen($this->emoji)));

        if (in_array('', $segments, true)) {
            return null;
        }

        // Round-trip: parents must already be kebab-clean and the leaf must be
        // exactly emoji + kebab(rest), so the derived name rebuilds the very
        // same directory below the hotfire root.
        $expected = rtrim($root, '/\\');
        $last = count($segments) - 1;
        foreach ($segments as $index => $segment) {
            $expected .= '/'.($index === $last ? $this->emoji.$segment : self::kebab($segment));
        }

        return $expected === $dir ? implode('.', $segments) : null;
    }

    /** View prefix every component folder lives under (configured, trimmed). */
    private function prefix(): string
    {
        return trim(Config::shared()->viewPrefix(), '/');
    }
}
