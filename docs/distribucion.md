# Distribución (Packagist)

Hot-UI se distribuye como paquete Composer desde
[packagist.org](https://packagist.org) con nombre `hot-ui/hot-ui`.

> **Fase beta**: la versión actual es `v0.9.x`. Semver trata `0.x` como
> estable para Composer (pre-`v1`), por eso `composer require hot-ui/hot-ui`
> funciona sin flags. La validación exhaustiva en proyectos reales ocurre
> durante esta fase; `v1` marca la API estable definitiva.

## Dar de alta el paquete (una vez, cuenta propia)

1. Crea una cuenta en <https://packagist.org> (usa el correo del autor:
   `arvelofalcon@gmail.com`). Packagist y GitHub son cuentas independientes;
   es mejor que el campo "Real name" coincida con GitHub para ordenar la
   atribución.
2. Verifica tu correo y entra en *"Submit"* del menú superior.
3. Pega la URL del repositorio: <https://github.com/andexer/hot-ui>.
4. Packagist hará el *hook* de actualización automático: cada tag nuevo que
   se pushee a GitHub se refleja en el paquete sin acciones extra.

## Versiones que verás en Packagist

| Tag | Estabilidad Composer | Instalación |
|---|---|---|
| `v0.9.0` | estable | `composer require hot-ui/hot-ui` |
| `v0.9.0-beta.1` | prerelease | `composer require "hot-ui/hot-ui:@beta"` |

## Verificación posterior al alta

Desde la raíz de un app real (p. ej. tu CI4 `~/D/my-ci4-site`):

```bash
composer config allow-plugins.hot-ui/hot-ui true   # autoriza el plugin (una vez)
composer require hot-ui/hot-ui                     # v0.9.x
ls public/css public/js                            # el plugin ya publicó
```

Detalles que comprobar:

- `vendor/hot-ui/hot-ui` existe y contiene `src/`, `views/`, `css/`, `js/`.
- No aparece `node_modules/`, `docs/`, `tests/` ni fuentes `.ts` (los excluye
  `.gitattributes` `export-ignore`).
- `public/css/hot-ui.css` y `public/js/app.js` se crearon solos (plugin de
  Composer; ver `README.md` → "Uso en CodeIgniter 4").
- Un controller con `Ci4::view()` / `Ci4::render()` sirve una página con
  componentes.

## Si aún no está en Packagist (workaround VCS)

Mientras no esté dado de alta — o para probar commits no taggeados:

```bash
composer config repositories.hot-ui vcs https://github.com/andexer/hot-ui
composer require hot-ui/hot-ui:v0.9.0     # o dev-main@dev para el último commit
```

`v0.9.0` es estable, por lo que también funciona `composer require hot-ui/hot-ui`
con este repositorio declarado.

## Cambios que invalidan el paquete

- No se toca `composer.json` con `"version"`: las versiones salen de los tags.
- Los tags **nunca se reescriben**; los fixes salen en `v0.9.x` siguientes.
- `packagist.org/packages/hot-ui/hot-ui` muestra el estado del hook; si una
  release "no se ve", haz click en *"Unresolve"*→*"Resolve"* o revisa que el
  hook de GitHub apunte al repo correcto.