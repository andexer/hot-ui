# Reactividad (Livewire-style)

Hot-UI añade una capa de **componentes reactivos** sobre el renderer de
siempre: estado público PHP, acciones por POST y actualización del DOM **sin
recargar la página** ni escribir una sola línea de JavaScript.

Es el mismo patrón que popularizó Livewire, pero nativo de PHP / CodeIgniter 4:

```
┌──────────────┐   HTML + snapshot firmado   ┌───────────────────────────┐
│  Navegador   │ ───────────────────────────▶ │  PHP (tu componente)      │
│  (js/app.js) │ ◀─────────────────────────── │  + Snapshot::verify()     │
│  morphdom    │   fragmento + snapshot nuevo │                          │
└──────────────┘                              └───────────────────────────┘
```

## Qué se necesita (2 minutos)

**1. Clave de firma** en tu `.env` (se usa para firmar/verificar el estado
incrustado en el DOM; sin ella la capa reactiva se niega a arrancar):

```env
HOTUI_SNAPSHOT_KEY=una-frase-larga-y-aleatoria
```

**2. Una ruta para el round-trip** (`app/Config/Routes.php`):

```php
$routes->post('hot-ui/update', 'Components\Ci4\Http\LivewireController::update');
```

Si el filtro global de CSRF protege los POST, excluye esta ruta (el driver
envía JSON, no un formulario con token):

```php
// app/Config/Filters.php
public array $globals = ['except' => ['csrf', 'hot-ui/update']];
```

**3.** Ya en tu layout los assets de siempre (no hace falta nada más):

```html
<link rel="stylesheet" href="/css/hot-ui.min.css">
<script type="module" src="/js/app.js"></script>
```

## Tu primer componente

`app/Components/Counter.php`:

```php
namespace App\Components;

use Components\Reactivity\Component;

final class Counter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = $this->count;              // inicialízalo como quieras
    }

    public function increment(): void
    {
        $this->count++;
    }
}
```

`app/Views/hotui/components/live/counter.php` (su plantilla — el motor renderiza
directivas `hot:*` y te expone `$component`):

```php
<button hot:click="increment">Contar (<?= $component->count ?>)</button>
<input hot:model="count" value="<?= $component->count ?>">
<div hot:poll="5000">pulso cada 5 s</div>
```

En el controller:

```php
use App\Components\Counter;
use Components\Ci4\Ci4;

public function demo(): string
{
    $page = Ci4::render('layouts/app', [
        'title'   => 'Contador',
        'content' => Ci4::live(new Counter()),
    ]);

    return $this->response->setBody($page);
}
```

Si no mueves `components/` a `app/Views/hotui`, apunta el motor a tu copia:

```php
Components\Ci4\Ci4::boot(APPPATH.'Views/hotui');
```

## Directivas `hot:*`

| Directiva | Se convierte en | Qué hace el driver |
|---|---|---|
| `hot:click="método"` | `data-hot-click` | POST `{método}` tras verificar el snapshot |
| `hot:model="propiedad"` | `data-hot-model` | POST el `value` del input → casteado según el tipo PHP |
| `hot:poll="ms"` | `data-hot-poll` | Re-render cada `ms` (y re-firma) |

Cualquier atributo `hot:*` desconocido se deja intacto para que tú definas tu
propia semántica.

## Cómo funciona

1. `Ci4::live()` (o `Engine::render()`) renderiza la plantilla y **firma el
   estado** (clase + propiedades públicas) con HMAC-SHA256; el resultado queda
   incrustado en el DOM:

   ```html
   <div data-hot-component
        data-hot-snapshot="…firmado…"
        data-hot-checksum="…"
        data-hot-action="/hot-ui/update">
       …fragmento…
   </div>
   ```

2. El driver de `js/app.js` delega click/change/input/poll, envía
   `{snapshot, action}` como JSON y **morpha** el fragmento devuelto en su
   sitio (Alpine ya inicializado dentro se re-inicializa vía `initTree`).

3. `Engine::call()` verifica el checksum (`hash_equals`), hidrata el
   componente, **corre la acción** (método público o actualización de modelo
   con casteo `int`/`float`/`bool`) y devuelve `{html, snapshot}` con un
   snapshot **fresco**.

El estado no vive en sesión ni en servidor: va firmado en el DOM, así que las
pestañas múltiples y el escalado horizontal funcionan igual que en Livewire.

### Seguridad

- El snapshot está firmado con HMAC-SHA256 usando `HOTUI_SNAPSHOT_KEY`; si se
  manipula, el servidor responde `422`.
- Solo se ejecutan **métodos públicos** y no los reservados del framework
  (`mount`, `hydrate`, `updated`, `state`, …).
- Los `params` de una acción se pasan como argumentos posicionales del método;
  valida tú siempre la entrada del cliente en tus métodos.
- `data-hot-action` la firma el servidor; el driver la usa tal cual.

## API

| Clase | Método | Qué hace |
|---|---|---|
| `Components\Reactivity\Component` | hooks `mount()`, `updated()`, `booted()` | Ciclo de vida del componente |
| `Components\Reactivity\Snapshot` | `encode()`, `decode()` | Firma/verifica el estado (HMAC-SHA256) |
| `Components\Reactivity\Engine` | `render()`, `call()`, `fragment()` | Orquesta render + round-trip |
| `Components\Reactivity\HtmlTransform` | `apply()` | Pasa `hot:*` → `data-hot-*` |
| `Components\Ci4\Http\LivewireController` | `update()` | Endpoint POST del round-trip |
| `Ci4::live($component)` | — | Renderiza el fragmento listo para el driver |

## Cómo añadir el endpoint si NO usas CI4

`Engine::call()` es agnóstico: solo necesita un POST que reciba
`{snapshot, action}` en JSON y devuelva `{html, snapshot}`. En cualquier
framework haz `Engine::call($body['snapshot'], $body['action'], 'tu/update')`
y devuelve el JSON.