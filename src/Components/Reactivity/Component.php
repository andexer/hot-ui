<?php

declare(strict_types=1);

namespace Components\Reactivity;

/**
 * Base class for server-side reactive components (the "Livewire for CI4"
 * layer). Public properties are the component state; public methods are the
 * callable actions referenced from views with hot:* attributes.
 *
 *   final class Counter extends Component {
 *       public int $count = 0;
 *       protected string $view = 'counter';
 *
 *       public function increment(): void {
 *           $this->count++;
 *       }
 *   }
 *
 * Template counter.php (rendered by the Hot-UI engine):
 *
 *   <div>
 *       <button hot:click="increment">+1</button>
 *       <span class="font-semibold">{{ $count }}</span>
 *   </div>
 *
 * Wire it up: Ci4::live(Counter::class, ['count' => 0]).
 */
abstract class Component
{
    /** Template rendered by the engine (relative to the hot-ui views path). */
    protected string $view = '';

    /** Runs once after construction and before hydration. */
    /**
     * Called once with fresh state when the component instance is created
     * (both the initial render and every round-trip).
     */
    public function mount(): void
    {
    }

    /** Runs inside mount() when fresh state is applied. */
    public function booted(array $state): void
    {
    }

    /** Runs whenever a public property is updated through an action. */
    public function updated(string $property): void
    {
    }

    /**
     * Absolute path to the template to render. Defaults to the class name in
     * kebab-case under components/live/ (a hot-ui view directory).
     */
    public function viewPath(): string
    {
        if ($this->view !== '') {
            return $this->view;
        }

        $short = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', (new \ReflectionClass($this))->getShortName()) ?? '');

        return 'components/live/'.rtrim((string) $short, '-');
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
            if (str_starts_with($name, '__') || in_array($name, ['className', 'description'], true)) {
                continue;
            }
            $state[$name] = $this->{$name};
        }

        return $state;
    }

    /**
     * Applies serialized state to public properties (hydration).
     *
     * @param array<string, mixed> $state
     */
    public function hydrate(array $state): void
    {
        foreach ($state as $name => $value) {
            if (property_exists($this, $name)) {
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