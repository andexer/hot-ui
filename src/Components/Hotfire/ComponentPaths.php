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
    public function __construct(private readonly string $emoji = '🔥')
    {
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
}