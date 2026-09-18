<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Exception\InvalidActionException;
use Components\Hotfire\Exception\UnknownComponentException;
use Components\Hotfire\Security\CsrfProtection;
use Components\Hotfire\Responses\DownloadResponse;
use Components\Hotfire\Responses\FlashMessage;
use Components\Hotfire\Responses\NoContentResponse;
use Components\Hotfire\Responses\RedirectResponse;
use Components\HotUI;
use Components\Ui;

/**
 * Server side of the Hotfire lifecycle:
 *
 *   render()  - signs state, renders the template, returns the DOM fragment
 *               (wrapped in a hotfire root) plus a fresh snapshot.
 *   call()    - verifies the snapshot, hydrates the component, runs the
 *               requested action (method call or model update), re-renders and
 *               re-signs.
 *
 * The protocol is JSON over POST (see the framework adapters). The core stays
 * framework-free: anything that can call a PHP method can drive it.
 */
final class Engine
{
    /**
     * Renders a component fresh (initial page load).
     *
     * @param Component   $component Freshly mounted instance.
     * @param string|null $actionUrl Endpoint that handles updates; defaults to
     *                               the configured endpoint. Pass '' to omit it.
     * @param string|null $key       Snapshot signing key (defaults to Config).
     * @param Ui|null     $ui        Renderer override.
     * @param Config|null $config    Configuration override.
     *
     * @return array{html: string, snapshot: array{payload: string, checksum: string}}
     */
    public static function render(
        Component $component,
        ?string $actionUrl = null,
        ?string $key = null,
        ?Ui $ui = null,
        ?Config $config = null,
    ): array {
        $config ??= Config::shared();
        $actionUrl ??= $config->endpoint();

        $state = $component->state();
        $state = $component->dehydrate($state);

        $snapshot = Snapshot::encode([
            'class' => get_class($component),
            'state' => $state,
        ], $key, $config);

        $html = self::fragment($component, $ui, $config);

        return [
            'html' => self::wrap($html, $snapshot, $actionUrl),
            'snapshot' => $snapshot,
        ];
    }

    /**
     * Processes an interaction: model update, method call or poll refresh.
     *
     * @param array{payload: string, checksum: string} $snapshot Signed state
     *              (contains the component class plus serialized properties).
     * @param array{name?: string, method?: string, params?: list<mixed>, property?: string, value?: mixed} $action
     *              Action as sent by the driver.
     * @param string|null $actionUrl Endpoint stamped into the response root.
     * @param string|null $key       Snapshot signing key (defaults to Config).
     * @param Ui|null     $ui        Renderer override.
     * @param Config|null $config    Configuration override.
     *
     * @return array{html: string, snapshot: array{payload: string, checksum: string}, response?: array{type: string, data: mixed}}
     */
    public static function call(
        array $snapshot,
        array $action,
        ?string $actionUrl = null,
        ?string $key = null,
        ?Ui $ui = null,
        ?Config $config = null,
    ): array {
        $config ??= Config::shared();

        $decoded = Snapshot::decode($snapshot, $key, $config);
        $class = (string) ($decoded['class'] ?? '');
        $component = self::instantiate($class);
        $state = (array) ($decoded['state'] ?? []);
        $component->hydrate($state);

        $name = (string) ($action['name'] ?? '');
        if ($name === 'model') {
            $property = (string) ($action['property'] ?? '');
            $stateProperty = self::statePropertyName($property);
            
            // Check if property is locked first
            if ($component->isLocked($property) || ($stateProperty !== $property && $component->isLocked($stateProperty))) {
                throw InvalidActionException::lockedProperty($class, $property);
            }
            
            if (! self::isModelProperty($component, $stateProperty, $state)) {
                throw InvalidActionException::notAStateProperty($class, $property);
            }
            
            $value = $action['value'] ?? null;
            
            // Handle array model updates (for checkboxes, multi-selects)
            if (isset($action['isArray']) && $action['isArray'] === true) {
                if ($stateProperty !== $property) {
                    $currentValue = FormBinder::getNestedValue($component, $property);
                } else {
                    $currentValue = $component->{$property} ?? [];
                }
                if (! is_array($currentValue)) {
                    $currentValue = [];
                }
                
                if (isset($action['remove']) && $action['remove'] === true) {
                    // Remove value from array
                    $key = array_search($value, $currentValue, true);
                    if ($key !== false) {
                        unset($currentValue[$key]);
                        $currentValue = array_values($currentValue);
                    }
                } else {
                    // Add value to array
                    if (! in_array($value, $currentValue, true)) {
                        $currentValue[] = $value;
                    }
                }
                
                $value = $currentValue;
            }
            
            // Call updating hook
            $component->updating($property, $value);
            
            try {
                if ($stateProperty !== $property) {
                    FormBinder::setNestedValue($component, $property, $value);
                } else {
                    $component->{$property} = self::castValue($component, $property, $value);
                }
            } catch (\TypeError) {
                throw InvalidActionException::notAStateProperty($class, $property);
            }
            $component->notifyUpdated($property);
        } elseif ($name !== 'poll') {
            $method = (string) ($action['method'] ?? '');
            
            // Check allowlist first if defined
            $allowedActions = $component->allowedActions();
            if ($allowedActions !== null && ! in_array($method, $allowedActions, true)) {
                throw InvalidActionException::notAnAllowedAction($class, $method);
            }
            
            if ($config->isReserved($method)) {
                throw InvalidActionException::reservedMethod($class, $method);
            }
            if (! method_exists($component, $method) || ! (new \ReflectionMethod($component, $method))->isPublic()) {
                throw InvalidActionException::notAPublicMethod($class, $method);
            }
            
            $params = array_values((array) ($action['params'] ?? []));
            
            // Call beforeAction hook
            $component->beforeAction($method, $params);
            
            try {
                $result = $component->{$method}(...$params);
                $component->afterAction($method, $params);
                
                // Check if action returned a special response
                if ($result instanceof RedirectResponse) {
                    return self::render($component, $actionUrl, $key, $ui, $config) + [
                        'response' => [
                            'type' => 'redirect',
                            'url' => $result->url,
                            'status' => $result->status,
                        ],
                    ];
                }
                
                if ($result instanceof FlashMessage) {
                    return self::render($component, $actionUrl, $key, $ui, $config) + [
                        'response' => [
                            'type' => 'flash',
                            'message' => $result->message,
                            'messageType' => $result->type,
                        ],
                    ];
                }
                
                if ($result instanceof DownloadResponse) {
                    return [
                        'response' => [
                            'type' => 'download',
                            'content' => $result->content,
                            'filename' => $result->filename,
                            'mimeType' => $result->mimeType,
                        ],
                    ];
                }
                
                if ($result instanceof NoContentResponse) {
                    return [
                        'response' => [
                            'type' => 'no-content',
                            'status' => $result->status,
                        ],
                    ];
                }
            } catch (\TypeError | \ArgumentCountError $error) {
                throw InvalidActionException::invalidArguments($class, $method, $error->getMessage());
            }
            
            // Call afterAction hook when not returning special response (if not already called)
        }

        return self::render($component, $actionUrl, $key, $ui, $config);
    }

