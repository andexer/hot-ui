# Integración 100% con CodeIgniter 4

Hot-UI v2.0 es **cero dependencias PHP**. No necesita League Plates: incluye su
propio renderer. Funciona dentro de CI4 sin tocar el motor de vistas del
framework: los helpers (`ui()`, `e()`, `js()`…) se autoloadan vía Composer y
puedes llamarlos desde cualquier vista CI4 nativa.

## Instalación

```bash
composer require hot-ui/hot-ui
```

Composer autoload carga `src/helpers.php` (funciones globales) y PSR-4
(`Components\`, `Components\Ci4\`). No necesitas configurar nada.

## Publicar assets

Añade el siguiente hook **una sola vez** en el `composer.json` de tu app CI4.
Tras cada `composer install` o `composer update`, los archivos css/js se
copian automáticamente a la carpeta `public/`:

```json
"scripts": {
    "post-install-cmd": ["Components\\HotUI::autoPublish"],
    "post-update-cmd": ["Components\\HotUI::autoPublish"]
}
```

`HotUI::autoPublish()` usa `FCPATH` automáticamente dentro de CI4. Alternativas
manuales:

```bash
php -r "require 'vendor/autoload.php'; Components\HotUI::autoPublish();"
```

```php
use Components\Ci4\Ci4;
use Components\HotUI;

HotUI::autoPublish();          // detecta FCPATH / public/
Ci4::publish();                // solo FCPATH
```

Esto copia `css/hot-ui.css` y `js/app.js` (con sus fuentes) a la carpeta
`public/` de CI4.

## Uso en un controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Components\Ci4\Ci4;

class Home extends BaseController
{
    public function index(): string
    {
        $ui = Ci4::boot();              // singleton del paquete

        return $this->response->setBody($ui->render('layouts/app', [
            'title'   => 'Dashboard',
            'meta'    => $ui->render('partials/meta', ['title' => 'Dashboard']),
            'content' => $ui->uiCard(['class' => 'max-w-sm'], 'Bienvenido'),
            'nav'     => ['Inicio' => '/', 'Miembros' => '/members'],
        ]));
    }
}
```

## Uso en vistas CI4 (helpers globales)

Cualquier vista CI4 cargada por `view()` puede usar los helpers sin hacer nada
extra:

```php
<!-- app/Views/mi-vista.php -->
<div class="flex gap-4">
    <?= ui()->button(['href' => '/nuevo', 'size' => 'sm'], 'Nuevo registro') ?>

    <?php ui()->open('card', ['class' => 'w-full']); ?>
        <?php ui()->open('card-header'); ?>
            <?= ui()->cardTitle([], 'Resumen') ?>
        <?= ui()->close() ?>
        <?php ui()->open('card-content'); ?>
            <?= ui()->badge(['tone' => 'success'], 'Activo') ?>
        <?= ui()->close() ?>
    <?= ui()->close() ?>
</div>
```

### `$this->uiXxx(…)` vs `ui()->uiXxx(…)`

| Contexto | Forma | Funciona |
|---|---|---|
| Vistas CI4 (renderizadas por `view()`) | `ui()->card(…)` | Sí |
| Vistas CI4 (renderizadas por `view()`) | `$this->card(…)` | **No** — `$this` es el objeto View de CI4 |
| Plantillas Hot-UI (renderizadas por el renderer propio) | `$this->uiCard(…)` | Sí — `$this` es el TemplateRenderer |
| Plantillas Hot-UI (renderizadas por el renderer propio) | `ui()->card(…)` | Sí (ambos estilos son equivalentes) |

> Regla simple: usa `ui()->…` en vistas CI4 y `$this->…` dentro de las
> propias plantillas de Hot-UI. Ambos son 100% equivalentes.

Como las vistas CI4 no se renderizan con el motor de Hot-UI, el paquete
también permite escribir páginas (archivos bajo `APPPATH.'Views'`) con la
sintaxis de tags y dejarlas compiladas+cacheadas:

```php
// En un controller
use Components\Ci4\Ci4;

public function sintaxis(): string
{
    $page = Ci4::view('hotui/pagina', ['variant' => 'destructive']);

    return $this->response->setBody($page);
}
```

```html
<!-- app/Views/hotui/pagina.php -->
<ui:button :variant="$variant" @click="guardar()">Guardar</ui:button>
```

`session()->…` y cualquier PHP de CI4 funcionan dentro del archivo; solo los
tags `<ui:…>`/`<blocks:…>` se traducen. Incluso puedes envolver el resultado
en un layout Hot-UI:

```php
$body = Ci4::view('hotui/contenido', $datos);
echo $ui->render('layouts/app', ['title' => '…', 'content' => $body]);
```

Detalles de la sintaxis (reglas híbridas de `:`, `ui:slot`, `$__ui`,
caché) en [`docs/sintaxis-tags.md`](sintaxis-tags.md). La caché compilada vive
en `WRITEPATH/cache/hotui`; borrarla reconstruye los archivos.

## Compartir datos entre vistas CI4 y Hot-UI

### Con el helper `ui()->render()`

```php
// En controller
$page = ui()->render('partials/meta', ['title' => 'Hola']);
echo ui()->render('layouts/app', ['content' => $page, 'title' => 'Hola']);
```

### Pasando datos de CI4 a componentes

```php
// En una vista CI4
<?php $user = session()->get('user'); ?>
<?= ui()->avatar(['class' => 'size-8'], fn () => ui()->avatarFallback([], $user['iniciales'])) ?>
```

## Seguridad en CI4

- **`e()`** — reemplaza al `esc()` de CI4 en contexto de vistas Hot-UI
  (funciona igual; `htmlspecialchars` con `ENT_QUOTES`).
- **`safe_url()`** — valida URLs contra una allowlist
  (http, https, mailto, tel, sms, ftp, #, rutas relativas).
- **`js()`** — serializa datos para `x-data="…"` de Alpine.js (attribute-safe).
- **`$attributes->twMerge(…)`** — escapa automáticamente atributos HTML.

En vistas CI4 normales, sigue usando `esc()` de CI4 para tus propias salidas.
Los componentes Hot-UI ya gestionan su propio escapado.

## Ejecutar la demo

```bash
cd vendor/hot-ui/hot-ui
php -S 127.0.0.1:8080 demo/router.php
```

## Renderizado propio: sin `view()`

Hot-UI renderiza con su propio renderer (PHP `include` puro). Las vistas CI4
nunca se tocan: el paquete resuelve `views/components/`, `views/layouts/` y
`views/partials/` internamente, aislado del motor de CI4. No hay colisiones de
nombres de plantilla.

Si necesitas sobreescribir un layout o partial del paquete, pasa tu propio
`view_path`:

```php
$ui = HotUI::instance(['view_path' => APPPATH.'Views/hotui']);
```
