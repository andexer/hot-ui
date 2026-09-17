<?php

declare(strict_types=1);

namespace Components\Hotfire;

/**
 * Base class for server-side Hotfire components.
 *
 * Public properties are the component state; public methods are the callable
 * actions referenced from views with hot:* attributes.
 *
 *   final class Counter extends Component
 *   {
 *       public int $count = 0;
 *
 *       public function increment(): void
 *       {
 *           $this->count++;
 *       }
 *   }
 *
 * Template (rendered by the Hotfire engine):
 *
 *   <button hot:click="increment">+1</button>
 *   <span><?= $component->count ?></span>
 *
 * Decorate it in a CodeIgniter controller with Ci4::hotfire($component).
 */
abstract class Component
{
    /** Template, relative to the configured views path. */
    protected string $view = '';

    /**
     * Called once with fresh state when the component instance is created
     * (both the initial render and every round-trip).
     */
    public function mount(): void
    {
    }

    /** Runs when fresh state is applied during hydration. */
    public function booted(array $state): void
    {
    }

    /** Runs whenever a public property is updated through an action. */
    public function updated(string $property): void
    {
    }

    /**
     * Template path for this component. Uses $view when declared, otherwise
     * derives it from the class name in kebab-case under the configured view
     * prefix (Config::viewPrefix()).
     */
    public function viewPath(): string
    {
        if ($this->view !== '') {
            return $this->view;
        }

        $short = ComponentPaths::kebab((new \ReflectionClass($this))->getShortName());
        $prefix = trim(Config::shared()->viewPrefix(), '/');

        return $prefix === '' ? $short : $prefix.'/'.$short;
    }

    /**
     * Public properties forming the component state.
     *
     * @return array<string, mixed>
     */
    public function state(): array
    {
        $state = [];
        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $name = $property->getName();
            if (str_starts_with($name, '__')) {
                continue;
            }
            $state[$name] = $this->{$name};
        }

        return $state;
    }

    /**
     * Applies serialized state to public properties (hydration).
     *
     * Only DECLARED public non-static properties are ever touched: unknown,
     * protected or dynamic member names are skipped instead of silently
     * creating new ones, keeping the state surface bit-for-bit the same set
     * that state() handed to the signed snapshot.
     *
     * @param array<string, mixed> $state
     */
    public function hydrate(array $state): void
    {
        foreach ($state as $name => $value) {
            try {
                $reflection = new \ReflectionProperty($this, (string) $name);
            } catch (\ReflectionException) {
                continue;
            }
            if ($reflection->isPublic() && ! $reflection->isStatic()) {
                $this->{$name} = $value;
            }
        }
        $this->booted($state);
    }

    /**
     * Invoked by the engine after an action that changed public state.
     */
    public function notifyUpdated(string $property): void
    {
        $this->updated($property);
    }
}