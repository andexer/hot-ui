<?php

declare(strict_types=1);

namespace Components\Config;

/**
 * Hot-UI Configuration File
 * 
 * This configuration file controls the behavior of Hot-UI components
 * in your CodeIgniter 4 application. Similar to Livewire's config,
 * it allows you to customize component locations, namespaces, and
 * generation behavior.
 * 
 * To publish this file to your application, run:
 *   php spark hot-ui:config --publish
 * 
 * @version 0.16.0
 */
class HotUI
{
    /**
     * Component Locations
     * 
     * Root directories that'll be used to resolve view-based components.
     * The make command will use the first directory in this array to add
     * new component files to.
     * 
     * Example:
     *   'component_locations' => [
     *       APPPATH.'Views/components',
     *       APPPATH.'Views/layouts',
     *   ],
     */
    public array $componentLocations = [
        APPPATH.'Views/components',
    ];

    /**
     * Component Namespaces
     * 
     * Default namespaces used to resolve view-based components.
     * These folders will also be referenced when creating new components.
     * 
     * Example:
     *   'component_namespaces' => [
     *       'layouts' => APPPATH.'Views/layouts',
     *       'pages' => APPPATH.'Views/pages',
     *   ],
     */
    public array $componentNamespaces = [];

    /**
     * Component Layout
     * 
     * The view that will be used as the layout when rendering a component
     * as an entire page. The component content will render into $slot.
     * 
     * Example: 'layouts::app'
     */
    public ?string $componentLayout = null;

    /**
     * Lazy Loading Placeholder
     * 
     * Every component can have a custom placeholder or you can define
     * the default placeholder view for all components below.
     * 
     * Example: 'placeholders::skeleton'
     */
    public ?string $componentPlaceholder = null;

    /**
     * Make Command Configuration
     * 
     * Default configuration for the spark make command.
     * You can configure whether to use emoji prefix and default
     * file generation options.
     */
    public array $makeCommand = [
        /**
         * Emoji prefix for component folders.
         * Set to false to disable emoji prefixes.
         */
        'emoji' => true,
        
        /**
         * Default emoji to use when emoji is enabled.
         * Common options: '🔥', '⚡', '✨', '🎨'
         */
        'default_emoji' => '🔥',
        
        /**
         * Default file generation options.
         * These will be used unless explicitly overridden via command flags.
         */
        'with' => [
            'js' => false,
            'css' => false,
            'global_css' => false,
            'test' => false,
        ],
    ];

    /**
     * Class Namespace
     * 
     * Root class namespace for Hotfire component classes.
     * This value will change where component auto-discovery finds components.
     * It's also referenced by the file creation commands.
     * 
     * Example: 'App\\Components'
     */
    public string $classNamespace = 'App\\Components';

    /**
     * Class Path
     * 
     * Path where Hotfire component class files are created when running
     * creation commands like `php spark make:hotfire`.
     * 
     * Example: APPPATH.'Components'
     */
    public string $classPath = APPPATH.'Components';

    /**
     * View Path
     * 
     * Path where Hotfire component templates are stored when running
     * file creation commands like `php spark make:hotfire`.
     * It is also used if you choose to omit a component's $view property.
     * 
     * Example: APPPATH.'Views/components'
     */
    public string $viewPath = APPPATH.'Views/components';

    /**
     * Stubs Directory
     * 
     * Directory where custom stub templates are stored.
     * Published stubs will take precedence over package defaults.
     * 
     * Example: APPPATH.'Components/stubs/hot-ui'
     */
    public ?string $stubsDirectory = null;

    /**
     * Snapshot Key
     * 
     * Secret key used to sign component snapshots for security.
     * Generate a secure key via: php spark hot-ui:install
     * 
     * Required for Hotfire component security.
     */
    public ?string $snapshotKey = null;

    /**
     * Auto-inject Frontend Assets
     * 
     * By default, Hot-UI automatically injects its JavaScript and CSS
     * into pages containing Hotfire components. By disabling this,
     * you need to manually include the assets.
     */
    public bool $injectAssets = true;

    /**
     * Asset Version
     * 
     * Version string for cache busting of frontend assets.
     * Change this value when you update assets to force browser refresh.
     */
    public string $assetVersion = '0.16.0';

    /**
     * Development Mode
     * 
     * When enabled, provides additional debugging information
     * and disables certain optimizations for easier development.
     */
    public bool $developmentMode = false;

    /**
     * ----------------------------------------------------------------
     * Validation Rules
     * ----------------------------------------------------------------
     * 
     * Security and validation rules for component properties and snapshots.
     */

    /**
     * Maximum payload size in bytes (default: 1MB)
     */
    public int $maxPayloadSize = 1024 * 1024;

    /**
     * Maximum nesting depth for dot-notation property paths
     */
    public int $maxNestingDepth = 10;

    /**
     * Maximum method calls per request
     */
    public int $maxCalls = 50;

    /**
     * Maximum components per batch request
     */
    public int $maxComponents = 200;

    /**
     * ----------------------------------------------------------------
     * CSP Configuration
     * ----------------------------------------------------------------
     * 
     * Content Security Policy settings for applications using strict CSP.
     */

    /**
     * Use CSP-safe version of Alpine.js bundle
     */
    public bool $cspSafe = false;

    /**
     * Allowed CSP sources for inline scripts
     */
    public array $cspScriptSrc = [];

    /**
     * Allowed CSP sources for inline styles
     */
    public array $cspStyleSrc = [];
}
