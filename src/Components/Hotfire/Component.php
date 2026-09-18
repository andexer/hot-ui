<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Events\DispatchesEvents;
use Components\Hotfire\Navigation\NavigationSupport;
use Components\Hotfire\Pagination\HasPagination;
use Components\Hotfire\Responses\ReturnsResponses;
use Components\Hotfire\Uploads\HandleUploads;
use Components\Hotfire\Validation\ValidatesRequests;

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
    use ValidatesRequests;
    use DispatchesEvents;
    use ReturnsResponses;
    use NavigationSupport;
    use HandleUploads;
    use HasPagination;

    /** Template, relative to the configured views path. */
    protected string $view = '';

    /**
     * Explicitly allowed actions. If set, only these methods can be called.
     * 
     * @var array<int, string>|null
     */
    public ?array $allowedActions = null;

    /**
     * Override to keep the callable action list on the server. Returning null
     * means every public non-reserved method remains callable.
     *
     * @return list<string>|null
     */
    public function allowedActions(): ?array
    {
        return $this->allowedActions;
    }

    /**
     * Locked properties that cannot be updated from the client.
     * 
     * @var array<int, string>
     */
    protected array $lockedProperties = [];

    /**
     * Component key for stable identification in nested scenarios.
     * 
     * @var string|null
     */
    public ?string $key = null;

    /**
     * Called once with fresh state when the component instance is created
     * (both the initial render and every round-trip).
     * 
     * @param array<string, mixed> $params Initial parameters from props
     */
    public function mount(array $params = []): void
    {
    }

    /**
     * Called once when the component is first initialized (before mount).
     * Good for setup that should only happen once.
     */
    public function boot(): void
    {
    }

    /** Runs when fresh state is applied during hydration. */
    public function booted(array $state): void
    {
    }

    /**
     * Runs before a property is updated.
     * 
     * @param string $property Property name
     * @param mixed $value New value
     */
    public function updating(string $property, mixed $value): void
    {
    }

    /** Runs whenever a public property is updated through an action. */
    public function updated(string $property): void
    {
    }

    /**
     * Runs before an action method is called.
     * 
     * @param string $method Method name
     * @param array<int, mixed> $params Method parameters
     */
    public function beforeAction(string $method, array $params): void
    {
    }

    /**
     * Runs after an action method is called.
     * 
     * @param string $method Method name
     * @param array<int, mixed> $params Method parameters
     */
    public function afterAction(string $method, array $params): void
    {
    }

    /**
     * Runs before component state is serialized to snapshot.
     * 
     * @return array<string, mixed> Modified state
     */
    public function dehydrate(array $state): array
    {
        return $state;
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
        $computed = $this->computedProperties();
        
        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $name = $property->getName();
            if (str_starts_with($name, '__')) {
                continue;
            }
            // Exclude trait properties (errors, customMessages) and lifecycle tracking
            if (in_array($name, ['allowedActions', 'errors', 'customMessages', 'componentId', 'eventListeners', 'key'], true)) {
                continue;
            }
            // Exclude navigation tracking properties
            if (in_array($name, ['historyStack', 'historyIndex', 'scrollPositions', 'persistentElements'], true)) {
                continue;
            }
            // Exclude upload tracking properties
            if (in_array($name, ['uploadProgress'], true)) {
                continue;
            }
            // Exclude lifecycle tracking properties (ending with Called)
            if (str_ends_with($name, 'Called')) {
                continue;
            }
            // Exclude computed properties (they are added separately)
            if (in_array($name, $computed, true)) {
                continue;
            }
            if (! $property->isInitialized($this)) {
                $state[$name] = null;
                continue;
            }
            $state[$name] = $this->{$name};
        }

        // Add computed properties
        foreach ($computed as $prop) {
            if (method_exists($this, 'get'.ucfirst($prop))) {
                $method = 'get'.ucfirst($prop);
                $state[$prop] = $this->{$method}();
            }
        }

        return $state;
    }

    /**
     * Computed properties that should be included in state but not persisted.
     * 
     * @return array<int, string> List of computed property names
     */
    public function computedProperties(): array
    {
        return [];
    }

    /**
     * Lock a property so it cannot be updated from the client.
     * 
     * @param string $property Property name
     * @return void
     */
    public function lockProperty(string $property): void
    {
        if (! in_array($property, $this->lockedProperties, true)) {
            $this->lockedProperties[] = $property;
        }
    }

    /**
     * Check if a property is locked.
     * 
     * @param string $property Property name
     * @return bool Whether the property is locked
     */
    public function isLocked(string $property): bool
    {
        return in_array($property, $this->lockedProperties, true);
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
        $computed = $this->computedProperties();

        foreach ($state as $name => $value) {
            if (in_array((string) $name, ['allowedActions', 'errors', 'customMessages', 'componentId', 'key'], true)) {
                continue;
            }
            if (in_array((string) $name, $computed, true)) {
                continue;
            }
            try {
                $reflection = new \ReflectionProperty($this, (string) $name);
            } catch (\ReflectionException) {
                continue;
            }
            if ($reflection->isPublic() && ! $reflection->isStatic()) {
                if ($value === null && $reflection->hasType() && ! $reflection->getType()?->allowsNull()) {
                    continue;
                }
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

    /**
     * Renders a labelled form control for a declared public state property.
     *
     * Used by generated "form" templates (make:hotfire --mfc), which loop over
     * the $props the engine exposes and call $this->renderField($prop). Type
     * aware: booleans render a checkbox, integers/floats a number input, and
     * everything else a text input — all bound with hot:model.
     *
     * @param string $property Declared public property name.
     */
    public function renderField(string $property): string
    {
        try {
            $reflection = new \ReflectionProperty($this, $property);
        } catch (\ReflectionException) {
            return '';
        }
        if (! $reflection->isPublic() || $reflection->isStatic()) {
            return '';
        }

        $type = $reflection->getType();
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : null;
        $model = htmlspecialchars($property, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars(ucfirst(str_replace(['_', '-'], ' ', $property)), ENT_QUOTES, 'UTF-8');
        $id = 'hotfire-'.htmlspecialchars(str_replace('.', '-', $property), ENT_QUOTES, 'UTF-8');
        $value = $reflection->isInitialized($this) ? $this->{$property} : null;

        if ($typeName === 'bool') {
            $checked = $value === true ? ' checked' : '';

            return '<div>'
                .'<label for="'.$id.'" class="flex items-center gap-2 text-sm font-medium text-gray-700">'
                .'<input type="checkbox" id="'.$id.'" hot:model="'.$model.'"'.$checked
                .' class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">'
                .$label
                .'</label></div>';
        }

        $inputType = in_array($typeName, ['int', 'float'], true) ? 'number' : 'text';
        $escapedValue = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

        return '<div>'
            .'<label for="'.$id.'" class="block text-sm font-medium text-gray-700">'.$label.'</label>'
            .'<input type="'.$inputType.'" id="'.$id.'" hot:model="'.$model.'" value="'.$escapedValue.'"'
            .' class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">'
            .'</div>';
    }

    /**
     * Gets validation errors (for form display).
     * 
     * @return array<string, string> Error messages
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * Checks if the component has validation errors.
     * 
     * @return bool Whether there are errors
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
