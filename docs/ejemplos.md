# Ejemplos completos

Tres páginas reales que ejercitan layouts, partials, componentes y bloques.
Son la mejor referencia de "cómo se monta una página" con Hot-UI. Desde v2.0
los bodies están escritos íntegramente en **sintaxis de tags** `<ui:…>`, sin
`ob_start()`/`ob_get_clean()` ni closures.

## Verlas

```bash
php -S 127.0.0.1:8080 demo/router.php
```

| Ruta | Página | Layout | Conceptos demostrados |
|---|---|---|---|
| `/examples/login` | Acceso | `layouts/guest` | card family completa, inputs, checkbox, link, variantes de botón |
| `/examples/dashboard` | Panel | `layouts/app` | container + page-header, grid responsive de stats, badges con tonos, tabla server-rendered, progress, separators |
| `/examples/blog` | Portada de blog | `layouts/app` | grid de article-cards, badge por categoría, avatar+meta, pagination family |

Cada body es una plantilla **autocontenida** en `views/examples/`. Los datos
viven en un bloque `<?php … ?>` arriba y el resto es markup legible:
`<ui:button>lorem ipsum</ui:button>`.

## Patrón recomendado (el que siguen los ejemplos)

```html
<!-- views/examples/blog.php -->
<?php
declare(strict_types=1);

$posts = [...];
$categoryTone = static fn (string $c): string => match ($c) { ... };
?>
<ui:container size="lg">
    <ui:page-header title="El blog" description="..." separator />
    <?php foreach ($posts as $post) { ?>
        <ui:card class="group">
            <ui:card-header>
                <ui:badge :tone="$categoryTone($post['categoria'])" size="sm">
                    <?= e($post['categoria']) ?>
                </ui:badge>
            </ui:card-header>
            <ui:link href="#articulo">
                <ui:card-title><?= e($post['titulo']) ?></ui:card-title>
            </ui:link>
        </ui:card>
    <?php } ?>
    <ui:pagination class="justify-center">
        <ui:pagination-link href="#p1" is-active>1</ui:pagination-link>
        <ui:pagination-link href="#p2">2</ui:pagination-link>
        <ui:pagination-next href="#siguiente" />
    </ui:pagination>
</ui:container>
```

Cómo se compone con el layout (las páginas *stream*, así que la composición la
hace el router/controller, igual que en Blade):

```php
// demo/router.php (o un controller)
$body = $ui->view('examples/blog');

echo $ui->render('layouts/app', [
    'title'   => 'Blog — Hot-UI',
    'meta'    => $ui->render('partials/meta', ['title' => 'Blog — Hot-UI']),
    'nav'     => ['Inicio' => '/', 'Artículos' => '#articulos'],
    'content' => $body,
]);
```

Detalles de la sintaxis en [`docs/sintaxis-tags.md`](sintaxis-tags.md).

## Reglas que los ejemplos demuestran

1. Los atributos de prop se escriben kebab-case y se mapean a la prop
   camelCase del componente: `is-active` → `isActive`,
   `:close-on-overlay="true"` → prop `closeOnOverlay`. (En `props()` el alias
   lo resuelve `self_kebabize` a runtime.)
2. `:tone="…"`, `:label="…"` son **expresiones PHP** evaluadas en el scope de
   la página; los helpers (`$categoryTone`, `e()`) definidos arriba están
   disponibles.
3. Los bloques `<?php … ?>` y los `foreach` se conservan tal cual; el
   compilador solo toca los tags `<ui:…>`/`<blocks:…>`.
4. El layout NO se renderiza desde la vista: `$ui->view()` produce el body y
   `$ui->render('layouts/…', ['content' => $body])` lo envuelve.

## Verificar por CLI

```bash
composer examples        # php bin/examples.php
```

Renderiza las tres páginas con el motor real (compilación de tags + layout) y
valida: doctype, título, contenido clave, inclusiones de css/js y ausencia de
fugas de código. Salida esperada:

```
OK   examples/login            9181 bytes
OK   examples/dashboard       16437 bytes
OK   examples/blog            13680 bytes
```