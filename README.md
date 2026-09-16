# Hot-UI

> **Fase beta** — en desarrollo activo. Falta validarse en proyectos reales
> antes de alcanzar la primera versión estable (`v1`).

Paquete Composer de componentes UI estilo shadcn para **PHP vainilla (cero
dependencias)** + **Alpine.js tipado**. Sin Laravel, sin Livewire, sin motores
de plantillas: funciona en **CodeIgniter 4**, Plates, Blade o cualquier app PHP.

```bash
composer require hot-ui/hot-ui
```

- **Cero dependencias PHP**: solo `php ^8.2` (renderer propio, PHP vainilla)
- **384 componentes** (`views/components/{ui,blocks}`) — sintaxis alternativa
- **Kernel TS + islas espejo** — `js/src/components/ui/<componente>.ts` junto a su `.php`
- **Cero dependencias JS extra**: posición, focus-trap y colapso son propios del kernel
- **Seguridad por defecto**: escapado explícito, URLs con allowlist, atributos validados

## Uso en CodeIgniter 4 (100% nativo)

No tocas el motor de vistas de CI4: los componentes se resuelven en un motor
interno aislado. Los helpers globales (`ui()`, `e()`, `js()`…) se autoloadan
vía Composer y funcionan dentro de cualquier vista CI4.

```bash
composer require hot-ui/hot-ui

# publica css/ y js/ a FCPATH (public/)
php -r "Components\Ci4\Ci4::publish();"
```

En un controller:

```php
use Components\Ci4\Ci4;

public function dashboard()
{
    $page = Ci4::render('layouts/app', [
        'title'   => 'Dashboard',
        'content' => Ci4::boot()->uiCard(['class' => 'max-w-sm'], 'Bienvenido'),
    ]);

    return $this->response->setBody($page);
}
```

Dentro de cualquier vista CI4 — estilo recomendado, sintaxis de tags
(`<ui:…>`, sin `ob_start` ni closures):

```html
<!-- app/Views/mi-vista.php -->
<ui:card class="max-w-sm">
    <ui:card-header>
        <ui:card-title>Bienvenido</ui:card-title>
    </ui:card-header>
    <ui:button :variant="$variant" @click="guardar()">Guardar</ui:button>
</ui:card>
```

```php
public function dashboard(): string
{
    $body   = Ci4::view('mi-vista', ['variant' => 'destructive']);
    $layout = Ci4::render('layouts/app', ['title' => '…', 'content' => $body]);

    return $this->response->setBody($layout);
}
```

`Ci4::view()` compila los tags una sola vez, cachea el archivo y deja el resto
del HTML/PHP intacto; atributos: `:prop` = PHP, `x-*`/`@*`/`data-*`/`aria-*` =
strings de Alpine. Detalles en [`docs/sintaxis-tags.md`](docs/sintaxis-tags.md).

Los demás estilos producen el mismo HTML y se mezclan libremente:

<details>
<summary>Streaming (open/close)</summary>

```php
<?php ui()->open('card', ['class' => 'max-w-sm']); ?>
    <?php ui()->open('card-title'); ?>
        Bienvenido
    <?= ui()->close() ?>
    <?php ui()->open('button', ['variant' => 'destructive']); ?>
        Borrar
    <?= ui()->close() ?>
<?= ui()->close() ?>
```

</details>

<details>
<summary>Por argumentos (closures)</summary>

```php
<?= ui()->card(['class' => 'max-w-sm'], function () { ?>
    <?= ui()->cardTitle([], 'Bienvenido') ?>
<?php }) ?>
```

</details>

> ¿Por qué `ui()->…` y no `$this->uiCard(…)`? CI4 no expone su objeto de vista
> a las plantillas; el helper global es el contrato. Dentro de las propias
> plantillas de Hot-UI (layout, partial, componente) sí puedes usar
> `$this->uiCard(…)`: el renderer interno lo soporta.

