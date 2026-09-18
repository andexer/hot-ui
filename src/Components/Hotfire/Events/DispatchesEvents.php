<?php

declare(strict_types=1);

namespace Components\Hotfire\Events;

/**
 * Trait for components that dispatch and listen to events.
 * 
 * Provides methods for:
 * - Dispatching events globally
 * - Dispatching events to specific components
 * - Listening to events
 * - Browser event handling
 */
trait DispatchesEvents
{
    /**
     * Component ID for event targeting.
     * 
     * @var string|null
     */
    public ?string $componentId = null;

    /**
     * Event listeners for this component.
     * 
     * @var array<string, array<int, callable>>
     */
    protected array $eventListeners = [];

    /**
     * Dispatch an event globally.
     * 
     * @param string $event Event name
     * @param array<string, mixed> $payload Event data
     * @return void
     */
    public function dispatch(string $event, array $payload = []): void
    {
        EventDispatcher::dispatch($event, $payload);
    }

    /**
     * Dispatch an event to a specific component.
     * 
     * @param string $componentId Target component ID
     * @param string $event Event name
     * @param array<string, mixed> $payload Event data
     * @return void
     */
    public function dispatchTo(string $componentId, string $event, array $payload = []): void
    {
        EventDispatcher::dispatchTo($componentId, $event, $payload);
    }

    /**
     * Listen to a global event.
     * 
     * @param string $event Event name
     * @param callable $listener Listener callback
     * @return void
     */
    public function listen(string $event, callable $listener): void
    {
        $this->eventListeners[$event][] = $listener;
        EventDispatcher::listen($event, $listener);
    }

    /**
     * Listen to an event from a specific component.
     * 
     * @param string $componentId Source component ID
     * @param string $event Event name
     * @param callable $listener Listener callback
     * @return void
     */
    public function listenTo(string $componentId, string $event, callable $listener): void
    {
        $this->eventListeners[$event][] = $listener;
        EventDispatcher::listenTo($componentId, $event, $listener);
    }

    /**
     * Handle a browser event.
     * 
     * @param string $event Browser event name (click, change, etc.)
     * @param array<string, mixed> $payload Event data
     * @return void
     */
    public function handleBrowserEvent(string $event, array $payload = []): void
    {
        $method = 'on'.ucfirst($event);
        if (method_exists($this, $method)) {
            $this->{$method}($payload);
        }
    }

    /**
     * Clean up event listeners.
     * 
     * @return void
     */
    public function cleanupListeners(): void
    {
        foreach ($this->eventListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                EventDispatcher::forgetListener($event, $listener);
            }
        }
        
        if ($this->componentId !== null) {
            EventDispatcher::forgetComponent($this->componentId);
        }
        
        $this->eventListeners = [];
    }
}
