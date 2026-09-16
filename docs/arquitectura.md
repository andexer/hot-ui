# Arquitectura de Hot-UI

Hot-UI es una librería de componentes UI estilo shadcn construida sobre dos
pilares deliberadamente pequeños: **PHP 8.2+ (cero dependencias)** para las
vistas y **TypeScript estricto + Alpine.js** para la interactividad. No hay
framework intermedio: nada de Laravel, Livewire, motores de plantillas o
dependencias de runtime más allá de `alpinejs`.

```
┌─────────────────────────────  PÁGINA  ─────────────────────────────┐
│  <link /css/hot-ui.min.css>      <script module /js/app.js>      │
└───────────────┬──────────────────────────────┬────────────────────┘
                │                              │
        views/components/{ui,blocks}/*.php    js/src/**            │
        (marcado puro, sintaxis alternativa) ├── hot/   ← kernel   │
                │                            └── components/ui/*.ts│
                ▼                                    │            │
        Components\Ui (renderer propio)          Alpine.js          │
        props()/aware()/$attributes           engines+islas       │
```

## Capa PHP (`src/Components`)

| Pieza | Responsabilidad única |
|---|---|
| `Ui` | Fachada de entrada: resuelve nombres, renderiza, gestiona streaming y el stack `share/aware` |
| `ComponentRegistry` | Escanea `views/components/*` y mapea nombres kebab → funciones camel |
| `Support\TemplateRenderer` | Renderer zero-dep: resuelve `ns::template`, extrae datos, binda `$this` para `$this->uiXxx()` |
| `Support\AttributeBag` | Atributos HTML inmutables: merge/class/twMerge/only/except + escapado |
| `Support\TailwindMerge` | Resolución de conflictos Tailwind (grupos por eje, variantes, brackets) |
| `Support\Slot` | Slots perezosos (Closure\|string); solo se renderizan si se usan |
| `Support\Js` | Serialización attribute-safe para Alpine (`js()`) |
| `helpers.php` | Contrato de plantilla: `props()`, `aware()`, `e()`, `classes()`, `safe_url()`, `ui()` |

Cada componente es una plantilla PHP que arranca con:

```php
<?php extract(props($__ctx, ['variant' => 'default'])); ?>
```

y deja en scope: props con defaults, `$attributes` (bag), `$slot` y los slots
nombrados pasados. La lógica vive arriba; el marcado abajo, legible.

## Capa JS (`js/src`)

### Kernel (`hot/`) — infraestructura compartida

- **`plugin.ts`**: contrato `IslandPlugin { name, register(HotContext) }`.
- **`register.ts`**: registra store `theme`, directivas `x-hot-*`
  (trigger, labelledby, anchor, dialog-layer, field, collapse), los motores
  `hotMenu/hotMenubar/hotSelect/hotListbox/hotCommand` y la magic única
  `$hot.{model, nav, type, number}`.
- **`dom/position.ts`**: motor de posicionamiento propio (offset→flip→shift→
  size + autoUpdate). Sustituye a floating-ui sin dependencias.
- **`dom/wiring.ts`**: re-derivación idempotente de ARIA ante cambios del DOM
  (`keepWired`), ids estables, resolución del control real dentro de wrappers.
- **`stores/theme.ts`**: modo/base/preset/radius/fuente… persistidos y
  aplicados como data-atributos que consume `css/hot-ui.min.css`.

### Islas (`components/ui/<componente>.ts`) — espejo 1:1 del PHP

Una isla existe SOLO cuando hay comportamiento sustancioso que merece módulo
propio (regla de los ~15 líneas). Cada isla exporta un `IslandPlugin` por
defecto y se registra en `components/islands.ts` — el único archivo que se
toca para añadirla. El cargador `app.ts` agrega kernel + islas dentro de
`alpine:init` y arranca Alpine: un único camino de ejecución.

Familias **sin isla a propósito** (el kernel ya lo cubre): dropdown-menu,
context-menu, menubar, select, combobox, tooltip, hover-card, tabs, stepper,
navigation-menu, input-otp, server-table, scheduler, gantt.

## Regla de oro de reactividad

Los controladores Alpine operan SIEMPRE sobre `this`: Alpine envuelve la
factoría en un proxy reactivo y escribir a través de cualquier otra referencia
(closures sobre el objeto crudo) congela la UI. Los `$magic` inyectados
(`$nextTick`, `$refs`, `$dispatch`) se tipan con intersecciones locales.

## Flujo de datos de un valor acotado

```
data-hot-model="user.name"  ──►  $hot.model(default)
     ▲                                │
     │  escribe por reactividad       ▼
  scope x-data más cercano ◄── controller (this._model.value)
```

Sin segundo origen de verdad: si hay binding, el path ES el estado; si no lo
hay, el modelo guarda localmente y el componente funciona igual.
