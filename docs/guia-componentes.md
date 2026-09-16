# Guía: crear un componente Hot-UI nuevo

De cero a componente completo (vista PHP + isla TS cuando aplique).

## Dos estilos de llamada (mismo HTML)

### 1. Streaming open/close — RECOMENDADO

Sin closures; el nombre es el archivo tal cual; las variables de la plantilla
están visibles sin `use()`:

```php
<?php ui()->open('card', ['class' => 'max-w-sm']); ?>

    <?php ui()->open('card-header'); ?>
        <?php ui()->open('card-title'); ?>
            Bienvenido
        <?= ui()->close() ?>
    <?= ui()->close() ?>

    <?php ui()->open('card-content', ['class' => 'space-y-3']); ?>
        <?php ui()->open('input', ['type' => 'email', 'placeholder' => 'tu@correo.com']); echo ui()->close(); ?>
        <?php ui()->open('button', ['class' => 'w-full']); ?>
            Entrar
        <?= ui()->close() ?>
    <?= ui()->close() ?>

<?= ui()->close() ?>
```

Slots nombrados: cambia el destino de captura con `into()` y vuelve al slot
por defecto llamándolo sin argumentos:

```php
<?php ui()->open('input', ['type' => 'password']); ?>
    <?php ui()->into('leading'); ?>
        <span class="text-muted-foreground">🔒</span>
    <?php ui()->into(); /* de vuelta al default */ ?>
<?= ui()->close() ?>
```

`close()` devuelve el HTML — imprímelo (`echo ui()->close()` / `<?= ui()->close() ?>`)
o asígnalo a una variable. Si una excepción interrumpe el flujo,
`ui()->discard()` cierra todos los frames abiertos.

### 2. Por argumentos (slots-closure)

```php
<?= ui()->card(['class' => 'max-w-sm'], function () { ?>
    <?= ui()->cardTitle([], 'Bienvenido') ?>
<?php }) ?>
```

Útil para piezas pequeñas o cuando prefieres expresiones. Ambos estilos se
mezclan sin problema dentro del mismo árbol.

## 1. Vista PHP

Crea `views/components/ui/mi-widget.php`:

```php
<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'default',
    'label'   => null,
]));
?>
<div
    data-slot="mi-widget"
    x-data="hotMiWidget({ variant: <?= js($variant) ?> })"
    <?= $attributes->twMerge($clases[$variant] ?? $clases['default']) ?>
>
    <?php if ($label !== null) { ?><span><?= e($label) ?></span><?php } ?>
    <?= $slot ?>
</div>
```

Puntos clave:
- `data-slot` siempre (es el contrato CSS y de las directivas `x-hot-*`).
- Clases literales en variables arriba para que el escáner de Tailwind las vea.
- Texto dinámico con `e()`, valores hacia JS con `js()`.

Uso inmediato, sin registrar nada más:

```php
<?= $this->uiMiWidget(['class' => 'w-full'], 'Contenido') ?>
```

## 2. Isla TypeScript (solo si hay lógica sustanciosa)

Si el widget necesita estado/comportamiento real (>~15 líneas), crea la isla
ESPEJO: `js/src/components/ui/mi-widget.ts`.

```ts
import type { IslandPlugin } from '../../hot/plugin.js';

export interface MiWidgetConfig { variant?: string }

interface Runtime {
    $nextTick(callback: () => void): void;
}

type Live<T> = T & Runtime;

/** Controlador reactivo: opera SIEMPRE sobre `this`. */
export interface HotMiWidgetController {
    variant: string;
    count: number;
    init(): void;
    increment(): void;
}

export function createMiWidget(config: MiWidgetConfig = {}): HotMiWidgetController {
    return {
        variant: config.variant ?? 'default',
        count: 0,

        init(): void {
            // Alpine llama init() automáticamente al montar.
        },

        increment(this: Live<HotMiWidgetController>): void {
            this.count += 1;
            this.$nextTick(() => this.$nextTick(() => { /* post-render */ }));
        },
    };
}

export default {
    name: 'mi-widget',
    register({ alpine }): void {
        alpine.data('hotMiWidget', createMiWidget as never);
    },
} satisfies IslandPlugin;
```

## 3. Registrarla

Una sola línea en `js/src/components/islands.ts`:

```ts
import miWidget from './ui/mi-widget.js';
// ...
    miWidget,
```

## 4. Compilar y verificar

```bash
npm run typecheck && npm run build
composer smoke        # 384+1/384+1 renderizan
php -S 127.0.0.1:8080 demo/router.php
```

## Checklist de calidad

- [ ] Sin Laravel/Illuminate/Livewire en vistas ni TS
- [ ] Todo eco dinámico pasa por `e()`; URLs por `safe_url()`
- [ ] Reactividad por `this` (nunca closures sobre el objeto crudo)
- [ ] `data-slot` presente; clases Tailwind como strings literales en el .php
- [ ] `npm run typecheck` verde · smoke verde
