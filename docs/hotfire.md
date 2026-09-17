# Hotfire

Hotfire is the reactive component layer of Hot-UI: server-side state, actions
over HTTP and in-place DOM updates — no page reloads, no JavaScript to write.

It is a pure server-driven state pattern — native PHP / CodeIgniter 4, no
JavaScript to write beyond the bundled driver.

```
┌──────────────┐   HTML + signed snapshot   ┌────────────────────────────┐
│  Browser     │ ──────────────────────────▶ │  PHP (your component)      │
│  (js/app.js) │ ◀────────────────────────── │  + Snapshot::verify()      │
│  morphdom    │   fragment + fresh snapshot │                           │
└──────────────┘                             └────────────────────────────┘
```

## Setup (2 minutes)

**1. Signing key** in your `.env` (used to sign/verify the state embedded in
the DOM; Hotfire refuses to run without one):

```env
HOTUI_SNAPSHOT_KEY=a-long-and-random-phrase
```

**2. A route for the round-trip** (`app/Config/Routes.php`):

```php
$routes->post('hot-ui/update', 'Components\Ci4\Http\HotfireController::update');
```

If the global CSRF filter protects POST routes, exclude this one (the driver
sends JSON, not a form CSRF token):

```php
// app/Config/Filters.php
public array $globals = ['except' => ['csrf', 'hot-ui/update']];
```

**3.** Your layout already loads the bundled assets — nothing else is needed:

```html
<link rel="stylesheet" href="/css/hot-ui.min.css">
<script type="module" src="/js/app.js"></script>
```

## Your first component

`app/Components/Counter.php`:

```php
namespace App\Components;

use Components\Hotfire\Component;

final class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
}
```

`app/Views/components/hotfire/counter.php` (its template — the engine renders
`hot:*` directives and exposes `$component`):

```php
<button hot:click="increment">Count (<?= $component->count ?>)</button>
<input hot:model="count" value="<?= $component->count ?>">
<div hot:poll="5000">pulse every 5 s</div>
```

In the controller:

```php
use App\Components\Counter;
use Components\Ci4\Ci4;

public function demo(): string
{
    $page = Ci4::render('layouts/app', [
        'title'   => 'Counter',
        'content' => Ci4::hotfire(new Counter()),
    ]);

    return $this->response->setBody($page);
}
```

If the Hot-UI views live in the native folder (`app/Views`, after
`php spark hot-ui:publish views`), point the engine at your copy:

```php
Components\Ci4\Ci4::boot(APPPATH.'Views');
```

## Scaffolding with `make:hotfire`

The package ships three `spark` commands (auto-discovered by CodeIgniter, no
`Config/Commands.php` needed — any class under `Components\Commands` extending
`BaseCommand` is picked up):

```console
php spark make:hotfire post.create --mfc
php spark make:hotfire-view post.create --props="title,content"
php spark list:hotfire
```

Both accept a component name as dotted or slashed segments
(`post.create`, `post/create`) — or a camelCase/PascalCase one (`BottomLogout`) —
and write a skeleton you edit next.

