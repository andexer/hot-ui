<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Pure path/naming logic shared by the scaffold generator and the CI4 boot
 * autoloader, so class files and view files always resolve identically.
 *
 * A Hotfire component owns one folder under the views root — the Livewire 4
 * "voltage" pattern, with 🔥 as Hot-UI's indicator — where its class, template
 * and sidecars cohabit:
 *
 *   post.create  →  components/hotfire/post/🔥create/create.php
 *                   components/hotfire/post/🔥create/create.view.php
 *                   components/hotfire/post/🔥create/create.js
 *                   components/hotfire/post/🔥create/create.css
 *                   components/hotfire/post/🔥create/create.global.css
 *                   components/hotfire/post/🔥create/create.test.php
 *
 * The 🔥 prefix is a visual affordance only (like Livewire 4's ⚡) and can be
 * changed via the emoji in the constructor or make:hotfire --emoji.
 */
final class ComponentPaths
{
    public function __construct(
        private readonly ?string $viewsRoot = null,
        private readonly string $emoji = '🔥',
    ) {
    }

    /**
     * Splits a component name into path segments, validating safety.
     *
     * Accepts "post.create", "post/create", "Post/Create", "BottomLogout" or
     * "bottom-loggout". Traversal ("..") and empty/garbage names are rejected.
     *
     * @return list<string>
     *
     * @throws \InvalidArgumentException On malformed or unsafe names.
     */
    public function segments(string $name): array
    {
        $name = trim($name, " \t\n\r\0\x0B./\\");
        if ($name === '' || preg_match('/\.\./', $name) || ! preg_match('#^[A-Za-z0-9_\-./\\\\]+$#', $name)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid Hotfire component name [%s]; use dotted or slashed segments (post.create).',
                $name,
            ));
        }

        $segments = preg_split('/[.\/\\\\]+/', $name) ?: [];
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid Hotfire component name [%s]; empty or traversal segments are not allowed.',
                    $name,
                ));
            }
        }

        return array_values($segments);
    }

    /** kebab-cases a segment: Post → post, BottomLogout → bottom-logout. */
    public function kebab(string $segment): string
    {
        $segment = str_replace(['_', ' '], '-', $segment);
        $segment = preg_replace('/(?<!^)[A-Z]/', '-$0', $segment) ?? $segment;

        return strtolower($segment);
    }

    /**
     * Pascal-cases a segment: create → Create, create-form → CreateForm.
     * Existing camel humps are preserved: BottomLogout → BottomLogout.
     */
    public function pascal(string $segment): string
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

        return $this->kebab(array_pop($segments));
    }

    /** kebab dirs for every segment except the leaf. */
    public function parentDirs(string $name): array
    {
        $segments = $this->segments($name);
        array_pop($segments);

        return array_map($this->kebab(...), $segments);
    }

    /**
     * Special folder owning every artifact of a component, view-prefix
     * included and always ending with the emoji-named leaf:
     * components/hotfire/post/🔥create.
     */
    public function folder(string $name): string
    {
        $parts = ['components', 'hotfire'];
        foreach ($this->parentDirs($name) as $dir) {
            $parts[] = $dir;
        }
        $parts[] = $this->emoji.$this->leafKebab($name);

        return implode('/', $parts);
    }

    /** Engine view name of the template ("...create.view", ".php" appended at render time). */
    public function viewRelative(string $name): string
    {
        return $this->folder($name).'/'.$this->leafKebab($name).'.view';
    }

    /**
     * Discovers every component folder under "<viewsRoot>/components/hotfire"
     * — the folders whose leaf segment carries the emoji indicator — and maps
     * the component name each one owns back to a relative "post.create" name.
     *
     * The returned list of stubs is deliberately render-free (no autoloading,
     * no class_exists) so it also works from the CLI without the app booted:
     * each entry is rebuildable with this same instance via folder(),
     * viewRelative() or className() on $name.
     *
     * @return list<array{name: string, folder: string, class: string|null, view: string|null, sidecars: list<string>}>|
     *              list<array{name: string, folder: string}>
     *              Keys "class"/"view"/"sidecars" are absolute paths and only
     *              present when $details is true; folders that hold a
     *              "<leaf>.php" class or a "<leaf>.view.php" template report
     *              them, any other "<leaf>.<suffix>" file counts as a sidecar
     *              (sorted, with sidecars owned by other folders never leaking
     *              in).
     */
    public function discover(bool $details = true): array
    {
        $root = $this->hotfireRoot();
        if (! is_dir($root)) {
            return [];
        }

        /** @var list<array{name: string, folder: string, class: string|null, view: string|null, sidecars: list<string>}> $out */
        $out = [];
        foreach ($this->componentDirs($root) as $dir) {
            $name = $this->nameForFolder($root, $dir);
            if ($name === null) {
                continue;
            }
            $entry = ['name' => $name, 'folder' => $dir];
            if ($details) {
                $leaf = $this->leafKebab($name);
                $entry += [
                    'class'    => is_file($dir.'/'.$leaf.'.php') ? $dir.'/'.$leaf.'.php' : null,
                    'view'     => is_file($dir.'/'.$leaf.'.view.php') ? $dir.'/'.$leaf.'.view.php' : null,
                    'sidecars' => [],
                ];
                foreach (scandir($dir) ?: [] as $file) {
                    if ($file === '.' || $file === '..' || $file === $leaf.'.php' || $file === $leaf.'.view.php' || ! is_file($dir.'/'.$file)) {
                        continue;
                    }
                    $entry['sidecars'][] = $dir.'/'.$file;
                }
                sort($entry['sidecars']);
            }
            $out[] = $entry;
        }

        usort($out, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $out;
    }

    /**
     * Absolute path of the configured hotfire view prefix — the folder whose
     * emoji-marked subdirectories own the components (end of discovery).
     */
    public function hotfireRoot(): string
    {
        $prefix = trim(Config::shared()->viewPrefix(), '/');

        return rtrim($this->viewsRoot, '/\\').($prefix === '' ? '' : '/'.$prefix);
    }

    /**
     * All directories anywhere under $root whose leaf segment starts with the
     * emoji indicator (checked non-recursively in every recursive level).
     *
     * @return list<string>
     */
    private function componentDirs(string $root): array
    {
        $dirs = [];
        $entries = @scandir($root) ?: [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $root.'/'.$entry;
            if (! is_dir($path)) {
                continue;
            }
            if (str_starts_with($entry, $this->emoji)) {
                $dirs[] = $path;
                continue;
            }
            foreach ($this->componentDirs($path) as $nested) {
                $dirs[] = $nested;
            }
        }

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
        $segments[] = $this->kebab(substr($leaf, strlen($this->emoji)));

        if (in_array('', $segments, true)) {
            return null;
        }

        // Round-trip: parents must already be kebab-clean and the leaf must
        // be exactly emoji + kebab(rest), so the derived name rebuilds the
        // very same directory below the hotfire root.
        $expected = rtrim($root, '/\\');
        $last = count($segments) - 1;
        foreach ($segments as $index => $segment) {
            $expected .= '/'.($index === $last ? $this->emoji.$segment : $this->kebab($segment));
        }

        return $expected === $dir ? implode('.', $segments) : null;
    }
}