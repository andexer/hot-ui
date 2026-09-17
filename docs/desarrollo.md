# Desarrollo — comandos y flujo de trabajo

## Requisitos

- PHP ≥ 8.2 con Composer
- Node ≥ 20 con npm (solo para tooling TS; el runtime es Alpine vía ESM)

## Instalación

```bash
composer install
npm install
```

## Comandos

| Comando | Qué hace |
|---|---|
| `composer test` | suite PHPUnit del núcleo PHP (AttributeBag, TailwindMerge, TemplateRenderer, Ui…) |
| `composer smoke` | renderiza los 384 componentes y valida salida |
| `composer examples` | renderiza login/dashboard/blog y valida sus páginas |
| `npm run dev` | `esbuild` en watch: genera `js/app.js` (bundle ESM único) |
| `npm run build` | `build:js` (bundle minificado) + `build:css` (Tailwind) |
| `npm run build:js` | empaqueta `js/src/app.ts` + Alpine + morphdom → `js/app.js` |
| `npm run typecheck` | verificación de tipos (`tsc --noEmit`) sin emitir |
| `php -S 127.0.0.1:8080 demo/router.php` | demo visual en http://127.0.0.1:8080 |

## Estructura

```
src/Components/          núcleo PHP (Ui, TemplateRenderer, registry, Support/*)
views/components/        384 plantillas espejo {ui,blocks}
js/
├── app.js               BUNDLE distribuible único (esbuild): Alpine + kernel + islas + driver Hotfire
└── src/
    ├── app.ts           cargador: kernel + islas → alpine:init → start(); instala Hotfire
    ├── hot/             kernel (plugin, directivas x-hot-*, engines, dom, theme)
    ├── hot/hotfire/     driver `data-hot-*` → POST → morphdom (ts + shim morphdom)
    └── components/ui/   islas TS espejo de las vistas (mismo nombre kebab)
css/hot-ui.css           fuente Tailwind v4 (fundaciones + tokens tema)
css/hot-ui.min.css       CSS compilado que se distribuye (npm run build:css)
src/Components/Hotfire/  capa reactiva PHP: Config, Snapshot, Component, Engine, HtmlTransform
src/Components/Ci4/Http/ HotfireController (endpoint POST hot-ui/update)
demo/router.php          router de la demo (estáticos + página)
docs/*.md                documentación del proyecto (español)
```

## Flujo típico

1. **Cambios de vista**: edita el `.php`; `composer smoke` valida.
2. **Cambios de comportamiento**: edita el `.ts`; `npm run dev` regenera el
   bundle `js/app.js` mientras pruebas en la demo o en un host.
3. **Componente nuevo**: sigue `docs/guia-componentes.md`.
4. **Antes de commit**: `composer test && composer smoke && npm run typecheck && npm run build` — `build` regenera `js/app.js` (bundle) y `css/hot-ui.min.css`; commitea ambos (son lo que se distribuye; el publicador copia `js/app.js`, nunca `js/src/`).

## Consumo desde una aplicación externa

### Cero dependencias: funciona en cualquier app PHP

Hot-UI no requiere ningún motor de plantillas. Los helpers `ui()`, `e()`, `js()`
se cargan automáticamente vía Composer `files` y son globales.

#### CodeIgniter 4 (100% nativo)

```bash
composer require hot-ui/hot-ui

# publicar assets a FCPATH (public/):
php -r "Components\Ci4\Ci4::publish();"
```

```php
// Controller
use Components\Ci4\Ci4;
return $this->response->setBody(Ci4::render('layouts/app', [
    'content' => Ci4::boot()->uiCard([], 'Hola'),
]));
```

En cualquier vista CI4: `<?= ui()->card([], 'Hola') ?>`.

Guía completa → `docs/codeigniter4.md`.

#### Standalone

```php
use Components\HotUI;
$ui = HotUI::shared();
echo $ui->card(['class' => 'max-w-sm'], 'Hola');
echo $ui->render('layouts/app', ['content' => '…']);
```

## Tema programático

```js
Alpine.store('theme').toggle();      // claro/oscuro
Alpine.store('theme').set('preset', 'teal');
window.exportTheme();                // CSS completo listo para pegar
```