## Uso standalone (cualquier app PHP)

```php
use Components\HotUI;

$ui = HotUI::shared();                    // una instancia por proceso
echo $ui->card(['class' => 'max-w-sm'], 'Hola Hot-UI');
echo $ui->render('layouts/app', ['content' => '…', 'title' => 'Home']);
```

## Publicar assets

```php
HotUI::publish('/mi/app/public');         // copia css/ y js/ compilados
HotUI::publish('/mi/app/public', only: ['css']);   // o solo uno
// En CI4, sin argumentos usa FCPATH:
Ci4::publish();                           // → public/css + public/js
```

Y en el layout:

```html
<link rel="stylesheet" href="/css/hot-ui.css">
<script type="module" src="/js/app.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons()</script>
```

## API de entrada

| Método | Qué hace |
|---|---|
| `HotUI::instance(array $config = [])` | Instancia dedicada (standalone) |
| `HotUI::shared(array $config = [])` | Instancia única por proceso, respalda a `ui()` |
| `HotUI::views()` | Ruta absoluta a las vistas incluidas (layouts, partials, components) |
| `HotUI::assets('css'\|'js')` | Ruta absoluta al runtime incluido |
| `HotUI::publish(string $public, ?array $only = null)` | Copia css/js al público del host |
| `Ci4::boot()` | Instancia Hot-UI vinculada a CI4 (singleton) |
| `Ci4::render($template, $data)` | Renderiza una página/partial a string |
| `Ci4::view($template, $data)` | Igual que `render()` pero compila la sintaxis de tags `<ui:…>` |
| `Ci4::publish($publicDir?)` | Publica a FCPATH por defecto |
| `ui_view($template, $data)` | Helper global: alias de `Ci4::view()` / `Ui::view()` en contexto |

Config soportada: `view_path` (sobrescribe las vistas internas del paquete).

## Ejemplos completos

Con la demo corriendo (`php -S localhost:8080 demo/router.php`):

| Ruta | Página |
|---|---|
| `/` | Showcase de componentes |
| `/sintaxis` | Los mismos componentes escritos con `<ui:…>` |
| `/examples/login` | Acceso (layout guest) |
| `/examples/dashboard` | Panel admin con stats, tabla y progreso |
| `/examples/blog` | Portada de blog con paginación |

Fuente en `views/examples/`, guía en [`docs/ejemplos.md`](docs/ejemplos.md).

## Documentación

- [`docs/arquitectura.md`](docs/arquitectura.md) — capas, renderer, flujo de datos
- [`docs/codeigniter4.md`](docs/codeigniter4.md) — integración 100% nativa con CI4
- [`docs/sintaxis-tags.md`](docs/sintaxis-tags.md) — tags `<ui:…>`, `ui:slot` y reglas de atributos
- [`docs/guia-componentes.md`](docs/guia-componentes.md) — crear componente + isla
- [`docs/catalogo.md`](docs/catalogo.md) — los 384 ↔ isla/motor que usan
- [`docs/ejemplos.md`](docs/ejemplos.md) — dashboard, blog y login paso a paso
- [`docs/layouts-partials.md`](docs/layouts-partials.md) — páginas maestras y fragmentos
- [`docs/BLOCKS.md`](docs/BLOCKS.md) · [`docs/COMPONENTS.md`](docs/COMPONENTS.md) — referencia API generada (8 bloques / 376 componentes)
- [`docs/convenciones.md`](docs/convenciones.md) · [`docs/seguridad.md`](docs/seguridad.md) · [`docs/desarrollo.md`](docs/desarrollo.md)

## Verificación

```bash
composer test      # PHPUnit (111 tests)
composer smoke     # 384/384 renderizan
composer examples  # valida login, dashboard y blog
npm run typecheck && npm run build   # TS estricto → ESM nativo
```

## Licencia

MIT — libre y de código abierto.

## Autor

Arvelo Falcon · arvelofalcon@gmail.com