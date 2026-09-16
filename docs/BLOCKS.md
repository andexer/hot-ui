# HotUI — Bloques compuestos

Los **bloques** (`views/components/blocks/*.php`) son composiciones completas construidas
sobre los primitivos `ui.*`: secciones listas para montar un layout de aplicación
(sidebar, navegación, usuario, buscador…). Se invocan igual que los componentes:

```php
<?= $this->blocksNavMain(['items' => [...], 'label' => 'Plataforma']) ?>
```

Cada bloque encapsula su propia composición de `uiSidebarGroup`, `uiSidebarMenu`,
`uiCollapsible`, `uiDropdownMenu`, etc., de modo que la página solo provee datos.

**Total:** 8 bloques.

| Bloque | Método | Descripción |
|---|---|---|
| `file-tree` | `blocksFileTree()` | Árbol de archivos recursivo para la barra lateral: carpetas colapsables y archivos como botones. |
| `nav-main` | `blocksNavMain()` | Grupo del menú principal de la barra lateral con ítems colapsables y submenús. |
| `nav-projects` | `blocksNavProjects()` | Lista de proyectos en la barra lateral con menú contextual (ver, compartir, eliminar) al pasar el cursor. |
| `nav-secondary` | `blocksNavSecondary()` | Sección secundaria de la barra lateral con botones pequeños de icono y título. |
| `nav-user` | `blocksNavUser()` | Menú de usuario de la barra lateral con avatar, email y acciones de cuenta, facturación y salida. |
| `search-form` | `blocksSearchForm()` | Formulario de búsqueda para la barra lateral con input redondeado e icono de lupa. |
| `team-switcher` | `blocksTeamSwitcher()` | Selector de equipos para la barra lateral: dropdown con nombre, plan, atajos y opción de añadir equipo. |
| `version-switcher` | `blocksVersionSwitcher()` | Selector de versiones de documentación en dropdown con check sobre la versión activa. |

---

## `blocksFileTree()`

Árbol de archivos recursivo para la barra lateral: carpetas colapsables y archivos como botones.

**Props:**

- `item` (mixed|null, defecto `null`) — Array o string; el primer elemento es el nombre y el resto son hijos que se renderizan recursivamente

## `blocksNavMain()`

Grupo del menú principal de la barra lateral con ítems colapsables y submenús.

**Props:**

- `items` (array, defecto `[]`) — Array [{title, icon?, isActive?, items?: [{title}]}] que genera botones colapsables con submenú
- `label` (string, defecto `'Platform'`) — Texto de la etiqueta del grupo; por defecto 'Platform'

## `blocksNavProjects()`

Lista de proyectos en la barra lateral con menú contextual (ver, compartir, eliminar) al pasar el cursor.

**Props:**

- `projects` (array, defecto `[]`) — Array de proyectos [{name, icon}] donde icon es un nombre lucide
- `label` (string, defecto `'Projects'`) — Etiqueta del grupo; por defecto 'Projects'

## `blocksNavSecondary()`

Sección secundaria de la barra lateral con botones pequeños de icono y título.

**Props:**

- `items` (array, defecto `[]`) — Array de ítems [{title, icon}] con tooltip; icon es nombre lucide

## `blocksNavUser()`

Menú de usuario de la barra lateral con avatar, email y acciones de cuenta, facturación y salida.

**Props:**

- `name` (string, defecto `'shadcn'`) — Nombre visible del usuario; por defecto 'shadcn'
- `email` (string, defecto `'m@example.com'`) — Email visible; por defecto 'm@example.com'
- `avatar` (string, defecto `''`) — URL de la imagen de avatar; vacío usa fallback
- `fallback` (string, defecto `'CN'`) — Iniciales si no hay imagen; por defecto 'CN'
- `align` (string, defecto `'end'`) — Alineación del dropdown: start/center/end; por defecto 'end'

## `blocksSearchForm()`

Formulario de búsqueda para la barra lateral con input redondeado e icono de lupa.

*Sin props; solo slot y atributos.*

## `blocksTeamSwitcher()`

Selector de equipos para la barra lateral: dropdown con nombre, plan, atajos y opción de añadir equipo.

**Props:**

- `teams` (array, defecto `[]`) — Array de equipos [{name, plan, logo}] donde logo es nombre de icono lucide

## `blocksVersionSwitcher()`

Selector de versiones de documentación en dropdown con check sobre la versión activa.

**Props:**

- `versions` (array, defecto `[]`) — Array de cadenas de versión, p. ej. ['1.0','2.0']
- `default` (mixed|null, defecto `null`) — Versión preseleccionada; si es null usa la primera de versions

---

## Ejemplo: ensamblar un sidebar completo

```php
<?= ui()->uiSidebarProvider([], function (): void { ?>
    <?= ui()->uiSidebar([], function (): void { ?>
        <?= ui()->uiSidebarHeader([], ui()->blocksTeamSwitcher(['teams' => $teams])) ?>
        <?= ui()->uiSidebarContent([], function (): void { ?>
            <?= ui()->blocksSearchForm() ?>
            <?= ui()->blocksNavMain(['items' => $mainItems]) ?>
            <?= ui()->blocksNavProjects(['projects' => $projects]) ?>
            <?= ui()->blocksNavSecondary(['items' => $secondaryItems]) ?>
        <?php }) ?>
        <?= ui()->uiSidebarFooter([], ui()->blocksNavUser(['name' => $user['name'], 'email' => $user['email']])) ?>
        <?= ui()->uiSidebarRail() ?>
    <?php }) ?>
    <?= ui()->uiSidebarInset(ui()->slot($page)) ?>
<?php }) ?>
```

Los arrays de items siguen la forma documentada en los props de cada bloque; todos los
iconos son nombres Lucide y las URLs pasan siempre por `safe_url()`.