Every artifact of a component cohabits **one self-contained folder** marked with
the 🔥 indicator (like Livewire 4's ⚡ "voltage" folders): the marker is purely
visual and is always the emoji-prefixed, kebab-cased leaf segment —
`BottomLogout` → `🔥bottom-logout/`. For `post.create`:

```
app/Views/components/hotfire/post/🔥create/
├── create.php            # PHP class (extends Components\Hotfire\Component)
├── create.view.php       # Blade-less template ($component + hot:* directives)
├── create.js             # Scoped JavaScript (optional, --js)
├── create.css            # Scoped styles (optional, --css)
├── create.global.css     # Global styles (optional, --global-css)
└── create.test.php       # PHPUnit test (optional, --test)
```

| Artifact | File (`post.create`) |
|---|---|
| Component class | `…/post/🔥create/create.php` |
| Template (view) | `…/post/🔥create/create.view.php` |
| Scoped JS (`--js`) | `…/post/🔥create/create.js` |
| Scoped CSS (`--css`) | `…/post/🔥create/create.css` |
| Global CSS (`--global-css`) | `…/post/🔥create/create.global.css` |
| PHPUnit test (`--test`) | `…/post/🔥create/create.test.php` |

Options:

- `--props="title,content"` — declares public `string` state and a `save()`
  action + matching form template.
- `--mfc` — shortcut for `--props="title,content"` (form scaffold).
- `--namespace="Blog\Posts"` — root namespace of the generated class;
  default `App\Components` — which the autoloader registered by `Ci4::boot()`
  resolves straight from the component's 🔥 folder, no `Config/Autoload.php`
  edits needed. Quote the value so the shell keeps the backslashes.
- `--views="/abs/path"` — views folder override (default `APPPATH.'Views'`).
- `--emoji="⚡"` — visual marker on the component folder; default `🔥`. Keep it
  directory-safe (no `/`, `\` or `.`).
- `--js`, `--css`, `--global-css` — also generate the scoped JavaScript, scoped
  stylesheet and global stylesheet sidecars.
- `--test` — also generate a PHPUnit test, collocated in the component folder.
- `--force` — overwrite existing files.

`make:hotfire` writes the class **and** the template; `make:hotfire-view`
writes only the template (for components whose class already exists).

The generated class pins its template, so it renders regardless of where the
component lives:

```php
namespace App\Components\Post;

final class Create extends Component
{
    protected string $view = 'components/hotfire/post/🔥create/create.view';

    public string $title  = '';
    public string $content = '';

    public function save(): void
    {
        // validate + persist here
    }
}
```

The generated `--test` scaffolds `CIUnitTestCase` coverage for the signed
render, a `model` round-trip and tamper rejection:

```console
vendor/bin/phpunit app/Views/components/hotfire/post/🔥create/create.test.php
```

### Listing components with `list:hotfire`

Every scaffolded component lives in its own 🔥-marked folder, so the whole
catalog is discoverable straight from disk — no registry, no config. The third
spark command walks `<views>/components/hotfire`, maps each marked folder back
to its component name and reports what it holds:

```console
php spark list:hotfire
php spark list:hotfire --simple                       # names only
php spark list:hotfire --views="/abs/path" --emoji="⚡"
```

```text
2 Hotfire components under /path/to/app/Views/components/hotfire:

bottom-logout
  class     …/components/hotfire/🔥bottom-logout/bottom-logout.php
  template  …/components/hotfire/🔥bottom-logout/bottom-logout.view.php
  sidecar   …/components/hotfire/🔥bottom-logout/bottom-logout.test.php

post.create
  class     …/components/hotfire/post/🔥create/create.php
  template  …/components/hotfire/post/🔥create/create.view.php
  sidecar   …/components/hotfire/post/🔥create/create.js
```

Names are sorted; files unrelated to any component are ignored, and folders
missing their class or template are listed with `(missing)` so half-scaffolded
components stand out. The discovery itself is `ComponentPaths::discover()` —
framework-free and render-free (no autoloading involved), so you can reuse it
from your own tooling.

### Customizing the stubs

The generators never embed templates in code: every artifact is produced from
a plain `*.stub` file under `src/Components/Hotfire/templates/` and tokens are
the only dynamic part (`{{class}}`, `{{namespace}}`, `{{propsAndSave}}`,
`{{fields}}`, …). To bend the scaffold to your house style, override any stub
(they ship with the package) — or pass your own folder via the
`$templatesDir` constructor argument:

```php
new Components\Hotfire\ComponentGenerator($viewsRoot, $namespace, null, '/path/to/your/stubs');
```

Stub files don't need to be valid PHP/JS/CSS on their own; unknown
placeholders are left untouched, so partial stubs keep working.

## Directives `hot:*`

| Directive | Becomes | Driver behaviour |
|---|---|---|
| `hot:click="method"` | `data-hot-click` | POST `{method}` after snapshot verification |
| `hot:model="property"` | `data-hot-model` | POST the input `value` → cast to the PHP type |
| `hot:poll="ms"` | `data-hot-poll` | Re-render every `ms` (and re-sign) |

Unknown `hot:*` attributes are left untouched so you can build your own
semantics on top.

## Configuration

Everything that used to be hardcoded is now driven by
`Components\Hotfire\Config` (a process-wide instance, installable in your
bootstrap with `Config::setShared($config)`):

| Setting | Default | Purpose |
|---|---|---|
| `endpoint` | `hot-ui/update` | URL the driver posts to |
| `viewPrefix` | `components/hotfire` | Folder for component templates without an explicit one |
| `snapshotKey` / `snapshotKeyEnv` | env `HOTUI_SNAPSHOT_KEY` | Signing key source |
| `directives` | `click, model, poll, change, key` | `hot:*` → `data-hot-*` map |
| `reserved` | `mount, booted, updated, ...` | Framework methods never callable as actions |

```php
use Components\Hotfire\Config;

Config::setShared(new Config(
    endpoint: 'hotfire/update',
    viewPrefix: 'sections',
));
```

## How it works

1. `Ci4::hotfire()` (or `Engine::render()`) renders the template and **signs
   the state** (class + public properties) with HMAC-SHA256; the result lives
   in the DOM:

   ```html
   <div data-hot-component
        data-hot-snapshot="…signed…"
        data-hot-checksum="…"
        data-hot-action="/hot-ui/update">
       …fragment…
   </div>
   ```

2. The driver in `js/app.js` delegates click/change/input/poll, POSTs
   `{snapshot, action}` as JSON and **morphs** the returned fragment into
   place (Alpine islands inside are re-initialized via `initTree`).

3. `Engine::call()` verifies the checksum (`hash_equals`), hydrates the
   component, **runs the action** (public method, model update with
   `int`/`float`/`bool` casting, or a poll refresh) and returns
   `{html, snapshot}` with a fresh snapshot.

State never lives in the session: it travels signed in the DOM, so multiple
tabs and horizontal scaling work exactly like the pattern they're inspired by.

### Security

- Snapshots are signed with HMAC-SHA256 using the configured key; tampered
  payloads get `422`.
- Only **public methods** can run, and only those not in the `reserved` list.
- Action `params` are passed positionally; always validate client input inside
  your own methods.
- `data-hot-action` is stamped by the server; the driver uses it verbatim.

## API

| Class | Method | Purpose |
|---|---|---|
| `Components\Hotfire\Config` | `shared()`, `setShared()`, getters | All engine settings |
| `Components\Hotfire\Component` | hooks `mount()`, `updated()`, `booted()` | Component lifecycle |
| `Components\Hotfire\Snapshot` | `encode()`, `decode()` | Sign/verify state (HMAC-SHA256) |
| `Components\Hotfire\Engine` | `render()`, `call()`, `fragment()` | Orchestrates render + round-trip |
| `Components\Hotfire\HtmlTransform` | `apply()` | Passes `hot:*` → `data-hot-*` |
| `Components\Ci4\Http\HotfireController` | `update()` | POST round-trip endpoint |
| `Ci4::hotfire($component)` | — | Renders the driver-ready fragment |

## Without CodeIgniter 4

`Engine::call()` is engine-agnostic: it only needs a POST endpoint that receives
`{snapshot, action}` as JSON and returns `{html, snapshot}`. In any framework,
call `Engine::call($body['snapshot'], $body['action'], 'your/update')` and
return the JSON. Use Config to point at your own endpoint before that.