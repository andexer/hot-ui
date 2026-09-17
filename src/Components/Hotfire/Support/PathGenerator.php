<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

use Components\Hotfire\ComponentPaths;

/**
 * Path generator for component file paths.
 * 
 * Single Responsibility: Generates all file paths for Hotfire components
 * including class paths, view paths, sidecar paths, and test paths.
 * 
 * This class follows the Single Responsibility Principle by focusing
 * exclusively on path generation logic.
 */
final readonly class PathGenerator
{
    public function __construct(
        private readonly string $viewsRoot,
        private readonly ComponentPaths $paths,
    ) {}

    /**
     * Gets the relative view name for a component.
     * 
     * @param string $name The component name
     * @return string The relative view name
     */
    public function viewRelative(string $name): string
    {
        return $this->paths->viewRelative($name);
    }

    /**
     * Gets the absolute path for a component template.
     * 
     * @param string $name The component name
     * @return string The absolute template path
     */
    public function viewPath(string $name): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->viewRelative($name).'.php';
    }

    /**
     * Gets the absolute path for a component class.
     * 
     * @param string $name The component name
     * @return string The absolute class path
     */
    public function classPath(string $name): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->paths->classRelative($name);
    }

    /**
     * Gets the absolute path for a component sidecar file.
     * 
     * @param string $name The component name
     * @param string $suffix The sidecar suffix (js, css, global.css)
     * @return string The absolute sidecar path
     */
    public function sidecarPath(string $name, string $suffix): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->paths->folder($name).'/'.$this->paths->leafKebab($name).'.'.ltrim($suffix, '.');
    }

    /**
     * Gets the absolute path for a component test file.
     * 
     * @param string $name The component name
     * @return string The absolute test path
     */
    public function testPath(string $name): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->paths->folder($name).'/'.$this->paths->leafKebab($name).'.test.php';
    }

    /**
     * Gets the component folder path.
     * 
     * @param string $name The component name
     * @return string The component folder path
     */
    public function folderPath(string $name): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->paths->folder($name);
    }
}
