<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Hotfire configuration.
 *
 * Every value that used to be baked into the engine lives here so hosts can
 * override the endpoint, the component view prefix, the snapshot key source,
 * the directive map and the reserved method list without patching the package.
 *
 * A process-wide instance is available through Config::shared(); create your
 * own and install it with Config::setShared() during bootstrap if you need
 * different defaults.
 */
final class Config
{
    /** Directives rewritten by HtmlTransform: hot:<name> => data-hot-<attr>. */
    public const DEFAULT_DIRECTIVES = [
        'click' => 'click',
        'model' => 'model',
        'poll' => 'poll',
        'change' => 'change',
        'key' => 'key',
    ];

    /** Framework methods that can never be invoked as an action. */
    public const DEFAULT_RESERVED = [
        'mount',
        'booted',
        'updated',
        'hydrate',
        'state',
        'notifyUpdated',
        'viewPath',
    ];

    private static ?self $shared = null;

    /**
     * @param array<string, string> $directives Directive name => data attribute suffix.
     * @param list<string>          $reserved   Framework methods excluded from actions.
     */
    public function __construct(
        private string $endpoint = 'hot-ui/update',
        private string $viewPrefix = 'components',
        private ?string $snapshotKey = null,
        private string $snapshotKeyEnv = 'HOTUI_SNAPSHOT_KEY',
        private array $directives = self::DEFAULT_DIRECTIVES,
        private array $reserved = self::DEFAULT_RESERVED,
    ) {
    }

    /** Process-wide configuration used when none is passed explicitly. */
    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    /** Installs (or resets with null) the shared configuration. */
    public static function setShared(?self $config): void
    {
        self::$shared = $config;
    }

    /** Endpoint the JS driver posts to (relative or absolute URL). */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /** View directory prefix for components that do not declare one. */
    public function viewPrefix(): string
    {
        return $this->viewPrefix;
    }

    /**
     * Signing key: the explicit one, else the configured environment value.
     */
    public function snapshotKey(): ?string
    {
        if ($this->snapshotKey !== null && $this->snapshotKey !== '') {
            return $this->snapshotKey;
        }

        $value = getenv($this->snapshotKeyEnv);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /** @return array<string, string> */
    public function directives(): array
    {
        return $this->directives;
    }

    /** Resolves the data attribute name for a given directive, if known. */
    public function directive(string $name): ?string
    {
        return $this->directives[$name] ?? null;
    }

    /** @return list<string> */
    public function reserved(): array
    {
        return $this->reserved;
    }

    public function isReserved(string $method): bool
    {
        return in_array($method, $this->reserved, true);
    }
}