# Layouts y partials

Fragmentos de aplicación que acompañan al paquete. No son componentes: no
entran en el registry, reciben datos crudos y devuelven HTML.

## Layouts (`views/layouts/`)

Páginas maestras listas para `$ui->render()`:

| Layout | Uso | Variables |
|---|---|---|
| `layouts/app` | shell autenticado con header/nav | `content`, `title`, `meta`, `analytics`, `nav` (label→href) |
| `layouts/guest` | tarjeta centrada (login/registro) | `content`, `title`, `meta`, `analytics` |
| `layouts/admin` | panel denso con sidebar | `content`, `title`, `meta`, `analytics`, `user` |
| `layouts/error` | página de estado full-screen | `code`, `message`, `backUrl`, `title`, `meta` |

```php
$ui->render('layouts/app', [
    'content'   => $ui->render('mi/pagina'),
    'meta'      => $ui->render('partials/meta', ['title' => 'Inicio']),
    'nav'       => ['Inicio' => '/', 'Perfil' => '/perfil'],
]);
```

## Partials (`views/partials/`)

| Partial | Variables | Salida |
|---|---|---|
| `partials/meta` | `title`, `description?`, `canonical?`, `indexable?` | charset, viewport, title, robots, canonical |
| `partials/analytics` | `analyticsId?`, `scriptUrl?` | gtag loader (nada si no hay id) |
| `partials/sidebar-widget` | `heading`, `actionUrl?`, `content` | widget de card para sidebars (body como string HTML en `content`) |

Dentro de otra plantilla de Hot-UI también puedes usar
`$this->insert('partials/meta', ['title' => '…'])` (el renderer proporciona
`insert()`/`fetch()`), o `ui()->render(...)`.

## Convención blocks vs partials

- `components/blocks/`: componente con contrato (props/bag/slots), registrado,
  reutilizable entre proyectos.
- `partials/`: pegamento privado de tu app; acoplado a tu dominio y medidas.
