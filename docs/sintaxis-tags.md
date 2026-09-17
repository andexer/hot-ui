# Sintaxis de tags `<ui:…>` y `<blocks:…>`

Desde v2.0, además de los estilos *streaming* (`open()`/`into()`/`close()`) y
*por argumentos* (`ui()->card(…)`), puedes escribir los componentes con una
sintaxis literal tipo Blade/Flux, sin buffers:

```html
<ui:card class="w-full">
    <ui:slot name="header">
        <blocks:kicker>portada</blocks:kicker>
    </ui:slot>
    <ui:button :variant="$variant" @click="guardar()">Guardar</ui:button>
</ui:card>
```

No necesita `ob_start()`/`ob_get_clean()` ni closures: el compilador traduce el
archivo una sola vez a las llamadas del API de streaming (con `$__ui->open()`,
`$__ui->into()`, `$__ui->close()` y `$__ui->renderComponent()`), lo deja en
caché compilada y lo incluye como PHP puro. El resto del archivo (HTML normal,
PHP, `<script>`, `<style>`, comentarios) **no se toca**.

La misma compilación se aplica a las **plantillas de componentes**, `layouts/`
y `partials/`: la sintaxis `<ui:…>`/`<blocks:…>` vale también dentro de un
componente, no solo en las páginas. Allí tienes además `$attributes` disponible
para el `{{ $attributes }}` (ver abajo).

## Cómo se renderiza

```php
// Standalone
echo $ui->view('pagina', ['variant' => 'destructive']);

// CódigoIgniter 4 — en un controller:
use Components\Ci4\Ci4;
$page = Ci4::view('pagina', ['variant' => 'destructive']);

// CódigoIgniter 4 — con el helper global, desde cualquier vista CI4:
echo ui_view('pagina', ['variant' => 'destructive']);
```

La resolución es idéntica a `render()`: busca `{base}/{archivo}.php` donde
`{base}` es `viewsPath()` (standalone), `APPPATH.'Views'` (CI4) o tu
`view_path` configurado. Los componentes anidados se siguen resolviendo contra
`views/components/{ui,blocks}/`.

Compila una vez por cambio de fuente (clave = `sha1(ruta+modificado+tamaño)`) y
cachea en `WRITEPATH/cache/hotui` (CI4) o `sys_get_temp_dir()/hotui-compiled`
(standalone). Borrar esa carpeta recrea la caché.

## Semántica de atributos (reglas híbridas)

La gramática sigue el estilo de Flux/Blade: atributos estáticos, props PHP
(`:`), directivas condicionales `@class`/`@style`, eventos Alpine reasignados y
_splat_ de bolsas de atributos.

| Atributo | Ejemplo | Tratamiento |
|---|---|---|
| Estático | `variant="outline"` | Valor literal (escapado al renderizar) |
| Arroba reasignado | `@click`, `@keydown.enter.prevent` | → `x-on:…` (string literal para Alpine) |
| `x-*` / `data-*` / `aria-*` | `x-model="q"`, `data-state="open"` | String literal (administra Alpine/ARIA) |
| `:var` (PHP) | `:variant="$variant"` | Evaluado en `eval` de contexto PHP |
| `:class` | `:class="$extra"` | Concatenado con `class` (`class . ' ' . $extra`) |
| `@class` | `@class(['px-4' => $activo, 'w-full'])` | Clases condicionales (ver abajo) |
| `@style` | `@style(['color' => $nivel ? 'rojo' : 'verde'])` | Estilos condicionales (ver abajo) |
| `:data-*` / `:aria-*` | `:data-state="abierto ? 'on' : 'off'"` | String literal (NO se evalúa) |
| `{{ $attributes }}` | en posición de atributo | Vuelca `->all()` de una bolsa en las props |
| `slot="nombre"` | solo en auto-cerrado `<ui:icon slot="header"/>` | Envuelve el componente en el slot con nombre padre |
| Sin valor | `disabled` | `true` (booleano) |

> Regla de oro: **`:` + prop = PHP**; `x-*`, `@*`, `data-*`, `aria-*` e
> `:data-*`/`:aria-*` = strings literales. La única excepción son las directivas
> `@class(...)`/`@style(...)`, que SÍ son PHP condicional.

### `@class([...])` / `@style([...])` — clases y estilos condicionales

