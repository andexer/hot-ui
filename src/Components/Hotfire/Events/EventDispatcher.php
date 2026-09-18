<?php

declare(strict_types=1);

namespace Components\Hotfire\Events;

/**
 * Event dispatcher for Hotfire components.
 * 
 * Supports:
 * - Server-side events between components
 * - Global listeners
 * - Directed listeners by component name/id
 * - Browser events
 */
final class EventDispatcher
{
    /**
     * Global event listeners.
     * 
     * @var array<string, array<int, callable>>
     */
    private static array $globalListeners = [];

    /**
     * Component-specific listeners.
     * 
     * @var array<string, array<string, array<int, callable>>>
     */
    private static array $componentListeners = [];

    /**
     * Dispatch an event globally.
     * 
     * @param string $event Event name
     * @param array<string, mixed> $payload Event data
     * @return void
     */
    public static function dispatch(string $event, array $payload = []): void
    {
        // Call global listeners
        foreach (self::$globalListeners[$event] ?? [] as $listener) {
            $listener($payload);
        }
    }

    /**
     * Dispatch an event to a specific component.
     * 
     * @param string $componentId Component ID
     * @param string $event Event name
     * @param array<string, mixed> $payload Event data
     * @return void
     */
    public static function dispatchTo(string $componentId, string $event, array $payload = []): void
    {
        // Call component-specific listeners
        foreach (self::$componentListeners[$componentId][$event] ?? [] as $listener) {
            $listener($payload);
        }
    }

    /**
     * Register a global event listener.
     * 
     * @param string $event Event name
     * @param callable $listener Listener callback
     * @return void
     */
    public static function listen(string $event, callable $listener): void
    {
        self::$globalListeners[$event][] = $listener;
    }

    /**
     * Register a component-specific event listener.
     * 
     * @param string $componentId Component ID
     * @param string $event Event name
     * @param callable $listener Listener callback
     * @return void
     */
    public static function listenTo(string $componentId, string $event, callable $listener): void
    {
        self::$componentListeners[$componentId][$event][] = $listener;
    }

    /**
     * Remove all listeners for an event.
     * 
     * @param string $event Event name
     * @return void
     */
    public static function forget(string $event): void
    {
        unset(self::$globalListeners[$event]);
    }

    /**
     * Remove all listeners for a component.
     * 
     * @param string $componentId Component ID
     * @return void
     */
    public static function forgetComponent(string $componentId): void
    {
        unset(self::$componentListeners[$componentId]);
    }

    /**
     * Clear all listeners.
     * 
     * @return void
     */
    public static function flush(): void
    {
        self::$globalListeners = [];
        self::$componentListeners = [];
    }
}