    /**
     * Renders the component template into a bare HTML fragment.
     */
    public static function fragment(Component $component, ?Ui $ui = null, ?Config $config = null): string
    {
        $config ??= Config::shared();
        $ui ??= HotUI::shared();

        $state = $component->state();

        return HtmlTransform::apply(
            $ui->viewBound(
                $component->viewPath(),
                $component,
                $state + ['component' => $component, 'props' => array_keys($state)],
                $ui->viewsPath(),
            ),
            $config,
        );
    }

    /**
     * @param array{payload: string, checksum: string} $snapshot
     */
    private static function wrap(string $html, array $snapshot, ?string $actionUrl): string
    {
        $url = $actionUrl !== null && $actionUrl !== ''
            ? ' data-hot-action="'.htmlspecialchars($actionUrl, ENT_QUOTES).'"'
            : '';

        // Add CSRF token if available
        $csrfToken = CsrfProtection::getToken();
        $csrfAttr = $csrfToken !== null
            ? ' data-hot-csrf="'.htmlspecialchars($csrfToken, ENT_QUOTES).'"'
            : '';

        return sprintf(
            '<div data-hot-component data-hot-snapshot="%s" data-hot-checksum="%s"%s%s>%s</div>',
            $snapshot['payload'],
            $snapshot['checksum'],
            $url,
            $csrfAttr,
            $html,
        );
    }

    private static function instantiate(string $class): Component
    {
        if ($class === '' || ! class_exists($class) || ! is_subclass_of($class, Component::class)) {
            throw new UnknownComponentException($class);
        }

        $component = new $class();
        $component->boot();
        $component->mount();

        return $component;
    }

    /**
     * A model target must be a declared PUBLIC non-static property that the
     * verified snapshot actually carries, so the raw action array can never
     * assign stray, undeclared or non-public members.
     *
     * @param array<string, mixed> $state
     */
    private static function isModelProperty(Component $component, string $property, array $state): bool
    {
        if (! array_key_exists($property, $state)) {
            return false;
        }

        try {
            $reflection = new \ReflectionProperty($component, $property);
        } catch (\ReflectionException) {
            return false;
        }

        return $reflection->isPublic() && ! $reflection->isStatic();
    }

    private static function statePropertyName(string $property): string
    {
        return explode('.', $property, 2)[0] ?? $property;
    }

    private static function castValue(Component $component, string $property, mixed $value): mixed
    {
        $reflection = new \ReflectionProperty($component, $property);
        $type = $reflection->getType();

        if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
            return $value;
        }

        if ($value === null && $type?->allowsNull()) {
            return null;
        }

        // Handle array types
        if ($type?->getName() === 'array') {
            if (is_string($value)) {
                // JSON-decode if it's a string (from checkboxes)
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return is_array($value) ? $value : [];
        }

        return match (strtolower((string) $type?->getName())) {
            'int' => is_numeric($value) ? (int) $value : 0,
            'float' => is_numeric($value) ? (float) $value : 0.0,
            'bool' => in_array($value, [true, 'true', 1, '1', 'on', 'yes'], true),
            'string' => is_scalar($value) || $value === null ? (string) $value : '',
            default => $value,
        };
    }
}
