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