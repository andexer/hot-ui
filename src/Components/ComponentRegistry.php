<?php

declare(strict_types=1);

namespace Components;

use Components\Exception\ComponentNotFoundException;
use Components\Exception\MissingDirectoryException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Scans component view directories and resolves component names to renderer
 * template references.
 *
 * Every component is reachable by its folder-qualified lowerCamel name
 * (ui::card-header -> "uiCard") and, when unambiguous, by its bare alias
 * ("card", case-insensitive). Dotted names ("ui.card") are always accepted.
 */
final class ComponentRegistry
{
    /** @var array<string, array{ns: string, template: string}> qualified name => entry */
    private array $entries = [];

    /** @var array<string, list<string>> lowercase bare name => qualified names */
    private array $bare = [];

    /**
     * @param array<string, string> $namespaces namespace => absolute path
     */
    public function __construct(private readonly array $namespaces)
    {
        foreach ($this->namespaces as $ns => $path) {
            $this->scan($ns, $path);
        }
    }

    /**
     * Resolves a component name into its namespace and template reference.
     *
     * Accepted shapes, all pointing to the same file:
     *   'card-content' · 'Card-Content' · 'cardcontent' · 'ui.card-content'
     *   'uiCardContent' · 'ui::card-content'
     *
     * @return array{ns: string, template: string}
     *
     * @throws ComponentNotFoundException when the name is unknown or ambiguous.
     */
    public function resolve(string $name): array
    {
        if (isset($this->entries[$name])) {
            return $this->entries[$name];
        }

        // Namespaced forms: "ui.card-header" / "ui::card-header".
        $namespaced = str_replace('::', '.', $name);
        if (str_contains($namespaced, '.')) {
            [$ns, $bare] = explode('.', $namespaced, 2);

            foreach ($this->qualifiedCandidates($ns, $bare) as $qualified) {
                if (isset($this->entries[$qualified])) {
                    return $this->entries[$qualified];
                }
            }

            throw new ComponentNotFoundException($name, $this->suggestions());
        }

        // Bare forms, case/dash insensitive: "card", "card-header", "CARD_CONTENT".
        foreach ($this->bareCandidates($name) as $key) {
            $candidates = $this->bare[$key] ?? [];
            if (count($candidates) === 1) {
                return $this->entries[$candidates[0]];
            }
            if (count($candidates) > 1) {
                throw new ComponentNotFoundException($name, $candidates);
            }
        }

        throw new ComponentNotFoundException($name, $this->suggestions());
    }

    /** Checks whether a component name resolves. */
    public function has(string $name): bool
    {
        try {
            $this->resolve(name: $name);

            return true;
        } catch (ComponentNotFoundException) {
            return false;
        }
    }

    /** @return list<string> All registered qualified names. */
    public function names(): array
    {
        return array_keys($this->entries);
    }

    /** @return array<string, array{ns: string, template: string}> */
    public function entries(): array
    {
        return $this->entries;
    }

    /**
     * Registers every pure-PHP template under a namespace directory.
     *
     * @param string $ns   Namespace (folder) name.
     * @param string $path Absolute directory path.
     */
    private function scan(string $ns, string $path): void
    {
        if (! is_dir($path)) {
            throw new MissingDirectoryException($path, 'component directory');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $basename = $file->getFilename();
            if (! str_ends_with(strtolower($basename), '.php') || str_ends_with($basename, '.blade.php')) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen(rtrim($path, '/\\')) + 1);
            $relative = strtr($relative, '\\', '/');
            $relative = substr($relative, 0, -strlen('.php'));

            $qualified = lcfirst($this->camel($ns).$this->camel(str_replace('/', '_', $relative)));

            $this->entries[$qualified] = ['ns' => $ns, 'template' => $relative];
            $this->bare[strtolower($this->camel($relative))][] = $qualified;
        }
    }

    /**
     * Converts kebab/snake/slash-separated segments into PascalCase.
     */
    private function camel(string $value): string
    {
        $parts = preg_split('/[-_\s]+/', $value) ?: [];

        return implode('', array_map(static fn (string $p): string => ucfirst($p), $parts));
    }

    /**
     * Qualified-name candidates for a namespaced request.
     *
     * @return list<string>
     */
    private function qualifiedCandidates(string $ns, string $bare): array
    {
        $qualified = lcfirst($this->camel($ns).$this->camel($bare));

        return [$qualified, lcfirst($this->camel($ns)).$bare, $ns.'.'.$bare];
    }

    /**
     * Bare lookup keys for a request: exact lowercase plus dash/square-stripped.
     *
     * @return list<string>
     */
    private function bareCandidates(string $name): array
    {
        $lower = strtolower($name);

        return [$lower, str_replace(['-', '_'], '', $lower)];
    }

    /**
     * Human suggestions in the canonical file form ("ui::card-header"), so
     * developers never need to consult camelCase spellings.
     *
     * @return list<string>
     */
    private function suggestions(): array
    {
        $out = [];
        foreach (array_slice($this->entries, 0, 10) as $entry) {
            $out[] = "{$entry['ns']}::{$entry['template']}";
        }

        return $out;
    }
}
