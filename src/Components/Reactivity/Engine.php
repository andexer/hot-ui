<?php

declare(strict_types=1);

namespace Components\Reactivity;

use Components\HotUI;
use Components\Ui;

/**
 * Orchestrates the server side of a reactive component lifecycle:
 *
 *   render()  — signs state, renders the template, returns the DOM fragment
 *               (wrapped in a data-hot-component root) + a fresh snapshot.
 *   call()    — verify snapshot, hydrate the component, run the requested
 *               action (method call or model update), re-render, re-sign.
 *
 * The protocol is JSON over POST (see the CI4 LivewireController). The core
 * stays framework-free: anything that can call a PHP method can drive it.
 */
final class Engine
{
    /** Framework-level methods that cannot be invoked directly as actions. */
    public const RESERVED = ['mount', 'booted', 'updated', 'hydrate', 'state', 'notifyUpdated', 'viewPath'];
    /**
     * Renders a component fresh (initial page load).
     *
     * @param Component       $component Freshly mounted instance.
     * @param string|null     $actionUrl Endpoint that handles updates.
     * @param string|null     $key       Snapshot signing key (defaults to env).
     * @param Ui|null         $ui        Hot-UI renderer override (tests).
     *
     * @return array{html: string, snapshot: array{payload: string, checksum: string}}
     */
    public static function render(Component $component, ?string $actionUrl = null, ?string $key = null, ?Ui $ui = null): array
    {
        $snapshot = Snapshot::encode([
            'class' => get_class($component),
            'state' => $component->state(),
        ], $key);

        $html = self::fragment($component, $ui);

        return [
            'html' => self::wrap($html, $snapshot, $actionUrl),
            'snapshot' => $snapshot,
        ];
    }

    /**
     * Processes an interaction: model update or arbitrary method call.
     *
     * @param array{payload: string, checksum: string} $snapshot Signed state
     *              (contains the component class plus serialized properties).
     * @param array{name?: string, method?: string, params?: list<mixed>, property?: string, value?: mixed} $action
     *              Action as sent by the driver.
     * @param string|null $actionUrl Endpoint that handles updates (stamped into the response root).
     * @param string|null $key       Snapshot key.
     *
     * @return array{html: string, snapshot: array{payload: string, checksum: string}}
     */
    public static function call(array $snapshot, array $action, ?string $actionUrl = null, ?string $key = null, ?Ui $ui = null): array
    {
        $decoded = Snapshot::decode($snapshot, $key);
        $class = (string) ($decoded['class'] ?? '');
        $component = self::instantiate($class);
        $component->hydrate((array) ($decoded['state'] ?? []));

        $name = $action['name'] ?? '';
        if ($name === 'model') {
            $property = (string) ($action['property'] ?? '');
            $component->{$property} = self::castValue($component, $property, $action['value'] ?? null);
            $component->notifyUpdated($property);
        } else {
            $method = (string) ($action['method'] ?? '');
            if (in_array($method, self::RESERVED, true)) {
                throw new \RuntimeException(sprintf('Hot-UI: framework method [%s] cannot be used as an action.', $method));
            }
            if (! method_exists($component, $method) || ! (new \ReflectionMethod($component, $method))->isPublic()) {
                throw new \RuntimeException(sprintf('Hot-UI: action [%s] is not a public method of %s.', $method, $class));
            }
            $component->{$method}(...((array) ($action['params'] ?? [])));
        }

        return self::render($component, $actionUrl, $key, $ui);
    }

    /**
     * Renders the component template into a bare HTML fragment.
     *
     * @param Ui|null $ui Hot-UI renderer override (tests).
     */
    public static function fragment(Component $component, ?Ui $ui = null): string
    {
        $ui ??= HotUI::shared();

        return HtmlTransform::apply(
            $ui->view($component->viewPath(), $component->state() + ['component' => $component], $ui->viewsPath()),
        );
    }

    /**
     * @param array{payload: string, checksum: string} $snapshot
     */
    private static function wrap(string $html, array $snapshot, ?string $actionUrl): string
    {
        $url = $actionUrl !== null ? ' data-hot-action="'.htmlspecialchars($actionUrl, ENT_QUOTES).'"' : '';

        return sprintf(
            '<div data-hot-component data-hot-snapshot="%s" data-hot-checksum="%s"%s>%s</div>',
            $snapshot['payload'],
            $snapshot['checksum'],
            $url,
            $html,
        );
    }

    private static function instantiate(string $class): Component
    {
        if (! class_exists($class)) {
            throw new \RuntimeException(sprintf('Hot-UI: component class [%s] not found.', $class));
        }
        /** @var Component $component */
        $component = new $class();
        if (! $component instanceof Component) {
            throw new \RuntimeException(sprintf('Hot-UI: [%s] must extend %s.', $class, Component::class));
        }
        $component->mount();

        return $component;
    }

    private static function castValue(Component $component, string $property, mixed $value): mixed
    {
        $reflection = new \ReflectionProperty($component, $property);
        $type = $reflection->getType();

        if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
            return $value;
        }

        return match (strtolower((string) $type?->getName())) {
            'int' => is_numeric($value) ? (int) $value : 0,
            'float' => is_numeric($value) ? (float) $value : 0.0,
            'bool' => in_array($value, [true, 'true', 1, '1', 'on', 'yes'], true),
            default => $value,
        };
    }
}