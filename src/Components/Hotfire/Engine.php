<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Exception\InvalidActionException;
use Components\Hotfire\Exception\UnknownComponentException;
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

        $snapshot = Snapshot::encode([
            'class' => get_class($component),
            'state' => $component->state(),
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
     * @return array{html: string, snapshot: array{payload: string, checksum: string}}
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
            if (! self::isModelProperty($component, $property, $state)) {
                throw InvalidActionException::notAStateProperty($class, $property);
            }
            $component->{$property} = self::castValue($component, $property, $action['value'] ?? null);
            $component->notifyUpdated($property);
        } elseif ($name !== 'poll') {
            $method = (string) ($action['method'] ?? '');
            if ($config->isReserved($method)) {
                throw InvalidActionException::reservedMethod($class, $method);
            }
            if (! method_exists($component, $method) || ! (new \ReflectionMethod($component, $method))->isPublic()) {
                throw InvalidActionException::notAPublicMethod($class, $method);
            }
            $component->{$method}(...((array) ($action['params'] ?? [])));
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

        return HtmlTransform::apply(
            $ui->view($component->viewPath(), $component->state() + ['component' => $component], $ui->viewsPath()),
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
        if ($class === '' || ! class_exists($class) || ! is_subclass_of($class, Component::class)) {
            throw new UnknownComponentException($class);
        }

        $component = new $class();
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