Estilo Blade/Flux: las claves numéricas se emiten cuando su valor es veraz; las
claves con nombre solo cuando su condición lo es. Se mezclan con la prop
`class`/`style` final (junto con `class=` y `:class=`), admiten paréntesis
anidados (llamadas a funciones) y el `=>` no cierra el tag gracias al scanner
de paréntesis balanceados.

```html
<ui:button
    class="base"
    :class="$cssExtra"
    @class(['px-4' => $contraido, 'font-bold' => $enfasis, 'w-full'])
    @click="guardar()"
>
    Guardar
</ui:button>
```

Compila a `\Components\Support\Classes::render([...])`, evaluado en cada
request. `@style([...])` equivale para `style`, con las piezas unidas por `; `.

### `{{ $attributes }}` — reenvío de la bolsa (splat)

Igual que en Flux, dentro de un tag de componente puedes volcar una bolsa de
atributos en las props en esa posición exacta. Los atributos escritos después
ganan. Requiere una variable en scope que exponga `->all()` — típicamente la
`$attributes` que devuelve `props()`:

```html
<!-- dentro de la plantilla de un componente: -->
<?php extract(props($__ctx, [])); ?>
<ui:button {{ $attributes }}>ver</ui:button>
```

Equivale a `$__ui->renderComponent('ui.button', [$attributes->all()])`.

### `slot="nombre"` inline en auto-cerrado

Equivalente a rodear el componente con `<ui:slot name="nombre">`: se coloca en
el slot con nombre del componente padre. Solo aplica a tags auto-cerrados
(misma convención que Flux).

```html
<ui:card>
    <ui:icon slot="icono" />
    <ui:button>Guardar</ui:button>
</ui:card>
```

### Props multi-palabra: kebab → camelCase

Las props declaradas camelCase se escriben como HTML normal (kebab) y se
mapean a runtime en `props()`:

```html
<ui:pagination-link is-active>1</ui:pagination-link>
<ui:calendar :default-month="$ahora" :disable-navigation="true" />
<comparison-table :close-on-overlay="$cerrar" />
```

`is-active` → prop `isActive`, `default-month` → `defaultMonth`,
`close-on-overlay` → `closeOnOverlay`. Los atributos que NO coinciden con una
prop declarada se quedan literal en el `$attributes` bag (útil para atributos
nativos o SVG como `stroke-width`).

## Slots

### Slot por defecto

```html
<ui:button>lorem ipsum</ui:button>
```

### Slot con nombre

```html
<ui:card>
    <ui:slot name="header">
        <ui:card-title>Resumen</ui:card-title>
    </ui:slot>
    Contenido del slot por defecto…
</ui:card>
```

Los slots con nombre se exponen al componente como **variables individuales**
(`$header` en el ejemplo) — igual que en el estilo streaming/`props()`. No
existen como `$slots[…]`. Solo se renderizan si la plantilla del componente las
imprime (p. ej. `<?= $header ?>`); los `ui:slot` de componentes que no los
declaran simplemente se pierden en el cuerpo.

## Reservado: `$__ui`

Las páginas compiladas usan la variable reservada `$__ui` para enlazar la
instancia del renderer correcta (importante en CI4/tests). No declare una
variable `$__ui` en tus vistas.

## Fuera de alcance en v1

- `@if`/`@foreach` y directivas Blade → usa PHP normal (`<?php if (…) … ?>`).
- Herencia de layouts estilo Blade (`@extends`) → usa `layouts/` + `render()`.
- Componentes dinámicos → escribe el tag o usa `ui()->componentName(…)`.

## Soporte a nivel de archivo

- Los bloques `<?php … ?>` y `<?= … ?>` se saltan (no se escanean).
- `<script>`, `<style>` y comentarios `<!-- … -->` se consumen íntegros; un
  `<ui:…>` dentro de ellos **no** se compila.
- `<ui:…>` dentro del *valor* de un atributo ajeno (`<img src="<ui:fake>">`)
  tampoco se compila.
- Namespaces no registrados (`<foo:bar>`) se dejan como texto literal.
- Errores de balance lanzan `InvalidArgumentException`
  (p. ej. "Mismatched closing tag [</ui:card-header>]; expected [</ui:card>]").

## Demo

Con la demo corriendo (`php -S localhost:8080 demo/router.php`), la ruta
`/sintaxis` renderiza `views/examples/sintaxis.php` íntegramente con tags.