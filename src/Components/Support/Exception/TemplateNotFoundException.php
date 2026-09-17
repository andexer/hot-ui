<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * A template reference resolved to a path that does not exist, or that escapes
 * the folder it was resolved against (path traversal included).
 */
final class TemplateNotFoundException extends \RuntimeException implements SupportException
{
    private function __construct(
        private readonly string $template,
        private readonly string $path,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** A namespaced template reference (ns::name) or root-relative name. */
    public static function forTemplate(string $template, string $path): self
    {
        return new self($template, $path, sprintf(
            'Template [%s] not found (resolved to [%s]).',
            $template,
            $path,
        ));
    }

    /** A host page rendered by Ui::render(), which needs an explicit base path. */
    public static function forView(string $template, string $path): self
    {
        return new self($template, $path, sprintf(
            'View [%s] not found (resolved to [%s]). Pass the base path when the page lives elsewhere.',
            $template,
            $path,
        ));
    }

    /** The reference as written by the caller. */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /** The absolute path it resolved to (nonexistent or outside the root). */
    public function getPath(): string
    {
        return $this->path;
    }
}
