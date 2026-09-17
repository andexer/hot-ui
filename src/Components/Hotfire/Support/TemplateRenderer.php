<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

use Components\Hotfire\Exception\StubTemplateNotFoundException;

/**
 * Template renderer for stub files.
 * 
 * Single Responsibility: Handles loading and processing of stub template files,
 * replacing placeholders with actual values.
 * 
 * This class follows the Single Responsibility Principle by focusing
 * exclusively on template rendering logic.
 */
final readonly class TemplateRenderer
{
    public function __construct(
        private readonly FilesystemInterface $filesystem,
        private readonly string $templatesDir,
        private readonly ?string $customStubsDir = null,
    ) {}

    /**
     * Renders a stub template by replacing placeholders with values.
     * 
     * @param string $file The stub file name
     * @param array<string, string> $tokens The placeholder values
     * @return string The rendered template
     * @throws StubTemplateNotFoundException If the stub file cannot be found
     */
    public function render(string $file, array $tokens): string
    {
        $content = $this->loadStub($file);
        return $this->replacePlaceholders($content, $tokens);
    }

    /**
     * Loads a stub file from the appropriate directory.
     * 
     * Resolution order:
     *   1. $customStubsDir (user-published via `hot-ui:stubs`) when available
     *   2. $templatesDir (built-in package stubs)
     * 
     * @param string $file The stub file name
     * @return string The stub content
     * @throws StubTemplateNotFoundException If the stub file cannot be found
     */
    private function loadStub(string $file): string
    {
        $path = $this->resolveStubPath($file);

        if (! $this->filesystem->isReadable($path)) {
            throw StubTemplateNotFoundException::notFound($file);
        }

        return $this->filesystem->read($path);
    }

    /**
     * Resolves the path to a stub file.
     * 
     * @param string $file The stub file name
     * @return string The resolved path
     */
    private function resolveStubPath(string $file): string
    {
        // Prefer user-published stubs if available
        if ($this->customStubsDir !== null) {
            $customPath = rtrim($this->customStubsDir, '/\\').'/'.$file;
            if ($this->filesystem->exists($customPath) && $this->filesystem->isReadable($customPath)) {
                return $customPath;
            }
        }

        return rtrim($this->templatesDir, '/\\').'/'.$file;
    }

    /**
     * Replaces placeholders in the template with actual values.
     * 
     * @param string $content The template content
     * @param array<string, string> $tokens The placeholder values
     * @return string The rendered content
     */
    private function replacePlaceholders(string $content, array $tokens): string
    {
        $map = [];
        foreach ($tokens as $key => $value) {
            $map['{{'.$key.'}}'] = $value;
        }

        $result = strtr($content, $map);
        return $result === '' ? '' : rtrim($result, "\n")."\n";
    }
}
