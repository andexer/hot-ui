<?php

declare(strict_types=1);

namespace Components\Hotfire\Navigation;

/**
 * Trait for components that support Hotfire navigation.
 * 
 * Provides methods for:
 * - Prefetching links
 * - History management
 * - Scroll restoration
 * - Persistent elements
 */
trait NavigationSupport
{
    /**
     * History stack for navigation.
     * 
     * @var array<int, array{url: string, title: string|null, scroll: int}>
     */
    public array $historyStack = [];

    /**
     * Current history index.
     * 
     * @var int
     */
    public int $historyIndex = -1;

    /**
     * Scroll position for each URL.
     * 
     * @var array<string, int>
     */
    public array $scrollPositions = [];

    /**
     * Persistent elements that survive re-renders.
     * 
     * @var array<string, bool>
     */
    public array $persistentElements = [];

    /**
     * Navigate to a URL with prefetch.
     * 
     * @param string $url Target URL
     * @param string|null $title Page title
     * @param bool $prefetch Whether to prefetch the target
     * @return array{url: string, title: string|null, prefetch: bool}
     */
    public function navigate(string $url, ?string $title = null, bool $prefetch = true): array
    {
        // Save current scroll position
        $this->saveScrollPosition();

        // Add to history
        $this->historyStack = array_slice($this->historyStack, 0, $this->historyIndex + 1);
        $this->historyStack[] = [
            'url' => $url,
            'title' => $title,
            'scroll' => 0,
        ];
        $this->historyIndex = count($this->historyStack) - 1;

        return [
            'url' => $url,
            'title' => $title,
            'prefetch' => $prefetch,
        ];
    }

    /**
     * Go back in history.
     * 
     * @return array{url: string|null, title: string|null, scroll: int}|null
     */
    public function back(): ?array
    {
        if ($this->historyIndex <= 0) {
            return null;
        }

        $this->historyIndex--;
        return $this->historyStack[$this->historyIndex] ?? null;
    }

    /**
     * Go forward in history.
     * 
     * @return array{url: string|null, title: string|null, scroll: int}|null
     */
    public function forward(): ?array
    {
        if ($this->historyIndex >= count($this->historyStack) - 1) {
            return null;
        }

        $this->historyIndex++;
        return $this->historyStack[$this->historyIndex] ?? null;
    }

    /**
     * Mark an element as persistent.
     * 
     * @param string $elementId Element ID
     * @return void
     */
    public function persistElement(string $elementId): void
    {
        $this->persistentElements[$elementId] = true;
    }

    /**
     * Check if an element is persistent.
     * 
     * @param string $elementId Element ID
     * @return bool Whether the element is persistent
     */
    public function isPersistent(string $elementId): bool
    {
        return isset($this->persistentElements[$elementId]);
    }

    /**
     * Save current scroll position.
     * 
     * @return void
     */
    public function saveScrollPosition(): void
    {
        // This would typically be called by the JS driver
        // Server-side we just provide the structure
    }

    /**
     * Get scroll position for a URL.
     * 
     * @param string $url URL
     * @return int Scroll position
     */
    public function getScrollPosition(string $url): int
    {
        return $this->scrollPositions[$url] ?? 0;
    }

    /**
     * Set scroll position for a URL.
     * 
     * @param string $url URL
     * @param int $position Scroll position
     * @return void
     */
    public function setScrollPosition(string $url, int $position): void
    {
        $this->scrollPositions[$url] = $position;
    }

    /**
     * Clear history.
     * 
     * @return void
     */
    public function clearHistory(): void
    {
        $this->historyStack = [];
        $this->historyIndex = -1;
    }
}
