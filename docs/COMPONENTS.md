# HotUI — Componentes UI

Referencia completa de los componentes del namespace **ui**. Cada archivo
`views/components/ui/<nombre>.php` se invoca como método dinámico del helper `ui()`
o dentro de plantillas vía `$this->ui<Nombre>(...)`.

```php
<?= ui()->uiButton(['variant' => 'outline', 'size' => 'sm'], 'Cancelar') ?>
```

Convenciones aplicadas a toda la familia:

- **Primer argumento**: array de props con nombre. Las claves no declaradas viajan al
  `AttributeBag` y se imprimen como atributos HTML escapados.
- **Segundo argumento en adelante**: contenido del slot por defecto (string, `Stringable`
  o closure que imprime HTML).
- **Slots nombrados**: argumentos con nombre (`leading:`, `trailing:` …) cuando el
  componente los declara.
- URLs siempre saneadas con `safe_url()`; todo texto dinámico pasa por `e()`.

**Total:** 376 componentes en 166 familias.

## Índice

- [Accent](familia-accent) — 1 componente
- [Accordion](familia-accordion) — 4 componentes
- [Add-to-cart](familia-add-to-cart) — 1 componente
- [Alert](familia-alert) — 4 componentes
- [Alert-dialog](familia-alert-dialog) — 9 componentes
- [Animated-beam](familia-animated-beam) — 1 componente
- [Aspect-ratio](familia-aspect-ratio) — 1 componente
- [Audio-player](familia-audio-player) — 1 componente
- [Aurora](familia-aurora) — 1 componente
- [Autocomplete](familia-autocomplete) — 1 componente
- [Autosize-textarea](familia-autosize-textarea) — 1 componente
- [Avatar](familia-avatar) — 4 componentes
- [Back-to-top](familia-back-to-top) — 1 componente
- [Badge](familia-badge) — 1 componente
- [Banner](familia-banner) — 1 componente
- [Bento-grid](familia-bento-grid) — 1 componente
- [Bento-item](familia-bento-item) — 1 componente
- [Border-beam](familia-border-beam) — 1 componente
- [Bottom-navigation](familia-bottom-navigation) — 2 componentes
- [Brand](familia-brand) — 1 componente
- [Breadcrumb](familia-breadcrumb) — 7 componentes
- [Button](familia-button) — 1 componente
- [Button-group](familia-button-group) — 3 componentes
- [Calendar](familia-calendar) — 1 componente
- [Card](familia-card) — 7 componentes
- [Carousel](familia-carousel) — 5 componentes
- [Chart](familia-chart) — 3 componentes
- [Chat](familia-chat) — 2 componentes
- [Checkbox](familia-checkbox) — 1 componente
- [Citation](familia-citation) — 1 componente
- [Code-block](familia-code-block) — 1 componente
- [Collapsible](familia-collapsible) — 3 componentes
- [Color-picker](familia-color-picker) — 1 componente
- [Combobox](familia-combobox) — 1 componente
- [Command](familia-command) — 9 componentes
- [Comparison-slider](familia-comparison-slider) — 1 componente
- [Comparison-table](familia-comparison-table) — 1 componente
- [Confetti](familia-confetti) — 1 componente
- [Container](familia-container) — 1 componente
- [Context-menu](familia-context-menu) — 14 componentes
- [Cookie-consent](familia-cookie-consent) — 1 componente
- [Copy-button](familia-copy-button) — 1 componente
- [Countdown](familia-countdown) — 1 componente
- [Data-table](familia-data-table) — 1 componente
- [Date-picker](familia-date-picker) — 1 componente
- [Datetime-picker](familia-datetime-picker) — 1 componente
- [Description-list](familia-description-list) — 2 componentes
- [Dialog](familia-dialog) — 8 componentes
- [Diff-viewer](familia-diff-viewer) — 1 componente
- [Dock](familia-dock) — 2 componentes
- [Dot-pattern](familia-dot-pattern) — 1 componente
- [Drawer](familia-drawer) — 8 componentes
- [Dropdown-menu](familia-dropdown-menu) — 14 componentes
- [Editable](familia-editable) — 1 componente
- [Empty](familia-empty) — 6 componentes
- [Field](familia-field) — 10 componentes
- [File-upload](familia-file-upload) — 1 componente
- [Flip-card](familia-flip-card) — 1 componente
- [Gallery](familia-gallery) — 1 componente
- [Gantt](familia-gantt) — 1 componente
- [Gradient-text](familia-gradient-text) — 1 componente
- [Grid-pattern](familia-grid-pattern) — 1 componente
- [Heatmap](familia-heatmap) — 1 componente
- [Hover-card](familia-hover-card) — 3 componentes
- [Icon](familia-icon) — 1 componente
- [Image](familia-image) — 1 componente
- [Infinite-scroll](familia-infinite-scroll) — 1 componente
- [Input](familia-input) — 2 componentes
- [Input-group](familia-input-group) — 5 componentes
- [Input-otp](familia-input-otp) — 4 componentes
- [Item](familia-item) — 8 componentes
- [Json-viewer](familia-json-viewer) — 2 componentes
- [Kanban](familia-kanban) — 1 componente
- [Kbd](familia-kbd) — 2 componentes
- [Knob](familia-knob) — 1 componente
- [Label](familia-label) — 1 componente
- [Link](familia-link) — 1 componente
- [Loading-overlay](familia-loading-overlay) — 1 componente
- [Map](familia-map) — 1 componente
- [Markdown-editor](familia-markdown-editor) — 1 componente
- [Marquee](familia-marquee) — 1 componente
- [Masonry](familia-masonry) — 1 componente
- [Mention-input](familia-mention-input) — 1 componente
- [Menu-checkbox-item](familia-menu-checkbox-item) — 1 componente
- [Menu-group](familia-menu-group) — 1 componente
- [Menu-item](familia-menu-item) — 1 componente
- [Menu-label](familia-menu-label) — 1 componente
- [Menu-radio-item](familia-menu-radio-item) — 1 componente
- [Menu-separator](familia-menu-separator) — 1 componente
- [Menu-shortcut](familia-menu-shortcut) — 1 componente
- [Menubar](familia-menubar) — 15 componentes
- [Meteors](familia-meteors) — 1 componente
- [Meter](familia-meter) — 1 componente
- [Mini-cart](familia-mini-cart) — 1 componente
- [Navigation-menu](familia-navigation-menu) — 6 componentes
- [Notification-center](familia-notification-center) — 1 componente
- [Number-input](familia-number-input) — 1 componente
- [Number-ticker](familia-number-ticker) — 1 componente
- [Onboarding-tour](familia-onboarding-tour) — 1 componente
- [Org-chart](familia-org-chart) — 2 componentes
- [Page-header](familia-page-header) — 1 componente
- [Pagination](familia-pagination) — 7 componentes
- [Parallax](familia-parallax) — 1 componente
- [Password-strength](familia-password-strength) — 1 componente
- [Phone-input](familia-phone-input) — 1 componente
- [Popover](familia-popover) — 3 componentes
- [Presence](familia-presence) — 1 componente
- [Price](familia-price) — 1 componente
- [Product-card](familia-product-card) — 1 componente
- [Profile](familia-profile) — 1 componente
- [Progress](familia-progress) — 1 componente
- [Prompt-input](familia-prompt-input) — 1 componente
- [Qr-code](familia-qr-code) — 1 componente
- [Quote](familia-quote) — 1 componente
- [Radio-group](familia-radio-group) — 2 componentes
- [Rating](familia-rating) — 1 componente
- [Reasoning](familia-reasoning) — 1 componente
- [Repeater](familia-repeater) — 1 componente
- [Resizable-panel](familia-resizable-panel) — 1 componente
- [Resizable-panel-group](familia-resizable-panel-group) — 2 componentes
- [Rich-text-editor](familia-rich-text-editor) — 1 componente
- [Scheduler](familia-scheduler) — 1 componente
- [Scroll-area](familia-scroll-area) — 1 componente
- [Scrollspy](familia-scrollspy) — 1 componente
- [Segmented-control](familia-segmented-control) — 1 componente
- [Select](familia-select) — 8 componentes
- [Separator](familia-separator) — 1 componente
- [Server-table](familia-server-table) — 1 componente
- [Sheet](familia-sheet) — 7 componentes
- [Sidebar](familia-sidebar) — 10 componentes
- [Sidebar-group](familia-sidebar-group) — 3 componentes
- [Sidebar-menu](familia-sidebar-menu) — 8 componentes
- [Signature-pad](familia-signature-pad) — 1 componente
- [Skeleton](familia-skeleton) — 1 componente
- [Slider](familia-slider) — 1 componente
- [Sonner](familia-sonner) — 2 componentes
- [Sparkline](familia-sparkline) — 1 componente
- [Speed-dial](familia-speed-dial) — 1 componente
- [Spinner](familia-spinner) — 1 componente
- [Spotlight-card](familia-spotlight-card) — 1 componente
- [Stack](familia-stack) — 1 componente
- [Stat](familia-stat) — 1 componente
- [Stepper](familia-stepper) — 9 componentes
- [Streaming-text](familia-streaming-text) — 1 componente
- [Switch](familia-switch) — 1 componente
- [Table](familia-table) — 8 componentes
- [Tabs](familia-tabs) — 4 componentes
- [Tags-input](familia-tags-input) — 1 componente
- [Terminal](familia-terminal) — 1 componente
- [Text-reveal](familia-text-reveal) — 1 componente
- [Textarea](familia-textarea) — 1 componente
- [Tilt-card](familia-tilt-card) — 1 componente
- [Time-field](familia-time-field) — 1 componente
- [Timeline](familia-timeline) — 2 componentes
- [Toggle](familia-toggle) — 1 componente
- [Toggle-group](familia-toggle-group) — 2 componentes
- [Tool-call](familia-tool-call) — 1 componente
- [Tooltip](familia-tooltip) — 3 componentes
- [Top-progress](familia-top-progress) — 1 componente
- [Tree](familia-tree) — 2 componentes
- [Tree-table](familia-tree-table) — 2 componentes
- [Typewriter](familia-typewriter) — 1 componente
- [Typography](familia-typography) — 1 componente
- [Variant-selector](familia-variant-selector) — 1 componente
- [Video](familia-video) — 1 componente
- [Visually-hidden](familia-visually-hidden) — 1 componente

---

## Familia `accent`

> Recolorea los tokens de tema (--primary, --ring, etc.) solo en su subárbol; renderiza como display:contents.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `accent` | `uiAccent()` | Recolorea los tokens de tema (--primary, --ring, etc.) solo en su subárbol; renderiza como display:contents. | `color` (mixed\|null, defecto `null`) — Cualquier color CSS que define el acento local del subárbol<br>`foreground` (mixed\|null, defecto `null`) — Color de texto sobre acentos rellenos; por defecto #ffffff<br>*Slot por defecto:* sí. |

## Familia `accordion`

> Raíz del acordeón (isla hotAccordion): gestión single/multiple, colapsable y navegación por teclado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `accordion` | `uiAccordion()` | Raíz del acordeón (isla hotAccordion): gestión single/multiple, colapsable y navegación por teclado. | `type` (string, defecto `'single'`) — 'single' (por defecto) o 'multiple'<br>`collapsible` (bool, defecto `false`) — Booleano; permite cerrar todos los paneles en modo single<br>`value` (mixed\|null, defecto `null`) — Valor(es) inicial(es) abierto(s): cadena en single, array en multiple<br>*Slot por defecto:* sí. |
| `accordion-content` | `uiAccordionContent()` | Panel plegable del acordeón con animación de altura, visible solo cuando su ítem está abierto. | *Slot por defecto:* sí. |
| `accordion-item` | `uiAccordionItem()` | Contenedor de un ítem de acordeón; fija su valor único y expone el estado abierto/cerrado. | `value` (mixed\|null, defecto `null`) — Valor único que identifica el panel en el acordeón; autogenerado si falta<br>*Slot por defecto:* sí. |
| `accordion-trigger` | `uiAccordionTrigger()` | Botón disparador del acordeón con icono de expansión configurable y estado accesible. | `icon` (string, defecto `'chevron'`) — Icono toggle: chevron (defecto), chevron-updown, plus-minus, plus, chevron-left, none<br>`iconPosition` (string, defecto `'right'`) — Posición del icono: right (defecto) o left<br>*Slot por defecto:* sí. |

## Familia `add-to-cart`

> Botón añadir al carrito con máquina de estados demo (inactivo→añadiendo→añadido) y avisos aria-live.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `add-to-cart` | `uiAddToCart()` | Botón añadir al carrito con máquina de estados demo (inactivo→añadiendo→añadido) y avisos aria-live. | `label` (string, defecto `'Add to cart'`) — Texto del botón; false lo oculta (modo solo icono)<br>`addedLabel` (string, defecto `'Added'`) — Texto del estado añadido; por defecto 'Added'<br>`size` (string, defecto `'default'`) — Tamaño delegado a uiButton: default, xs, sm, lg, icon...<br>`icon` (string, defecto `'shopping-cart'`) — Nombre lucide del icono en reposo; por defecto shopping-cart<br>*Slot por defecto:* sí. |

## Familia `alert`

> Contenedor de alerta con variantes default/destructive y tonos success, warning, danger, info, neutral.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `alert` | `uiAlert()` | Contenedor de alerta con variantes default/destructive y tonos success, warning, danger, info, neutral. | `variant` (string, defecto `'default'`) — default (por defecto) o destructive; también intensidad soft/solid/outline si hay tone<br>`tone` (mixed\|null, defecto `null`) — Tono semántico: success, warning, danger, info o neutral<br>*Slot por defecto:* sí. |
| `alert-action` | `uiAlertAction()` | Área de acción opcional de la alerta (p. ej. botón de descarte), fijada arriba a la derecha. | *Slot por defecto:* sí. |
| `alert-description` | `uiAlertDescription()` | Texto descriptivo de la alerta en tono apagado, situado bajo el título. | *Slot por defecto:* sí. |
| `alert-title` | `uiAlertTitle()` | Título de la alerta en una línea con recorte (line-clamp-1). | *Slot por defecto:* sí. |

## Familia `alert-dialog`

> Raíz del alert dialog de confirmación; guarda el estado abierto y admite apertura remota por id.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `alert-dialog` | `uiAlertDialog()` | Raíz del alert dialog de confirmación; guarda el estado abierto y admite apertura remota por id. | `open` (bool, defecto `false`) — Estado inicial abierto; booleano, por defecto false<br>`id` (mixed\|null, defecto `null`) — Si se define, habilita eventos window open-alert-dialog-{id}/close-alert-dialog-{id}<br>*Slot por defecto:* sí. |
| `alert-dialog-action` | `uiAlertDialogAction()` | Botón primario de confirmación del alert dialog; ejecuta el clic del usuario y cierra el diálogo. | *Slot por defecto:* sí. |
| `alert-dialog-cancel` | `uiAlertDialogCancel()` | Botón outline de cancelación del alert dialog; cierra el diálogo sin confirmar. | *Slot por defecto:* sí. |
| `alert-dialog-content` | `uiAlertDialogContent()` | Panel modal del alert dialog con overlay, focus trap, cierre con Escape y transiciones. | *Slot por defecto:* sí. |
| `alert-dialog-description` | `uiAlertDialogDescription()` | Párrafo descriptivo del alert dialog en tono apagado. | *Slot por defecto:* sí. |
| `alert-dialog-footer` | `uiAlertDialogFooter()` | Pie del alert dialog: apila botones y los alinea a la derecha desde sm. | *Slot por defecto:* sí. |
| `alert-dialog-header` | `uiAlertDialogHeader()` | Cabecera del alert dialog que agrupa título y descripción, centrada (al inicio en sm+). | *Slot por defecto:* sí. |
| `alert-dialog-title` | `uiAlertDialogTitle()` | Título h2 del alert dialog con tipografía semibold. | *Slot por defecto:* sí. |
| `alert-dialog-trigger` | `uiAlertDialogTrigger()` | Disparador del alert dialog: abre el diálogo local o uno remoto por id vía evento dispatch. | `for` (mixed\|null, defecto `null`) — Id del alert dialog despachable a abrir; si falta, abre el del mismo ámbito<br>*Slot por defecto:* sí. |

## Familia `animated-beam`

> Haz SVG animado que une dos elementos con un destello curvo; se remide ante cambios de tamaño.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `animated-beam` | `uiAnimatedBeam()` | Haz SVG animado que une dos elementos con un destello curvo; se remide ante cambios de tamaño. | `from` (mixed\|null, defecto `null`) — Selector CSS del elemento origen dentro del componente<br>`to` (mixed\|null, defecto `null`) — Selector CSS del elemento destino<br>`curvature` (int, defecto `0`) — Curvatura en px del punto de control; por defecto 0 (recta)<br>`duration` (int, defecto `3`) — Duración en segundos del barrido; por defecto 3<br>*Slot por defecto:* sí. |

## Familia `aspect-ratio`

> Contenedor que fuerza una relación de aspecto al contenido; por defecto 1 / 1.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `aspect-ratio` | `uiAspectRatio()` | Contenedor que fuerza una relación de aspecto al contenido; por defecto 1 / 1. | `ratio` (string, defecto `'1 / 1'`) — Valor CSS aspect-ratio, p. ej. '16 / 9'; por defecto '1 / 1'<br>*Slot por defecto:* sí. |

## Familia `audio-player`

> Reproductor de audio con play/pausa, progreso, volumen, silencio y metadatos de pista.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `audio-player` | `uiAudioPlayer()` | Reproductor de audio con play/pausa, progreso, volumen, silencio y metadatos de pista. | `src` (mixed\|null, defecto `null`) — URL segura del archivo de audio<br>`title` (mixed\|null, defecto `null`) — Título de la pista mostrado sobre los controles<br>`artist` (mixed\|null, defecto `null`) — Artista o subtítulo en tono apagado<br>`autoplay` (bool, defecto `false`) — Booleano; reproduce al cargar (arranca silenciado) |

## Familia `aurora`

> Fondo decorativo aurora con blobs difuminados en movimiento detrás del contenido del slot.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `aurora` | `uiAurora()` | Fondo decorativo aurora con blobs difuminados en movimiento detrás del contenido del slot. | `colors` (mixed\|null, defecto `null`) — Array de colores CSS; null usa paleta por defecto (cyan, violeta, esmeralda, rosa)<br>`blur` (int, defecto `60`) — Desenfoque en px de la capa; por defecto 60<br>`speed` (int, defecto `12`) — Duración en segundos del ciclo de deriva; por defecto 12<br>*Slot por defecto:* sí. |

## Familia `autocomplete`

> Alias obsoleto del combobox con trigger=input: campo con lista filtrable de sugerencias.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `autocomplete` | `uiAutocomplete()` | Alias obsoleto del combobox con trigger=input: campo con lista filtrable de sugerencias. | `name` (mixed\|null, defecto `null`) — Atributo name del control<br>`options` (array, defecto `[]`) — Array de opciones filtrables del listbox<br>`value` (string, defecto `''`) — Valor/selección inicial<br>`placeholder` (string, defecto `'Search...'`) — Marcador del input; por defecto 'Search...'<br>`empty` (string, defecto `'No results found.'`) — Mensaje sin resultados; por defecto 'No results found.'<br>`size` (string, defecto `'default'`) — sm, default o lg<br>`disabled` (bool, defecto `false`) — Booleano; deshabilita el control<br>`multiple` (bool, defecto `false`) — Booleano; true activa entrada de etiquetas con chips<br>`icon` (mixed\|null, defecto `null`) — Nombre lucide de icono inicial opcional<br>`width` (string, defecto `'w-[260px]'`) — Clase de ancho; por defecto 'w-[260px]' |

## Familia `autosize-textarea`

> Textarea autoexpandible obsoleto; alias fino de uiTextarea que mapea minRows a rows.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `autosize-textarea` | `uiAutosizeTextarea()` | Textarea autoexpandible obsoleto; alias fino de uiTextarea que mapea minRows a rows. | `name` (mixed\|null, defecto `null`) — Atributo name del textarea<br>`placeholder` (mixed\|null, defecto `null`) — Texto de marcador<br>`minRows` (int, defecto `2`) — Filas mínimas; por defecto 2 (mapea a rows)<br>`maxRows` (mixed\|null, defecto `null`) — Filas máximas antes de scroll; null sin límite<br>`size` (string, defecto `'default'`) — sm, default o lg<br>`disabled` (bool, defecto `false`) — Booleano<br>`id` (mixed\|null, defecto `null`) — Id del elemento<br>`value` (mixed\|null, defecto `null`) — Valor inicial; sustituye al slot como contenido<br>*Slot por defecto:* sí. |

## Familia `avatar`

> Contenedor circular del avatar con estado de carga/error para coordinar imagen y fallback.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `avatar` | `uiAvatar()` | Contenedor circular del avatar con estado de carga/error para coordinar imagen y fallback. | *Slot por defecto:* sí. |
| `avatar-fallback` | `uiAvatarFallback()` | Respaldo del avatar (iniciales/icono) visible mientras carga la imagen o si esta falla. | *Slot por defecto:* sí. |
| `avatar-group` | `uiAvatarGroup()` | Pila superpuesta de avatares con iniciales automáticas y burbuja +N para el resto. | `avatars` (array, defecto `[]`) — Array [['src' => url?, 'name' => string?]] de avatares a mostrar<br>`max` (int, defecto `4`) — Máximo visible antes del +N; por defecto 4<br>`size` (string, defecto `'default'`) — sm, default o lg; controla caja, solape, anillo y texto |
| `avatar-image` | `uiAvatarImage()` | Imagen del avatar; se oculta al fallar la carga y comunica loaded/error al contenedor. | `src` (mixed\|null, defecto `null`) — URL segura de la imagen (validada con safe_url)<br>`alt` (string, defecto `''`) — Texto alternativo; por defecto '' |

## Familia `back-to-top`

> Botón flotante que aparece al superar el umbral de scroll y vuelve arriba suavemente.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `back-to-top` | `uiBackToTop()` | Botón flotante que aparece al superar el umbral de scroll y vuelve arriba suavemente. | `threshold` (int, defecto `300`) — Px de scroll antes de aparecer; por defecto 300<br>`variant` (string, defecto `'primary'`) — primary (relleno, defecto) o subtle (tarjeta con borde)<br>`demo` (bool, defecto `false`) — Booleano solo docs: siempre visible y en flujo |

## Familia `badge`

> Insignia compacta con variantes de marca o tonos semánticos; se convierte en enlace si hay href.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `badge` | `uiBadge()` | Insignia compacta con variantes de marca o tonos semánticos; se convierte en enlace si hay href. | `variant` (string, defecto `'default'`) — default, secondary, destructive u outline; con tone actúa como intensidad soft/solid/outline<br>`tone` (mixed\|null, defecto `null`) — success, warning, danger, info o neutral<br>`size` (string, defecto `'default'`) — sm, default o lg<br>`href` (mixed\|null, defecto `null`) — URL segura; si existe renderiza <a> en vez de <span><br>*Slot por defecto:* sí. |

## Familia `banner`

> Banda de anuncio a todo el ancho con tono, botón de descartar y persistencia en localStorage.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `banner` | `uiBanner()` | Banda de anuncio a todo el ancho con tono, botón de descartar y persistencia en localStorage. | `tone` (string, defecto `'default'`) — default, primary, info, success, warning o danger<br>`dismissible` (bool, defecto `true`) — Booleano; muestra botón de cierre, true por defecto<br>`id` (mixed\|null, defecto `null`) — Id usado como clave de persistencia junto a persist<br>`persist` (bool, defecto `false`) — Booleano; recuerda el descarte en localStorage (requiere id)<br>*Slot por defecto:* sí. |

## Familia `bento-grid`

> Rejilla bento responsiva de celdas auto-altura con 2, 3 o 4 columnas en pantallas grandes.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `bento-grid` | `uiBentoGrid()` | Rejilla bento responsiva de celdas auto-altura con 2, 3 o 4 columnas en pantallas grandes. | `columns` (int, defecto `3`) — 2, 3 (defecto) o 4 columnas en lg<br>*Slot por defecto:* sí. |

## Familia `bento-item`

> Celda bento con icono, título y descripción opcionales, y expansión de filas/columnas.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `bento-item` | `uiBentoItem()` | Celda bento con icono, título y descripción opcionales, y expansión de filas/columnas. | `title` (mixed\|null, defecto `null`) — Encabezado opcional en semibold<br>`description` (mixed\|null, defecto `null`) — Texto de apoyo opcional en tono apagado<br>`icon` (mixed\|null, defecto `null`) — Nombre lucide mostrado en tile apagado<br>`colSpan` (int, defecto `1`) — Columnas abarcadas en lg+: 1, 2 o 3<br>`rowSpan` (int, defecto `1`) — Filas abarcadas: 1 o 2<br>*Slots nombrados:* `leading`. |

## Familia `border-beam`

> Tarjeta con destello cónico giratorio recorriendo el borde; color, grosor y duración ajustables.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `border-beam` | `uiBorderBeam()` | Tarjeta con destello cónico giratorio recorriendo el borde; color, grosor y duración ajustables. | `duration` (int, defecto `6`) — Segundos por vuelta del haz; por defecto 6<br>`color` (mixed\|null, defecto `null`) — Color CSS del haz; por defecto var(--color-primary)<br>`size` (int, defecto `2`) — Grosor del haz en px; por defecto 2<br>*Slot por defecto:* sí. |

## Familia `bottom-navigation`

> Barra de navegación inferior móvil (landmark nav) con fila de ítems de igual ancho.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `bottom-navigation` | `uiBottomNavigation()` | Barra de navegación inferior móvil (landmark nav) con fila de ítems de igual ancho. | `ariaLabel` (string, defecto `'Bottom navigation'`) — Nombre accesible del nav; por defecto 'Bottom navigation'<br>*Slot por defecto:* sí. |
| `bottom-navigation-item` | `uiBottomNavigationItem()` | Ítem de barra inferior móvil: icono, etiqueta, badge (punto o contador) y estado activo. | `icon` (mixed\|null, defecto `null`) — Nombre lucide del icono superior<br>`label` (mixed\|null, defecto `null`) — Etiqueta visible bajo el icono<br>`href` (string, defecto `'#'`) — URL destino; renderiza <a>, si falta renderiza <button><br>`active` (bool, defecto `false`) — Booleano; marca el ítem como página actual<br>`badge` (mixed\|null, defecto `null`) — true = punto rojo; string/número = píldora contador<br>*Slot por defecto:* sí. |

## Familia `brand`

> Logotipo de marca enlazable con nombre; acepta URL de logo, slot propio o solo texto.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `brand` | `uiBrand()` | Logotipo de marca enlazable con nombre; acepta URL de logo, slot propio o solo texto. | `name` (mixed\|null, defecto `null`) — Nombre de la marca mostrado junto al logo<br>`href` (string, defecto `'/'`) — URL del enlace; null o false renderiza un div<br>`logo` (mixed\|null, defecto `null`) — URL de la imagen del logo<br>`alt` (mixed\|null, defecto `null`) — Texto alternativo del logo y aria-label si no hay nombre<br>*Slot por defecto:* sí. |

## Familia `breadcrumb`

> Raíz landmark nav de las migas de pan con aria-label breadcrumb.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `breadcrumb` | `uiBreadcrumb()` | Raíz landmark nav de las migas de pan con aria-label breadcrumb. | *Slot por defecto:* sí. |
| `breadcrumb-ellipsis` | `uiBreadcrumbEllipsis()` | Elipsis decorativa para migas truncadas con icono more-horizontal. | — |
| `breadcrumb-item` | `uiBreadcrumbItem()` | Elemento li que agrupa cada nivel (enlace o página) de las migas de pan. | *Slot por defecto:* sí. |
| `breadcrumb-link` | `uiBreadcrumbLink()` | Enlace de un nivel intermedio de las migas de pan. | `href` (string, defecto `'#'`) — URL del enlace; por defecto '#'<br>*Slot por defecto:* sí. |
| `breadcrumb-list` | `uiBreadcrumbList()` | Lista ordenada ol que contiene los ítems de las migas de pan. | *Slot por defecto:* sí. |
| `breadcrumb-page` | `uiBreadcrumbPage()` | Página actual de las migas de pan; no navegable y marcada con aria-current=page. | *Slot por defecto:* sí. |
| `breadcrumb-separator` | `uiBreadcrumbSeparator()` | Separador entre niveles de migas; pinta chevron-right salvo contenido propio. | *Slot por defecto:* sí. |

## Familia `button`

> Botón polimórfico (button/a/as) con variantes, tamaños y recolor local vía color.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `button` | `uiButton()` | Botón polimórfico (button/a/as) con variantes, tamaños y recolor local vía color. | `variant` (string, defecto `'default'`) — default, destructive, outline, secondary, ghost o link<br>`size` (string, defecto `'default'`) — default, xs, sm, lg, icon, icon-xs, icon-sm o icon-lg<br>`href` (mixed\|null, defecto `null`) — URL segura; convierte el botón en <a><br>`type` (string, defecto `'button'`) — Tipo del button nativo; por defecto 'button'<br>`as` (mixed\|null, defecto `null`) — Etiqueta HTML explícita que anula button/a<br>`color` (mixed\|null, defecto `null`) — Color CSS que sobreescribe los tokens primary/ring locales<br>`colorForeground` (mixed\|null, defecto `null`) — Color de etiqueta para color; por defecto blanco<br>*Slots nombrados:* `before`, `after`. |

## Familia `button-group`

> Agrupa botones y controles conectando bordes según orientación horizontal o vertical.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `button-group` | `uiButtonGroup()` | Agrupa botones y controles conectando bordes según orientación horizontal o vertical. | `orientation` (string, defecto `'horizontal'`) — horizontal (defecto) o vertical<br>*Slot por defecto:* sí. |
| `button-group-separator` | `uiButtonGroupSeparator()` | Divisor fino entre elementos de un grupo de botones. | — |
| `button-group-text` | `uiButtonGroupText()` | Bloque de texto no interactivo estilizado para insertar en un grupo de botones. | *Slot por defecto:* sí. |

## Familia `calendar`

> Calendario (isla hotCalendar) con modos single/multiple/range, límites de fechas y accesibilidad.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `calendar` | `uiCalendar()` | Calendario (isla hotCalendar) con modos single/multiple/range, límites de fechas y accesibilidad. | `mode` (string, defecto `'single'`) — single, multiple o range<br>`value` (mixed\|null, defecto `null`) — Selección inicial; compatible con x-model<br>`name` (mixed\|null, defecto `null`) — Genera inputs hidden (con [from]/[to] en range) para formularios<br>`locale` (mixed\|null, defecto `null`) — Locale BCP 47 para formatear fechas<br>`numberOfMonths` (int, defecto `1`) — Meses visibles; entero, por defecto 1<br>`defaultMonth` (mixed\|null, defecto `null`) — Mes mostrado inicialmente<br>`weekStart` (int, defecto `0`) — 0-6 (0=domingo) o nombre de día en inglés<br>`captionLayout` (string, defecto `'label'`) — label o dropdown<br>`showWeekNumber` (bool, defecto `false`) — Booleano; columna de número de semana<br>`disabled` (mixed\|null, defecto `null`) — Fecha(s) deshabilitadas<br>`min` (mixed\|null, defecto `null`) — Duración mínima del rango en días (legado)<br>`max` (mixed\|null, defecto `null`) — Duración máxima del rango en días (legado)<br>`minDays` (mixed\|null, defecto `null`) — Igual que min, más explícito<br>`maxDays` (mixed\|null, defecto `null`) — Igual que max, más explícito<br>`required` (bool, defecto `false`) — Booleano de obligatoriedad<br>`startMonth` (mixed\|null, defecto `null`) — Primer mes navegable<br>`endMonth` (mixed\|null, defecto `null`) — Último mes navegable<br>`disableNavigation` (bool, defecto `false`) — Booleano; bloquea el cambio de mes<br>`modifiers` (array, defecto `[]`) — Mapa {nombre: regla de fecha} para días especiales<br>`modifiersClass` (array, defecto `[]`) — Mapa {nombre: clase Tailwind} aplicada por modificador<br>`buttonVariant` (string, defecto `'ghost'`) — ghost (defecto) u outline para los botones de navegación<br>`showOutsideDays` (bool, defecto `true`) — Booleano; muestra días fuera del mes, true por defecto<br>`minDate` (mixed\|null, defecto `null`) — Límite absoluto inferior en Y-m-d<br>`maxDate` (mixed\|null, defecto `null`) — Límite absoluto superior en Y-m-d<br>`outOfRange` (string, defecto `'disable'`) — disable (bloquea selección) o flag (permite y marca en rojo)<br>`prevMonthLabel` (mixed\|null, defecto `null`) — Nombre accesible del botón mes anterior<br>`nextMonthLabel` (mixed\|null, defecto `null`) — Nombre accesible del botón mes siguiente<br>`todayLabel` (mixed\|null, defecto `null`) — Prefijo aria del día actual; :date es la fecha localizada<br>`selectedLabel` (mixed\|null, defecto `null`) — Sufijo aria cuando el día está seleccionado<br>`calendarId` (mixed\|null, defecto `null`) — Identificador para dirigir eventos calendar:* a este calendario |

## Familia `card`

> Tarjeta con relleno simple (default) o layout seccionado para header/content/footer.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `card` | `uiCard()` | Tarjeta con relleno simple (default) o layout seccionado para header/content/footer. | `variant` (string, defecto `'default'`) — default (caja con p-6) o sectioned (columna con py-6 para las partes)<br>*Slot por defecto:* sí. |
| `card-action` | `uiCardAction()` | Acción colocada en la cabecera de tarjeta, alineada arriba a la derecha. | *Slot por defecto:* sí. |
| `card-content` | `uiCardContent()` | Cuerpo de la tarjeta con padding horizontal. | *Slot por defecto:* sí. |
| `card-description` | `uiCardDescription()` | Descripción apagada bajo el título de la tarjeta. | *Slot por defecto:* sí. |
| `card-footer` | `uiCardFooter()` | Pie de la tarjeta; fila flexible para acciones o notas finales. | *Slot por defecto:* sí. |
| `card-header` | `uiCardHeader()` | Cabecera de tarjeta en rejilla que aloja título, descripción y acción. | *Slot por defecto:* sí. |
| `card-title` | `uiCardTitle()` | Título de la tarjeta con fuente semibold y leading compacto. | *Slot por defecto:* sí. |

## Familia `carousel`

> Carrusel accesible (isla hotCarousel) con flechas de teclado y swipe táctil opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `carousel` | `uiCarousel()` | Carrusel accesible (isla hotCarousel) con flechas de teclado y swipe táctil opcional. | `orientation` (string, defecto `'horizontal'`) — horizontal (defecto) o vertical<br>`swipe` (bool, defecto `true`) — Booleano; habilita el gesto táctil, true por defecto<br>*Slot por defecto:* sí. |
| `carousel-content` | `uiCarouselContent()` | Ventana overflow-hidden con pista deslizante del carrusel; soporta arrastre táctil. | *Slot por defecto:* sí. |
| `carousel-item` | `uiCarouselItem()` | Diapositiva individual del carrusel; ocupa todo el ancho/alto por defecto. | *Slot por defecto:* sí. |
| `carousel-next` | `uiCarouselNext()` | Botón siguiente del carrusel; se deshabilita al final y rota en orientación vertical. | — |
| `carousel-previous` | `uiCarouselPrevious()` | Botón anterior del carrusel; se deshabilita al inicio. | — |

## Familia `chart`

> Gráfico ApexCharts listo para usar (isla shadcnChart) con series, opciones y colores tokenizados.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `chart` | `uiChart()` | Gráfico ApexCharts listo para usar (isla shadcnChart) con series, opciones y colores tokenizados. | `type` (string, defecto `'line'`) — Tipo ApexCharts: line (defecto), bar, pie, etc.<br>`series` (array, defecto `[]`) — Datos de las series ApexCharts<br>`options` (array, defecto `[]`) — Opciones extra que se fusionan con la configuración base<br>`colors` (array, defecto `[]`) — Colores directos de series; se derivan de config si faltan<br>`config` (array, defecto `[]`) — Mapa shadcn {clave: {label, color}} que crea vars --color-clave<br>`labels` (array, defecto `[]`) — Etiquetas de categorías del gráfico<br>`height` (int, defecto `250`) — Altura en px; por defecto 250<br>`label` (string, defecto `'Chart'`) — aria-label del gráfico; por defecto 'Chart' |
| `chart-container` | `uiChartContainer()` | Contenedor temático para gráficos Recharts; genera vars --color-<key> desde config. | `config` (array, defecto `[]`) — Mapa {clave: {label, color}} que emite variables CSS por serie<br>`id` (mixed\|null, defecto `null`) — Id del gráfico; se genera uno aleatorio chart-xxxx si falta<br>*Slot por defecto:* sí. |
| `chart-tooltip-content` | `uiChartTooltipContent()` | Estiliza el tooltip de gráficos: caja flotante pequeña con borde y sombra. | *Slot por defecto:* sí. |

## Familia `chat`

> Hilo de conversación desplazable (role=log, aria-live polite) que aloja mensajes de chat.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `chat` | `uiChat()` | Hilo de conversación desplazable (role=log, aria-live polite) que aloja mensajes de chat. | *Slot por defecto:* sí. |
| `chat-message` | `uiChatMessage()` | Mensaje de chat con avatar, burbuja alineada según rol (user/assistant) y estado typing animado. | `role` (string, defecto `'assistant'`) — 'user' alinea al final con burbuja primaria; cualquier otro valor es assistant<br>`name` (mixed\|null, defecto `null`) — Nombre mostrado sobre la burbuja y origen de las iniciales<br>`time` (mixed\|null, defecto `null`) — Marca temporal textual junto al nombre<br>`avatar` (mixed\|null, defecto `null`) — URL de imagen de avatar; si falta usa iniciales o icono genérico<br>`typing` (bool, defecto `false`) — Booleano; tres puntos animados en vez del contenido<br>*Slot por defecto:* sí. |

## Familia `checkbox`

> Casilla de verificación Alpine (o nativa con native) con indeterminado y soporte de formulario.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `checkbox` | `uiCheckbox()` | Casilla de verificación Alpine (o nativa con native) con indeterminado y soporte de formulario. | `id` (mixed\|null, defecto `null`) — Id del control<br>`name` (mixed\|null, defecto `null`) — Name para el envío (input hidden en modo Alpine)<br>`value` (string, defecto `'on'`) — Valor enviado; por defecto 'on'<br>`checked` (bool, defecto `false`) — Estado marcado inicial<br>`disabled` (bool, defecto `false`) — Booleano; deshabilita el control<br>`indeterminate` (bool, defecto `false`) — Booleano; guion y aria-checked=mixed (solo modo Alpine)<br>`native` (bool, defecto `false`) — Booleano; renderiza un input real para formularios sin JS |

## Familia `citation`

> Superíndice de cita numerada con popover de fuente: título, dominio enlazado y extracto.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `citation` | `uiCitation()` | Superíndice de cita numerada con popover de fuente: título, dominio enlazado y extracto. | `index` (int, defecto `1`) — Número de la cita mostrado; por defecto 1<br>`title` (mixed\|null, defecto `null`) — Título de la fuente en el popover<br>`url` (mixed\|null, defecto `null`) — URL de la fuente; muestra el dominio con enlace externo<br>`snippet` (mixed\|null, defecto `null`) — Extracto breve del contenido citado<br>*Slot por defecto:* sí. |

## Familia `code-block`

> Panel de código oscuro con cabecera de archivo opcional y botón de copiar al portapapeles.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `code-block` | `uiCodeBlock()` | Panel de código oscuro con cabecera de archivo opcional y botón de copiar al portapapeles. | `filename` (mixed\|null, defecto `null`) — Nombre de archivo en la cabecera; null omite la cabecera.<br>*Slot por defecto:* sí. |

## Familia `collapsible`

> Raíz del colapsable que gestiona el estado open compartido con trigger y contenido.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `collapsible` | `uiCollapsible()` | Raíz del colapsable que gestiona el estado open compartido con trigger y contenido. | `open` (bool, defecto `false`) — Estado inicial abierto; booleano, false por defecto.<br>*Slot por defecto:* sí. |
| `collapsible-content` | `uiCollapsibleContent()` | Panel de contenido del collapsible que se anima y solo se muestra cuando está abierto. | *Slot por defecto:* sí. |
| `collapsible-trigger` | `uiCollapsibleTrigger()` | Botón que alterna abrir/cerrar el collapsible con estados ARIA actualizados. | *Slot por defecto:* sí. |

## Familia `color-picker`

> Selector de color con vista previa, deslizador de matiz, entrada hex y paleta de muestras.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `color-picker` | `uiColorPicker()` | Selector de color con vista previa, deslizador de matiz, entrada hex y paleta de muestras. | `name` (mixed\|null, defecto `null`) — Name del input hidden; null lo omite.<br>`value` (string, defecto `'#6366f1'`) — Color inicial hex; '#6366f1' por defecto.<br>`swatches` (mixed\|null, defecto `null`) — Array de hex personalizados; usa paleta predeterminada si es null.<br>`disabled` (bool, defecto `false`) — Deshabilita el control; booleano.<br>`inline` (bool, defecto `false`) — Panel siempre visible en flujo en vez de popover; booleano.<br>`id` (mixed\|null, defecto `null`) — Id estable para la etiqueta sr-only; autogenerado si es null. |

## Familia `combobox`

> Combobox de selección única o múltiple con búsqueda, trigger de botón o input y chips removibles.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `combobox` | `uiCombobox()` | Combobox de selección única o múltiple con búsqueda, trigger de botón o input y chips removibles. | `name` (mixed\|null, defecto `null`) — Name del input hidden; en múltiple genera name[].<br>`options` (array, defecto `[]`) — Lista de opciones como strings o arrays {value,label}.<br>`value` (string, defecto `''`) — Valor inicial; string o array si multiple.<br>`placeholder` (mixed\|null, defecto `null`) — Texto del trigger; 'Select option...' o 'Search...' por defecto.<br>`searchPlaceholder` (mixed\|null, defecto `null`) — Placeholder de la búsqueda; 'Search...' por defecto.<br>`empty` (mixed\|null, defecto `null`) — Mensaje sin resultados; 'No results found.' por defecto.<br>`width` (string, defecto `'w-[200px]'`) — Clase de ancho; 'w-[200px]' por defecto.<br>`searchable` (bool, defecto `true`) — Muestra caja de búsqueda en trigger button; true por defecto.<br>`disabled` (bool, defecto `false`) — Deshabilita el control; booleano.<br>`multiple` (bool, defecto `false`) — Selección múltiple con chips; booleano.<br>`trigger` (string, defecto `'button'`) — 'button' / 'input'; input convierte el campo en buscador.<br>`size` (string, defecto `'default'`) — sm / default / lg; solo para trigger input.<br>`icon` (mixed\|null, defecto `null`) — Icono lucide inicial; solo trigger input.<br>`indicator` (string, defecto `'check'`) — check / checkbox / radio; marcado del ítem seleccionado. |

## Familia `command`

> Raíz del componente command que provee estado de consulta y navegación de ítems (hotCommand).

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `command` | `uiCommand()` | Raíz del componente command que provee estado de consulta y navegación de ítems (hotCommand). | *Slot por defecto:* sí. |
| `command-dialog` | `uiCommandDialog()` | Paleta de comandos modal con overlay, foco atrapado, Escape y título accesible en sr-only. | `title` (string, defecto `'Command Palette'`) — Título accesible; 'Command Palette' por defecto.<br>`description` (string, defecto `'Search for a command to run...'`) — Descripción accesible; texto sobre buscar comando.<br>*Slots nombrados:* `trigger`. |
| `command-empty` | `uiCommandEmpty()` | Mensaje centrado que aparece dentro del command cuando ningún ítem coincide con la búsqueda. | *Slot por defecto:* sí. |
| `command-group` | `uiCommandGroup()` | Agrupa ítems del command bajo un encabezado opcional vinculado por aria-labelledby. | `heading` (mixed\|null, defecto `null`) — Texto del encabezado del grupo; null lo omite.<br>*Slot por defecto:* sí. |
| `command-input` | `uiCommandInput()` | Campo de búsqueda del command que filtra ítems con navegación por flechas y Enter. | `placeholder` (string, defecto `'Type a command or search...'`) — Placeholder del campo; 'Type a command or search...' por defecto. |
| `command-item` | `uiCommandItem()` | Ítem filtrable y seleccionable del command; renderiza <a> si hay href, si no <div>. | `value` (mixed\|null, defecto `null`) — Palabra clave de filtrado; por defecto el texto visible del ítem.<br>`href` (mixed\|null, defecto `null`) — URL destino segura (safe_url); null renderiza div.<br>`disabled` (bool, defecto `false`) — Marca el ítem como deshabilitado con aria-disabled.<br>*Slot por defecto:* sí. |
| `command-list` | `uiCommandList()` | Contenedor role=listbox con scroll vertical que agrupa los ítems del command. | *Slot por defecto:* sí. |
| `command-separator` | `uiCommandSeparator()` | Divisor horizontal decorativo aria-hidden entre grupos del command. | — |
| `command-shortcut` | `uiCommandShortcut()` | Texto de atajo de teclado en tamaño pequeño alineado a la derecha del ítem. | *Slot por defecto:* sí. |

## Familia `comparison-slider`

> Comparador antes/después de imágenes con divisor arrastrable, etiquetas y slider accesible.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `comparison-slider` | `uiComparisonSlider()` | Comparador antes/después de imágenes con divisor arrastrable, etiquetas y slider accesible. | `before` (mixed\|null, defecto `null`) — URL de la imagen 'antes' (safe_url).<br>`after` (mixed\|null, defecto `null`) — URL de la imagen 'después' (safe_url).<br>`beforeLabel` (mixed\|null, defecto `null`) — Etiqueta esquina superior izquierda; null la omite.<br>`afterLabel` (mixed\|null, defecto `null`) — Etiqueta esquina superior derecha; null la omite.<br>`beforeAlt` (string, defecto `''`) — Texto alternativo de la imagen 'antes'.<br>`afterAlt` (string, defecto `''`) — Texto alternativo de la imagen 'después'.<br>`value` (int, defecto `50`) — Posición inicial del divisor en %; 0-100, 50 por defecto. |

## Familia `comparison-table`

> Tabla comparativa de columnas/tiers con filas de características, checks y tier destacado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `comparison-table` | `uiComparisonTable()` | Tabla comparativa de columnas/tiers con filas de características, checks y tier destacado. | `tiers` (array, defecto `[]`) — Encabezados de columna, p. ej. ['Hobby','Pro'].<br>`rows` (array, defecto `[]`) — Filas [{feature,values}]; valores true/false/string.<br>`highlight` (mixed\|null, defecto `null`) — Nombre del tier o índice base 0 a destacar; null nada.<br>`featureLabel` (string, defecto `'Feature'`) — Etiqueta de la primera columna; 'Feature' por defecto. |

## Familia `confetti`

> Ráfaga de partículas de confeti con disparador propio o externo; respeta prefers-reduced-motion.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `confetti` | `uiConfetti()` | Ráfaga de partículas de confeti con disparador propio o externo; respeta prefers-reduced-motion. | `count` (int, defecto `80`) — Número de partículas; 80 por defecto.<br>`spread` (int, defecto `70`) — Velocidad base de dispersión; 70 por defecto.<br>`colors` (mixed\|null, defecto `null`) — Array de colores hex; paleta predeterminada si es null.<br>`direction` (mixed\|null, defecto `null`) — Ángulo del disparo en grados; null = abanico 360°.<br>`spreadArc` (int, defecto `90`) — Arco del abanico dirigido en grados; 90 por defecto.<br>`fullscreen` (bool, defecto `false`) — Lluvia a pantalla completa desde arriba; booleano.<br>*Slot por defecto:* sí. |

## Familia `container`

> Contenedor centrado con padding lateral responsivo y ancho máximo según talla.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `container` | `uiContainer()` | Contenedor centrado con padding lateral responsivo y ancho máximo según talla. | `size` (string, defecto `'lg'`) — sm / md / lg / xl / prose / full; lg por defecto.<br>*Slot por defecto:* sí. |

## Familia `context-menu`

> Raíz del menú contextual que mantiene el estado open mediante hotMenu.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `context-menu` | `uiContextMenu()` | Raíz del menú contextual que mantiene el estado open mediante hotMenu. | *Slot por defecto:* sí. |
| `context-menu-checkbox-item` | `uiContextMenuCheckboxItem()` | Ítem con casilla del menú contextual; delega en el primitivo uiMenuCheckboxItem. | `checked` (bool, defecto `false`) — Estado marcado inicial; booleano.<br>`disabled` (bool, defecto `false`) — Deshabilita el ítem; booleano.<br>`closeOnSelect` (bool, defecto `false`) — Cierra el menú al elegir; false por defecto.<br>*Slot por defecto:* sí. |
| `context-menu-content` | `uiContextMenuContent()` | Panel del menú contextual fijado a las coordenadas del clic derecho, teletransportado al body. | *Slot por defecto:* sí. |
| `context-menu-group` | `uiContextMenuGroup()` | Grupo de ítems del menú contextual con etiqueta opcional; delega en uiMenuGroup. | *Slot por defecto:* sí. |
| `context-menu-item` | `uiContextMenuItem()` | Ítem del menú contextual como botón o enlace; siempre envía como botón y cierra al seleccionar. | `href` (mixed\|null, defecto `null`) — URL del enlace; null renderiza botón.<br>`variant` (string, defecto `'default'`) — 'default' / destructive; estilo del ítem.<br>`inset` (bool, defecto `false`) — Sangría adicional a la izquierda; booleano.<br>`disabled` (bool, defecto `false`) — Deshabilita el ítem; booleano.<br>*Slot por defecto:* sí. |
| `context-menu-label` | `uiContextMenuLabel()` | Etiqueta de sección del menú contextual con sangría opcional; delega en uiMenuLabel. | `inset` (bool, defecto `false`) — Aplica sangría ps-8 con data-inset; booleano.<br>*Slot por defecto:* sí. |
| `context-menu-radio-group` | `uiContextMenuRadioGroup()` | Grupo de opciones tipo radio del menú contextual que comparte un valor común. | `value` (string, defecto `''`) — Valor seleccionado inicial; '' por defecto.<br>*Slot por defecto:* sí. |
| `context-menu-radio-item` | `uiContextMenuRadioItem()` | Opción radio del menú contextual con valor propio y cierre opcional al elegir. | `value` (string, defecto `''`) — Valor identificador de la opción; '' por defecto.<br>`closeOnSelect` (bool, defecto `false`) — Cierra el menú al elegir; false por defecto.<br>*Slot por defecto:* sí. |
| `context-menu-separator` | `uiContextMenuSeparator()` | Separador visual entre grupos del menú contextual; delega en uiMenuSeparator. | — |
| `context-menu-shortcut` | `uiContextMenuShortcut()` | Atajo de teclado dentro de un ítem del menú contextual; delega en uiMenuShortcut. | *Slot por defecto:* sí. |
| `context-menu-sub` | `uiContextMenuSub()` | Submenú anidado del menú contextual que abre al hover y cierra con retardo (hotMenu). | *Slot por defecto:* sí. |
| `context-menu-sub-content` | `uiContextMenuSubContent()` | Panel del submenú contextual anclado a la derecha del sub-trigger, con navegación por teclado. | *Slot por defecto:* sí. |
| `context-menu-sub-trigger` | `uiContextMenuSubTrigger()` | Fila disparadora del submenú contextual con chevron y estados hover/open. | `inset` (bool, defecto `false`) — Aplica sangría ps-8; booleano.<br>*Slot por defecto:* sí. |
| `context-menu-trigger` | `uiContextMenuTrigger()` | Zona que abre el menú contextual al recibir el evento clic derecho. | *Slot por defecto:* sí. |

## Familia `cookie-consent`

> Banner GDPR de cookies con aceptar/rechazar, categorías configurables y memoria en localStorage.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `cookie-consent` | `uiCookieConsent()` | Banner GDPR de cookies con aceptar/rechazar, categorías configurables y memoria en localStorage. | `position` (string, defecto `'bottom'`) — bottom / bottom-start / bottom-end / top; bottom por defecto.<br>`customizable` (bool, defecto `false`) — Muestra toggle Personalizar con switches por categoría; booleano.<br>`demo` (bool, defecto `false`) — Render estático en flujo que ignora localStorage; booleano.<br>*Slot por defecto:* sí. |

## Familia `copy-button`

> Botón que copia un valor al portapapeles, cambia a check y anuncia Copied vía aria-live.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `copy-button` | `uiCopyButton()` | Botón que copia un valor al portapapeles, cambia a check y anuncia Copied vía aria-live. | `value` (string, defecto `''`) — Texto a copiar; '' por defecto.<br>`label` (string, defecto `'Copy'`) — Etiqueta accesible; 'Copy' por defecto, pasa a Copied.<br>*Slot por defecto:* sí. |

## Familia `countdown`

> Cronómetro regresivo con tarjetas de días/horas/minutos/segundos y mensaje al expirar.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `countdown` | `uiCountdown()` | Cronómetro regresivo con tarjetas de días/horas/minutos/segundos y mensaje al expirar. | `to` (mixed\|null, defecto `null`) — Fecha objetivo parseable, p. ej. '2026-12-31 18:00'.<br>`expired` (string, defecto `'Expired'`) — Texto al expirar; 'Expired' por defecto o slot.<br>`labels` (array, defecto `['days' => 'Days', 'hours' => 'Hrs', 'minutes' => 'Min', 'seconds' => 'Sec']`) — Etiquetas de unidades; Days/Hrs/Min/Sec por defecto.<br>*Slot por defecto:* sí. |

## Familia `data-table`

> Tabla de datos con búsqueda, ordenamiento, selección de filas, paginación y columna de acciones.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `data-table` | `uiDataTable()` | Tabla de datos con búsqueda, ordenamiento, selección de filas, paginación y columna de acciones. | `columns` (array, defecto `[]`) — Columnas [{key,label,sortable,class}].<br>`rows` (array, defecto `[]`) — Filas como arrays asociativos clave=>valor.<br>`searchable` (bool, defecto `true`) — Muestra campo de búsqueda; true por defecto.<br>`searchKey` (mixed\|null, defecto `null`) — Clave de columna a buscar; null busca en todas.<br>`searchPlaceholder` (string, defecto `'Search...'`) — Placeholder del buscador; 'Search...' por defecto.<br>`selectable` (bool, defecto `true`) — Casillas de selección por fila; true por defecto.<br>`pageSize` (int, defecto `5`) — Filas por página; 5 por defecto.<br>`rowKey` (string, defecto `'id'`) — Clave estable de fila; 'id' por defecto.<br>`actionsLabel` (string, defecto `'Actions'`) — Encabezado sr-only de la columna acciones; 'Actions'.<br>`stickyActions` (bool, defecto `false`) — Fija la columna acciones al borde derecho; booleano.<br>*Slots nombrados:* `actions`. |

## Familia `date-picker`

> Selector de fechas en popover con modos single/range, límites, validación y presets rápidos.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `date-picker` | `uiDatePicker()` | Selector de fechas en popover con modos single/range, límites, validación y presets rápidos. | `mode` (string, defecto `'single'`) — 'single' / 'range'.<br>`name` (mixed\|null, defecto `null`) — Name base; range usa name[from] y name[to].<br>`value` (mixed\|null, defecto `null`) — 'Y-m-d' o ['from','to'] en 'Y-m-d'.<br>`placeholder` (mixed\|null, defecto `null`) — Texto del trigger; según modo.<br>`numberOfMonths` (mixed\|null, defecto `null`) — Meses visibles; 1 por defecto, 2 en range.<br>`captionLayout` (string, defecto `'label'`) — Encabezado del calendario; 'label' por defecto.<br>`weekStart` (int, defecto `0`) — 0-6 o nombre del día; 0 = domingo.<br>`defaultMonth` (mixed\|null, defecto `null`) — Mes inicial del calendario; null = hoy.<br>`min` (mixed\|null, defecto `null`) — Fecha mínima 'Y-m-d'; null sin límite.<br>`max` (mixed\|null, defecto `null`) — Fecha máxima 'Y-m-d'; null sin límite.<br>`minNights` (mixed\|null, defecto `null`) — Noches mínimas del rango; null ilimitado.<br>`maxNights` (mixed\|null, defecto `null`) — Noches máximas del rango; null ilimitado.<br>`outOfRange` (string, defecto `'disable'`) — 'disable' / 'flag'; manejo de fechas fuera de rango.<br>`showOutsideDays` (bool, defecto `true`) — Muestra días ajenos al mes; true por defecto.<br>`width` (mixed\|null, defecto `null`) — Clase de ancho; 240px o 300px en range.<br>`presets` (mixed\|null, defecto `null`) — true (por modo), array de claves nombradas o rangos propios. |

## Familia `datetime-picker`

> Selector de fecha y hora en popover que combina calendario y time field con validación de rango.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `datetime-picker` | `uiDatetimePicker()` | Selector de fecha y hora en popover que combina calendario y time field con validación de rango. | `mode` (string, defecto `'single'`) — 'single' / 'range'.<br>`name` (mixed\|null, defecto `null`) — Name base; range usa name[from] y name[to].<br>`value` (mixed\|null, defecto `null`) — 'Y-m-d\TH:i' o ['from','to'] iguales.<br>`placeholder` (mixed\|null, defecto `null`) — Texto del trigger; según modo.<br>`hourCycle` (string, defecto `'auto'`) — auto / 12 / 24; ciclo horario del time field.<br>`timeVariant` (string, defecto `'input'`) — 'input' / 'select'; variante del time field.<br>`seconds` (bool, defecto `false`) — Muestra segundos; booleano.<br>`minuteStep` (int, defecto `1`) — Incremento de minutos; 1 por defecto.<br>`captionLayout` (string, defecto `'dropdown'`) — Encabezado del calendario; 'dropdown' por defecto.<br>`min` (mixed\|null, defecto `null`) — Límite 'Y-m-d' o completo 'Y-m-d\TH:i'.<br>`max` (mixed\|null, defecto `null`) — Límite 'Y-m-d' o completo 'Y-m-d\TH:i'.<br>`minNights` (mixed\|null, defecto `null`) — Noches mínimas del rango; null ilimitado.<br>`maxNights` (mixed\|null, defecto `null`) — Noches máximas del rango; null ilimitado.<br>`outOfRange` (string, defecto `'disable'`) — 'disable' / 'flag'; fechas fuera de rango.<br>`weekStart` (int, defecto `0`) — 0-6 o nombre del día; 0 = domingo.<br>`numberOfMonths` (mixed\|null, defecto `null`) — Meses visibles; 1 por defecto, 2 en range.<br>`defaultMonth` (mixed\|null, defecto `null`) — Mes inicial del calendario; null = hoy.<br>`showOutsideDays` (bool, defecto `true`) — Muestra días ajenos al mes; true por defecto.<br>`width` (mixed\|null, defecto `null`) — Clase de ancho; 280px o 320px en range. |

## Familia `description-list`

> Lista de descripciones <dl> con disposición horizontal/vertical y tarjeta con divisores opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `description-item` | `uiDescriptionItem()` | Fila término/valor de una description-list que hereda layout y bordered del padre. | `term` (mixed\|null, defecto `null`) — Texto del término; admite markup vía slot term.<br>`layout` (string, defecto `'horizontal'`) — 'horizontal' / 'vertical'; heredado del padre.<br>`bordered` (bool, defecto `false`) — Activa padding interno con divisores; booleano.<br>*Slots nombrados:* `term`. |
| `description-list` | `uiDescriptionList()` | Lista de descripciones <dl> con disposición horizontal/vertical y tarjeta con divisores opcional. | `layout` (string, defecto `'horizontal'`) — 'horizontal' / 'vertical'; horizontal por defecto.<br>`bordered` (bool, defecto `false`) — Envuelve en tarjeta con divide-y; booleano.<br>*Slot por defecto:* sí. |

## Familia `dialog`

> Raíz del diálogo modal que guarda el estado open y soporta apertura por dispatch con id.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `dialog` | `uiDialog()` | Raíz del diálogo modal que guarda el estado open y soporta apertura por dispatch con id. | `open` (bool, defecto `false`) — Estado inicial abierto; booleano, false por defecto.<br>`id` (mixed\|null, defecto `null`) — Id para modo dispatchable open-dialog-{id}; null desactiva.<br>*Slot por defecto:* sí. |
| `dialog-close` | `uiDialogClose()` | Span contenedor que cierra el diálogo al hacer clic. | *Slot por defecto:* sí. |
| `dialog-content` | `uiDialogContent()` | Panel modal del diálogo con overlay, foco atrapado, posición configurable y botón de cierre. | `showClose` (bool, defecto `true`) — Muestra botón X de cierre; true por defecto.<br>`fullscreen` (bool, defecto `false`) — Renderiza a pantalla completa inset-0; booleano.<br>`position` (string, defecto `'center'`) — center / top / bottom / left / right / esquinas; center.<br>`closeOnOverlay` (bool, defecto `true`) — Cerrar al hacer clic en el fondo; true por defecto.<br>*Slot por defecto:* sí. |
| `dialog-description` | `uiDialogDescription()` | Párrafo descriptivo del diálogo asociado vía aria-labelledby. | *Slot por defecto:* sí. |
| `dialog-footer` | `uiDialogFooter()` | Pie del diálogo que apila y alinea acciones a la derecha en pantallas grandes. | *Slot por defecto:* sí. |
| `dialog-header` | `uiDialogHeader()` | Cabecera del diálogo que agrupa título y descripción con separación flexible. | *Slot por defecto:* sí. |
| `dialog-title` | `uiDialogTitle()` | Título <h2> del diálogo referenciado por aria-labelledby. | *Slot por defecto:* sí. |
| `dialog-trigger` | `uiDialogTrigger()` | Disparador que abre el diálogo local o uno dispatchable mediante el prop for. | `for` (mixed\|null, defecto `null`) — Id del diálogo dispatchable a abrir; null abre el local.<br>*Slot por defecto:* sí. |

## Familia `diff-viewer`

> Visor de diferencias línea a línea calculadas con LCS, en modos inline o split.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `diff-viewer` | `uiDiffViewer()` | Visor de diferencias línea a línea calculadas con LCS, en modos inline o split. | `old` (string, defecto `''`) — Texto original a comparar.<br>`new` (string, defecto `''`) — Texto cambiado a comparar.<br>`mode` (string, defecto `'inline'`) — 'inline' / 'split'; inline por defecto.<br>`filename` (mixed\|null, defecto `null`) — Etiqueta de cabecera del archivo; null la omite. |

## Familia `dock`

> Barra flotante estilo macOS cuyos iconos se magnifican cerca del cursor (efecto fisheye).

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `dock` | `uiDock()` | Barra flotante estilo macOS cuyos iconos se magnifican cerca del cursor (efecto fisheye). | `magnify` (float, defecto `1.6`) — Escala máxima junto al cursor; 1.6 por defecto.<br>`distance` (int, defecto `120`) — Radio de influencia en px; 120 por defecto.<br>*Slot por defecto:* sí. |
| `dock-item` | `uiDockItem()` | Tile del dock con icono lucide, tooltip y punto activo que se magnifica cerca del cursor. | `icon` (mixed\|null, defecto `null`) — Nombre del icono lucide; null permite componer por slot.<br>`label` (mixed\|null, defecto `null`) — Nombre accesible y texto del tooltip; null lo omite.<br>`href` (string, defecto `'#'`) — Destino del enlace; '#' o null renderiza botón.<br>`active` (bool, defecto `false`) — Muestra punto indicador y aria-current; booleano.<br>*Slot por defecto:* sí. |

## Familia `dot-pattern`

> Capa decorativa absoluta de fondo punteado con máscara radial opcional y color del tema.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `dot-pattern` | `uiDotPattern()` | Capa decorativa absoluta de fondo punteado con máscara radial opcional y color del tema. | `size` (int, defecto `1`) — Diámetro del punto en px; 1 por defecto.<br>`gap` (int, defecto `16`) — Separación entre puntos en px; 16 por defecto.<br>`mask` (bool, defecto `false`) — Desvanece los puntos hacia los bordes; booleano. |

## Familia `drawer`

> Raíz del drawer que guarda dirección y estado open mediante hotDrawer.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `drawer` | `uiDrawer()` | Raíz del drawer que guarda dirección y estado open mediante hotDrawer. | `direction` (string, defecto `'bottom'`) — top / bottom / left / right; bottom por defecto.<br>*Slot por defecto:* sí. |
| `drawer-close` | `uiDrawerClose()` | Span contenedor que cierra el drawer al hacer clic. | *Slot por defecto:* sí. |
| `drawer-content` | `uiDrawerContent()` | Panel del drawer con overlay, diseño según dirección y cierre opcional por fondo. | `direction` (mixed\|null, defecto `null`) — Dirección heredada top / bottom / left / right; estiliza el panel.<br>`closeOnOverlay` (bool, defecto `true`) — Cerrar al hacer clic en el fondo; true por defecto.<br>*Slot por defecto:* sí. |
| `drawer-description` | `uiDrawerDescription()` | Párrafo descriptivo del drawer asociado vía aria-labelledby. | *Slot por defecto:* sí. |
| `drawer-footer` | `uiDrawerFooter()` | Pie del drawer empujado abajo con mt-auto para acciones. | *Slot por defecto:* sí. |
| `drawer-header` | `uiDrawerHeader()` | Cabecera del drawer que agrupa título y descripción, centrada en direcciones verticales. | *Slot por defecto:* sí. |
| `drawer-title` | `uiDrawerTitle()` | Título <h2> del drawer referenciado por aria-labelledby. | *Slot por defecto:* sí. |
| `drawer-trigger` | `uiDrawerTrigger()` | Span disparador que abre el drawer con atributos ARIA de diálogo. | *Slot por defecto:* sí. |

## Familia `dropdown-menu`

> Raíz del menú desplegable (hotMenu) que envuelve trigger y contenido sin caja visual.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `dropdown-menu` | `uiDropdownMenu()` | Raíz del menú desplegable (hotMenu) que envuelve trigger y contenido sin caja visual. | *Slot por defecto:* sí. |
| `dropdown-menu-checkbox-item` | `uiDropdownMenuCheckboxItem()` | Ítem con casilla del menú desplegable; delega en uiMenuCheckboxItem con dataSlot propio. | `checked` (bool, defecto `false`) — Estado marcado inicial; booleano.<br>`disabled` (bool, defecto `false`) — Deshabilita el ítem; booleano.<br>`closeOnSelect` (bool, defecto `false`) — Cierra el menú al elegir; false por defecto.<br>*Slot por defecto:* sí. |
| `dropdown-menu-content` | `uiDropdownMenuContent()` | Panel del menú desplegable anclado al trigger con colocación y offset configurables. | `align` (string, defecto `'start'`) — 'start' / 'center' / 'end'; start por defecto.<br>`side` (string, defecto `'bottom'`) — Lado de anclaje ('bottom', etc.); bottom por defecto.<br>`sideOffset` (int, defecto `4`) — Separación en px respecto al trigger; 4 por defecto.<br>*Slot por defecto:* sí. |
| `dropdown-menu-group` | `uiDropdownMenuGroup()` | Grupo compacto de ítems del dropdown con etiqueta opcional; delega en uiMenuGroup. | *Slot por defecto:* sí. |
| `dropdown-menu-item` | `uiDropdownMenuItem()` | Ítem del menú desplegable como enlace o botón, con variantes y cierre configurable al elegir. | `href` (mixed\|null, defecto `null`) — URL del enlace; null renderiza botón.<br>`variant` (string, defecto `'default'`) — 'default' / destructive; estilo del ítem.<br>`inset` (bool, defecto `false`) — Sangría adicional; booleano.<br>`disabled` (bool, defecto `false`) — Deshabilita el ítem; booleano.<br>`closeOnSelect` (bool, defecto `true`) — Cierra el menú al elegir; true por defecto.<br>`type` (string, defecto `'button'`) — 'button' por defecto; 'submit' envía el formulario.<br>*Slot por defecto:* sí. |
| `dropdown-menu-label` | `uiDropdownMenuLabel()` | Etiqueta de sección del menú desplegable con sangría opcional; delega en uiMenuLabel. | `inset` (bool, defecto `false`) — Aplica sangría con data-inset; booleano.<br>*Slot por defecto:* sí. |
| `dropdown-menu-radio-group` | `uiDropdownMenuRadioGroup()` | Grupo radio del menú desplegable que comparte radioValue entre sus opciones. | `value` (string, defecto `''`) — Valor seleccionado inicial; '' por defecto.<br>*Slot por defecto:* sí. |
| `dropdown-menu-radio-item` | `uiDropdownMenuRadioItem()` | Opción radio del menú desplegable con valor propio y cierre opcional al elegir. | `value` (string, defecto `''`) — Valor identificador de la opción; '' por defecto.<br>`closeOnSelect` (bool, defecto `false`) — Cierra el menú al elegir; false por defecto.<br>*Slot por defecto:* sí. |
| `dropdown-menu-separator` | `uiDropdownMenuSeparator()` | Separador visual entre grupos del menú desplegable; delega en uiMenuSeparator. | — |
| `dropdown-menu-shortcut` | `uiDropdownMenuShortcut()` | Atajo de teclado dentro de un ítem del dropdown; delega en uiMenuShortcut. | *Slot por defecto:* sí. |
| `dropdown-menu-sub` | `uiDropdownMenuSub()` | Contenedor de submenú para dropdown que abre y cierra al pasar el mouse. | *Slot por defecto:* sí. |
| `dropdown-menu-sub-content` | `uiDropdownMenuSubContent()` | Panel del submenú del dropdown anclado a la derecha del sub-trigger con navegación propia. | *Slot por defecto:* sí. |
| `dropdown-menu-sub-trigger` | `uiDropdownMenuSubTrigger()` | Trigger del submenú del dropdown con chevron derecho y estado abierto o cerrado. | `inset` (bool, defecto `false`) — booleano (false); añade data-inset con sangría extra<br>*Slot por defecto:* sí. |
| `dropdown-menu-trigger` | `uiDropdownMenuTrigger()` | Span disparador del dropdown que alterna el menú y navega con Enter, espacio y flechas. | *Slot por defecto:* sí. |

## Familia `editable`

> Texto editable en línea: botón que se convierte en input o textarea para guardar o cancelar.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `editable` | `uiEditable()` | Texto editable en línea: botón que se convierte en input o textarea para guardar o cancelar. | `name` (mixed\|null, defecto `null`) — name del input hidden que refleja el valor<br>`value` (string, defecto `''`) — texto inicial<br>`as` (string, defecto `'input'`) — input / textarea<br>`placeholder` (mixed\|null, defecto `null`) — placeholder del campo<br>`label` (string, defecto `'value'`) — etiqueta accesible para editar<br>`size` (string, defecto `'default'`) — sm / default / lg<br>`id` (mixed\|null, defecto `null`) — id del input oculto (autogenerado si falta) |

## Familia `empty`

> Contenedor raíz del estado vacío con borde discontinuo y contenido centrado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `empty` | `uiEmpty()` | Contenedor raíz del estado vacío con borde discontinuo y contenido centrado. | *Slot por defecto:* sí. |
| `empty-content` | `uiEmptyContent()` | Área de contenido del estado vacío para acciones, centrada y de ancho limitado. | *Slot por defecto:* sí. |
| `empty-description` | `uiEmptyDescription()` | Descripción apagada del estado vacío con enlaces subrayados. | *Slot por defecto:* sí. |
| `empty-header` | `uiEmptyHeader()` | Cabecera centrada del estado vacío que agrupa media, título y descripción. | *Slot por defecto:* sí. |
| `empty-media` | `uiEmptyMedia()` | Bloque de media del estado vacío, transparente o con fondo muted para icono. | `variant` (string, defecto `'default'`) — default / icon<br>*Slot por defecto:* sí. |
| `empty-title` | `uiEmptyTitle()` | Título destacado del estado vacío. | *Slot por defecto:* sí. |

## Familia `field`

> Campo de formulario con orientación vertical, horizontal o responsive.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `field` | `uiField()` | Campo de formulario con orientación vertical, horizontal o responsive. | `orientation` (string, defecto `'vertical'`) — vertical / horizontal / responsive<br>*Slot por defecto:* sí. |
| `field-content` | `uiFieldContent()` | Columna flexible que agrupa control y textos dentro de un campo. | *Slot por defecto:* sí. |
| `field-description` | `uiFieldDescription()` | Texto de ayuda apagado que acompaña a un campo de formulario. | *Slot por defecto:* sí. |
| `field-error` | `uiFieldError()` | Mensajes de error del campo con role alert; uno en texto o varios en lista. | `messages` (mixed\|null, defecto `null`) — null, string o array de mensajes u objetos con clave message<br>*Slot por defecto:* sí. |
| `field-group` | `uiFieldGroup()` | Agrupa campos en columna con espaciado adaptado a checkbox y radio groups. | *Slot por defecto:* sí. |
| `field-label` | `uiFieldLabel()` | Etiqueta accesible de campo que reacciona a grupos deshabilitados. | `for` (mixed\|null, defecto `null`) — id del control asociado<br>*Slot por defecto:* sí. |
| `field-legend` | `uiFieldLegend()` | Legend de fieldset dentro de Field con tamaño según variante. | `variant` (string, defecto `'legend'`) — legend / label<br>*Slot por defecto:* sí. |
| `field-separator` | `uiFieldSeparator()` | Línea divisoria entre grupos de campos, opcionalmente con texto central. | *Slot por defecto:* sí. |
| `field-set` | `uiFieldSet()` | Fieldset que agrupa campos y grupos relacionados en columna. | *Slot por defecto:* sí. |
| `field-title` | `uiFieldTitle()` | Título compacto en negrita media dentro de un campo. | *Slot por defecto:* sí. |

## Familia `file-upload`

> Dropzone de subida de archivos con input oculto y lista de archivos seleccionados.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `file-upload` | `uiFileUpload()` | Dropzone de subida de archivos con input oculto y lista de archivos seleccionados. | `name` (mixed\|null, defecto `null`) — name del input file (con [] si es múltiple)<br>`multiple` (bool, defecto `false`) — booleano (false) permite varios archivos<br>`accept` (mixed\|null, defecto `null`) — tipos aceptados, p.ej. image/*<br>`maxSizeLabel` (mixed\|null, defecto `null`) — etiqueta de tamaño máximo mostrada como pista<br>`id` (mixed\|null, defecto `null`) — id estable del input (autogenerado si falta)<br>`disabled` (bool, defecto `false`) — booleano (false) desactiva la zona |

## Familia `flip-card`

> Tarjeta 3D que voltea entre cara frontal y trasera al hover o clic.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `flip-card` | `uiFlipCard()` | Tarjeta 3D que voltea entre cara frontal y trasera al hover o clic. | `trigger` (string, defecto `'hover'`) — hover / click<br>`height` (mixed\|null, defecto `null`) — altura CSS de la caja, p.ej. 16rem (por defecto)<br>*Slots nombrados:* `front`, `back`. |

## Familia `gallery`

> Cuadrícula de miniaturas con lightbox modal, navegación prev/next y contador accesible.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `gallery` | `uiGallery()` | Cuadrícula de miniaturas con lightbox modal, navegación prev/next y contador accesible. | `images` (array, defecto `[]`) — array de URLs u objetos con src, thumb y alt<br>`columns` (int, defecto `3`) — número de columnas 1-6 (3)<br>`rounded` (string, defecto `'rounded-lg'`) — clase de redondeo de miniaturas (rounded-lg) |

## Familia `gantt`

> Diagrama de Gantt en tabla con barras por fechas, progreso, hitos y línea de hoy.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `gantt` | `uiGantt()` | Diagrama de Gantt en tabla con barras por fechas, progreso, hitos y línea de hoy. | `tasks` (array, defecto `[]`) — array de tareas con name, start, end, progress y color<br>`start` (mixed\|null, defecto `null`) — fecha inicial YYYY-MM-DD (por defecto mínima de tareas)<br>`end` (mixed\|null, defecto `null`) — fecha final YYYY-MM-DD (por defecto máxima de tareas)<br>`unit` (string, defecto `'day'`) — day / week / month<br>`today` (mixed\|null, defecto `null`) — fecha YYYY-MM-DD que anula hoy para demos |

## Familia `gradient-text`

> Texto con relleno degradado, presets de marca y animación shimmer opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `gradient-text` | `uiGradientText()` | Texto con relleno degradado, presets de marca y animación shimmer opcional. | `from` (mixed\|null, defecto `null`) — color inicial hex<br>`via` (mixed\|null, defecto `null`) — color intermedio opcional<br>`to` (mixed\|null, defecto `null`) — color final hex<br>`preset` (mixed\|null, defecto `null`) — brand / sunset / ocean / candy / gold / aurora / flamingo / mint<br>`animate` (bool, defecto `false`) — booleano (false) activa el shimmer infinito<br>`as` (string, defecto `'span'`) — etiqueta HTML del elemento (span)<br>*Slot por defecto:* sí. |

## Familia `grid-pattern`

> Fondo decorativo de rejilla con gradientes CSS, color heredado y máscara radial opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `grid-pattern` | `uiGridPattern()` | Fondo decorativo de rejilla con gradientes CSS, color heredado y máscara radial opcional. | `gap` (int, defecto `24`) — tamaño de celda en px (24)<br>`lineWidth` (int, defecto `1`) — grosor de línea en px (1)<br>`mask` (bool, defecto `false`) — booleano (false) aplica desvanecido radial hacia los bordes |

## Familia `heatmap`

> Mapa de calor semanal estilo GitHub con niveles de intensidad y leyenda Less/More.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `heatmap` | `uiHeatmap()` | Mapa de calor semanal estilo GitHub con niveles de intensidad y leyenda Less/More. | `data` (array, defecto `[]`) — mapa YYYY-MM-DD a entero o array plano de conteos (demo si vacío)<br>`levels` (mixed\|null, defecto `null`) — umbrales ascendentes opcionales, p.ej. [1,3,6,9] |

## Familia `hover-card`

> Contenedor hover card que abre por mouse o foco con retardos configurables.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `hover-card` | `uiHoverCard()` | Contenedor hover card que abre por mouse o foco con retardos configurables. | `openDelay` (int, defecto `400`) — milisegundos antes de abrir (400)<br>`closeDelay` (int, defecto `100`) — milisegundos antes de cerrar (100)<br>*Slot por defecto:* sí. |
| `hover-card-content` | `uiHoverCardContent()` | Contenido flotante del hover card teleportado al body y ancado al trigger. | `align` (string, defecto `'center'`) — center / start / end (center)<br>`side` (string, defecto `'bottom'`) — lado de anclaje (bottom)<br>`sideOffset` (int, defecto `4`) — desplazamiento en px (4)<br>*Slot por defecto:* sí. |
| `hover-card-trigger` | `uiHoverCardTrigger()` | Span disparador enfocable que referencia al trigger del hover card. | *Slot por defecto:* sí. |

## Familia `icon`

> Icono Lucide por nombre; añade clase de volteo RTL a flechas direccionales.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `icon` | `uiIcon()` | Icono Lucide por nombre; añade clase de volteo RTL a flechas direccionales. | `name` (mixed\|null, defecto `null`) — nombre del icono Lucide, p.ej. chevron-right |

## Familia `image`

> Imagen con placeholder difuminado, aparición suave y fallback de error con icono.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `image` | `uiImage()` | Imagen con placeholder difuminado, aparición suave y fallback de error con icono. | `src` (mixed\|null, defecto `null`) — URL de imagen validada por allowlist<br>`alt` (string, defecto `''`) — texto alternativo (y texto del error)<br>`ratio` (mixed\|null, defecto `null`) — relación de aspecto CSS, p.ej. 16/9<br>`placeholder` (mixed\|null, defecto `null`) — URL de imagen previa borrosa<br>`rounded` (string, defecto `'rounded-lg'`) — clase de redondeo (rounded-lg)<br>`fit` (string, defecto `'cover'`) — cover / contain |

## Familia `infinite-scroll`

> Lista con scroll infinito vía IntersectionObserver, spinner visible y botón Load more.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `infinite-scroll` | `uiInfiniteScroll()` | Lista con scroll infinito vía IntersectionObserver, spinner visible y botón Load more. | `threshold` (int, defecto `200`) — px antes del borde para disparar la carga (200)<br>*Slot por defecto:* sí. |

## Familia `input`

> Input con tamaños, toggle de visibilidad para password y adornos leading/trailing.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `input` | `uiInput()` | Input con tamaños, toggle de visibilidad para password y adornos leading/trailing. | `type` (string, defecto `'text'`) — tipo HTML (text); password activa el eye-toggle<br>`size` (string, defecto `'default'`) — sm / default / lg<br>`toggle` (mixed\|null, defecto `null`) — booleano; false desactiva el toggle de contraseña<br>`color` (mixed\|null, defecto `null`) — color CSS que personaliza ring y selección<br>*Slots nombrados:* `leading`, `trailing`. |
| `input-mask` | `uiInputMask()` | Input de texto con máscara aplicada por Alpine conservando los literales del patrón. | `mask` (string, defecto `''`) — patrón donde 9=dígito, a=letra, *=alfanumérico y resto literal<br>`value` (string, defecto `''`) — valor inicial<br>`id` (mixed\|null, defecto `null`) — id del input<br>`name` (mixed\|null, defecto `null`) — name del campo<br>`placeholder` (mixed\|null, defecto `null`) — placeholder<br>`inputmode` (mixed\|null, defecto `null`) — atributo inputmode, p.ej. numeric |

## Familia `input-group`

> Marco con borde y sombra que combina input, adornos, textos y botones.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `input-group` | `uiInputGroup()` | Marco con borde y sombra que combina input, adornos, textos y botones. | *Slot por defecto:* sí. |
| `input-group-addon` | `uiInputGroupAddon()` | Adorno del input group (icono o texto) posicionado según align. | `align` (string, defecto `'inline-start'`) — inline-start / inline-end / block-start / block-end<br>*Slot por defecto:* sí. |
| `input-group-button` | `uiInputGroupButton()` | Botón compacto para insertar dentro de un input group. | `size` (string, defecto `'xs'`) — xs / sm / icon-xs / icon-sm<br>`variant` (string, defecto `'ghost'`) — ghost / outline / default<br>`type` (string, defecto `'button'`) — atributo type del botón (button)<br>*Slot por defecto:* sí. |
| `input-group-input` | `uiInputGroupInput()` | Input plano sin bordes que actúa como control principal de un input group. | `type` (string, defecto `'text'`) — tipo HTML del input (text) |
| `input-group-text` | `uiInputGroupText()` | Texto o icono informativo estático dentro del input group. | *Slot por defecto:* sí. |

## Familia `input-otp`

> Contenedor OTP con input real invisible sobre casillas visuales y binding por modelo.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `input-otp` | `uiInputOtp()` | Contenedor OTP con input real invisible sobre casillas visuales y binding por modelo. | `name` (mixed\|null, defecto `null`) — name del input oculto<br>`maxlength` (int, defecto `6`) — longitud del código (6)<br>`value` (string, defecto `''`) — valor inicial<br>`disabled` (bool, defecto `false`) — booleano (false) deshabilita el campo<br>`alphanumeric` (bool, defecto `false`) — booleano (false) permite letras además de dígitos<br>`ariaLabel` (string, defecto `'One-time password'`) — etiqueta accesible (One-time password)<br>*Slot por defecto:* sí. |
| `input-otp-group` | `uiInputOtpGroup()` | Fila flex que agrupa las casillas del código OTP. | *Slot por defecto:* sí. |
| `input-otp-separator` | `uiInputOtpSeparator()` | Separador decorativo con guion entre grupos de dígitos del OTP. | — |
| `input-otp-slot` | `uiInputOtpSlot()` | Casilla visual del OTP que muestra el carácter y el cursor parpadeante activo. | `index` (int, defecto `0`) — posición del carácter (base 0) |

## Familia `item`

> Item de lista versátil; se vuelve enlace cuando recibe href válido.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `item` | `uiItem()` | Item de lista versátil; se vuelve enlace cuando recibe href válido. | `variant` (string, defecto `'default'`) — default / outline / muted<br>`size` (string, defecto `'default'`) — default / sm<br>`href` (mixed\|null, defecto `null`) — URL validada; convierte el item en enlace<br>*Slot por defecto:* sí. |
| `item-actions` | `uiItemActions()` | Contenedor de acciones (botones o menú) al final de un item. | *Slot por defecto:* sí. |
| `item-content` | `uiItemContent()` | Columna flexible con título y descripción dentro de un item. | *Slot por defecto:* sí. |
| `item-description` | `uiItemDescription()` | Descripción secundaria truncada a dos líneas para items. | *Slot por defecto:* sí. |
| `item-group` | `uiItemGroup()` | Lista vertical que agrupa varios items. | *Slot por defecto:* sí. |
| `item-media` | `uiItemMedia()` | Media del item (icono o imagen) con variantes default, icon e image. | *Slot por defecto:* sí. |
| `item-separator` | `uiItemSeparator()` | Divisor horizontal fino entre items. | — |
| `item-title` | `uiItemTitle()` | Título en negrita media del item. | *Slot por defecto:* sí. |

## Familia `json-viewer`

> Visor JSON colapsable con etiqueta de cabecera, botón copiar y árbol indentado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `json-viewer` | `uiJsonViewer()` | Visor JSON colapsable con etiqueta de cabecera, botón copiar y árbol indentado. | `data` (mixed\|null, defecto `null`) — array, objeto, escalar o string JSON<br>`expanded` (bool, defecto `true`) — booleano (true) deja los nodos abiertos<br>`rootLabel` (mixed\|null, defecto `null`) — etiqueta de cabecera (JSON por defecto) |
| `json-viewer-node` | `uiJsonViewerNode()` | Nodo recursivo del árbol JSON: contenedor colapsable o hoja coloreada por tipo. | `value` (mixed\|null, defecto `null`) — valor PHP a renderizar (array, objeto o escalar)<br>`depth` (int, defecto `0`) — nivel de indentación (0)<br>`expanded` (bool, defecto `true`) — booleano (true) estado inicial de apertura<br>`keyName` (mixed\|null, defecto `null`) — clave string o int del nodo<br>`isLast` (bool, defecto `true`) — booleano (true) omite la coma final |

## Familia `kanban`

> Tablero Kanban con tarjetas arrastrables entre columnas mediante drag & drop nativo.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `kanban` | `uiKanban()` | Tablero Kanban con tarjetas arrastrables entre columnas mediante drag & drop nativo. | `columns` (array, defecto `[]`) — array de columnas con id, title y cards (id, title, tags, meta) |

## Familia `kbd`

> Tecla estilizada para atajos; el aria-label recibido pasa a texto oculto.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `kbd` | `uiKbd()` | Tecla estilizada para atajos; el aria-label recibido pasa a texto oculto. | *Slot por defecto:* sí. |
| `kbd-group` | `uiKbdGroup()` | Agrupa teclas kbd consecutivas en una fila. | *Slot por defecto:* sí. |

## Familia `knob`

> Perilla giratoria accesible (slider de 270 grados) operable con arrastre, rueda y teclado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `knob` | `uiKnob()` | Perilla giratoria accesible (slider de 270 grados) operable con arrastre, rueda y teclado. | `name` (mixed\|null, defecto `null`) — name del input hidden sincronizado<br>`value` (int, defecto `50`) — valor inicial (50)<br>`min` (int, defecto `0`) — valor mínimo (0)<br>`max` (int, defecto `100`) — valor máximo (100)<br>`step` (int, defecto `1`) — incremento permitido (1)<br>`size` (string, defecto `'default'`) — sm / default / lg<br>`label` (string, defecto `'Value'`) — aria-label del dial (Value)<br>`disabled` (bool, defecto `false`) — booleano (false) bloquea la interacción |

## Familia `label`

> Etiqueta de formulario que se atenúa en grupos deshabilitados.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `label` | `uiLabel()` | Etiqueta de formulario que se atenúa en grupos deshabilitados. | *Slot por defecto:* sí. |

## Familia `link`

> Enlace con variantes de énfasis y modo externo que avisa a lectores de pantalla.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `link` | `uiLink()` | Enlace con variantes de énfasis y modo externo que avisa a lectores de pantalla. | `href` (string, defecto `'#'`) — URL validada por allowlist (# por defecto)<br>`variant` (string, defecto `'default'`) — default / muted / subtle<br>`external` (bool, defecto `false`) — booleano abre en nueva pestaña con noopener<br>*Slot por defecto:* sí. |

## Familia `loading-overlay`

> Velo superpuesto con spinner y mensaje accesible mientras el contenido está ocupado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `loading-overlay` | `uiLoadingOverlay()` | Velo superpuesto con spinner y mensaje accesible mientras el contenido está ocupado. | `show` (bool, defecto `false`) — booleano (false) muestra el velo<br>`message` (mixed\|null, defecto `null`) — texto bajo el spinner (Loading… por defecto)<br>`blur` (bool, defecto `true`) — booleano (true) aplica desenfoque de fondo<br>*Slot por defecto:* sí. |

## Familia `map`

> Mapa OpenStreetMap embebido en iframe con marcador y enlace al mapa ampliado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `map` | `uiMap()` | Mapa OpenStreetMap embebido en iframe con marcador y enlace al mapa ampliado. | `lat` (mixed\|null, defecto `null`) — latitud (Bruselas por defecto)<br>`lon` (mixed\|null, defecto `null`) — longitud (Bruselas por defecto)<br>`zoom` (int, defecto `14`) — nivel 1-19 (14)<br>`label` (string, defecto `'Location'`) — etiqueta accesible del mapa (Location)<br>`marker` (bool, defecto `true`) — booleano (true) coloca el marcador<br>`height` (int, defecto `320`) — alto en px (320) sin ratio<br>`ratio` (mixed\|null, defecto `null`) — relación de aspecto CSS alternativa al alto |

## Familia `markdown-editor`

> Editor markdown con toolbar de formato y pestañas Write/Preview renderizadas en cliente.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `markdown-editor` | `uiMarkdownEditor()` | Editor markdown con toolbar de formato y pestañas Write/Preview renderizadas en cliente. | `name` (mixed\|null, defecto `null`) — name del textarea<br>`value` (string, defecto `''`) — markdown inicial<br>`placeholder` (string, defecto `'Write markdown…'`) — placeholder (Write markdown…)<br>`rows` (int, defecto `8`) — filas del textarea (8)<br>`id` (mixed\|null, defecto `null`) — id base (autogenerado si falta) |

## Familia `marquee`

> Cinta continua que duplica el contenido y lo anima en horizontal o vertical.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `marquee` | `uiMarquee()` | Cinta continua que duplica el contenido y lo anima en horizontal o vertical. | `direction` (string, defecto `'left'`) — left / right / up / down<br>`duration` (string, defecto `'40s'`) — duración CSS de la animación (40s)<br>`gap` (string, defecto `'1rem'`) — espaciado CSS entre elementos (1rem)<br>`pauseOnHover` (bool, defecto `true`) — booleano (true) pausa al pasar el mouse<br>`fade` (bool, defecto `false`) — booleano (false) desvanece los bordes con máscara<br>*Slot por defecto:* sí. |

## Familia `masonry`

> Mosaico tipo Pinterest con columnas CSS responsivas y corte de hijos evitado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `masonry` | `uiMasonry()` | Mosaico tipo Pinterest con columnas CSS responsivas y corte de hijos evitado. | `columns` (int, defecto `3`) — número máximo de columnas responsive 1-6 (3)<br>`gap` (string, defecto `'4'`) — token de espaciado 0-8 (4)<br>*Slot por defecto:* sí. |

## Familia `mention-input`

> Textarea combobox que sugiere menciones con avatar al teclear el carácter trigger.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `mention-input` | `uiMentionInput()` | Textarea combobox que sugiere menciones con avatar al teclear el carácter trigger. | `name` (mixed\|null, defecto `null`) — name del textarea compuesto<br>`mentions` (array, defecto `[]`) — strings u objetos con value, label, avatar y sub<br>`trigger` (string, defecto `'@'`) — carácter que abre las sugerencias (@)<br>`placeholder` (string, defecto `'Type @ to mention…'`) — placeholder del campo<br>`rows` (int, defecto `3`) — filas del textarea (3)<br>`disabled` (bool, defecto `false`) — booleano (false)<br>`id` (mixed\|null, defecto `null`) — id del campo (autogenerado si falta)<br>*Slot por defecto:* sí. |

## Familia `menu-checkbox-item`

> Ítem de menú tipo casilla que alterna checked y muestra un icono check al estar marcado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-checkbox-item` | `uiMenuCheckboxItem()` | Ítem de menú tipo casilla que alterna checked y muestra un icono check al estar marcado. | `dataSlot` (string, defecto `'menu-checkbox-item'`) — valor de data-slot (defecto menu-checkbox-item)<br>`checked` (bool, defecto `false`) — bool (defecto false); estado inicial marcado<br>`disabled` (bool, defecto `false`) — bool (defecto false); deshabilita el ítem<br>`closeOnSelect` (bool, defecto `false`) — bool (defecto false); cierra el menú al alternar<br>*Slot por defecto:* sí. |

## Familia `menu-group`

> Agrupa ítems de menú como role=group y los asocia con su etiqueta vía aria-labelledby.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-group` | `uiMenuGroup()` | Agrupa ítems de menú como role=group y los asocia con su etiqueta vía aria-labelledby. | `dataSlot` (string, defecto `'menu-group'`) — valor de data-slot (defecto menu-group)<br>`labelSlot` (string, defecto `'menu-label'`) — selector del data-slot de etiqueta (defecto menu-label)<br>`compact` (bool, defecto `false`) — bool (defecto false); reproduce el layout exacto del dropdown<br>*Slot por defecto:* sí. |

## Familia `menu-item`

> Ítem genérico de menú como botón o enlace, con variante destructive, inset y cierre al elegir.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-item` | `uiMenuItem()` | Ítem genérico de menú como botón o enlace, con variante destructive, inset y cierre al elegir. | `dataSlot` (string, defecto `'menu-item'`) — valor de data-slot (defecto menu-item)<br>`classes` (mixed, defecto `"focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground data-[variant=destructive]:text-destructive data-[variant=destructive]:focus:bg-destructive/10 dark:data-[variant=destructive]:focus:bg-destructive/20 data-[variant=destructive]:focus:text-destructive data-[variant=destructive]:hover:bg-destructive/10 data-[variant=destructive]:*:[svg]:!text-destructive [&_svg:not([class*='text-'`) — clases base sobreescribibles<br>`href` — string/null; URL segura: renderiza <a>, si no <button><br>`variant` — default/destructive<br>`inset` — bool; sangría inicial extra (data-inset)<br>`disabled` — bool; deshabilita el botón<br>`closeOnSelect` — bool (defecto true); cierra el menú al seleccionar<br>`type` — tipo de botón (defecto button)<br>`mergeClick` — bool (defecto true); fusiona el @click llamador con close-on-select<br>`clickConditional` — bool (defecto true); omite @click si la expresión queda vacía<br>*Slot por defecto:* sí. |

## Familia `menu-label`

> Etiqueta de sección de menú (role=presentation) con sangría opcional y clases ajustables.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-label` | `uiMenuLabel()` | Etiqueta de sección de menú (role=presentation) con sangría opcional y clases ajustables. | `dataSlot` (string, defecto `'menu-label'`) — valor de data-slot (defecto menu-label)<br>`classes` (string, defecto `'px-2 py-1.5 text-sm font-medium data-[inset]:ps-8'`) — clases base sobreescribibles<br>`inset` (bool, defecto `false`) — bool (defecto false); añade data-inset con sangría<br>*Slot por defecto:* sí. |

## Familia `menu-radio-item`

> Ítem radio de menú que marca radioValue con su valor y muestra punto circular al elegirse.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-radio-item` | `uiMenuRadioItem()` | Ítem radio de menú que marca radioValue con su valor y muestra punto circular al elegirse. | `dataSlot` (string, defecto `'menu-radio-item'`) — valor de data-slot (defecto menu-radio-item)<br>`value` (string, defecto `''`) — string; valor asignado a radioValue al pulsar<br>`closeOnSelect` (bool, defecto `false`) — bool (defecto false); cierra el menú tras seleccionar<br>*Slot por defecto:* sí. |

## Familia `menu-separator`

> Separador horizontal fino entre grupos de un menú.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-separator` | `uiMenuSeparator()` | Separador horizontal fino entre grupos de un menú. | `dataSlot` (string, defecto `'menu-separator'`) — valor de data-slot (defecto menu-separator) |

## Familia `menu-shortcut`

> Atajo de teclado en texto pequeño alineado al final dentro de un ítem de menú.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menu-shortcut` | `uiMenuShortcut()` | Atajo de teclado en texto pequeño alineado al final dentro de un ítem de menú. | `dataSlot` (string, defecto `'menu-shortcut'`) — valor de data-slot (defecto menu-shortcut)<br>*Slot por defecto:* sí. |

## Familia `menubar`

> Barra de menús horizontal con role menubar y navegación por teclado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `menubar` | `uiMenubar()` | Barra de menús horizontal con role menubar y navegación por teclado. | *Slot por defecto:* sí. |
| `menubar-checkbox-item` | `uiMenubarCheckboxItem()` | Ítem de casilla del menubar delegado en el primitivo uiMenuCheckboxItem. | `checked` (bool, defecto `false`) — booleano (false) muestra el check<br>`disabled` (bool, defecto `false`) — booleano (false)<br>`closeOnSelect` (bool, defecto `false`) — booleano (false) cierra el menú al elegir<br>*Slot por defecto:* sí. |
| `menubar-content` | `uiMenubarContent()` | Panel del menú del menubar teleportado con Escape, click fuera y flechas laterales. | `align` (string, defecto `'start'`) — start / center / end (start)<br>`side` (string, defecto `'bottom'`) — lado de anclaje (bottom)<br>`sideOffset` (int, defecto `8`) — separación del trigger en px (8)<br>*Slot por defecto:* sí. |
| `menubar-group` | `uiMenubarGroup()` | Grupo semántico de ítems del menubar con etiqueta opcional. | *Slot por defecto:* sí. |
| `menubar-item` | `uiMenubarItem()` | Ítem del menubar como botón o enlace; cierra siempre el menú al activarse. | `href` (mixed\|null, defecto `null`) — URL; lo convierte en enlace<br>`variant` (string, defecto `'default'`) — default / destructive<br>`inset` (bool, defecto `false`) — booleano (false) añade sangría<br>`disabled` (bool, defecto `false`) — booleano (false)<br>*Slot por defecto:* sí. |
| `menubar-label` | `uiMenubarLabel()` | Etiqueta descriptiva de sección dentro del menú del menubar. | `inset` (bool, defecto `false`) — booleano (false) añade sangría izquierda<br>*Slot por defecto:* sí. |
| `menubar-menu` | `uiMenubarMenu()` | Menú individual del menubar con id único que coordina trigger y panel. | *Slot por defecto:* sí. |
| `menubar-radio-group` | `uiMenubarRadioGroup()` | Grupo radio del menubar que guarda la opción activa en estado Alpine. | `value` (string, defecto `''`) — valor seleccionado inicial<br>*Slot por defecto:* sí. |
| `menubar-radio-item` | `uiMenubarRadioItem()` | Opción radio del menubar delegada en uiMenuRadioItem con indicador circular. | `value` (string, defecto `''`) — valor identificador de la opción<br>`closeOnSelect` (bool, defecto `false`) — booleano (false) cierra al elegir<br>*Slot por defecto:* sí. |
| `menubar-separator` | `uiMenubarSeparator()` | Línea divisoria entre ítems del menú del menubar. | — |
| `menubar-shortcut` | `uiMenubarShortcut()` | Atajo de teclado alineado a la derecha del ítem del menubar. | *Slot por defecto:* sí. |
| `menubar-sub` | `uiMenubarSub()` | Contenedor de submenú dentro del menubar que responde al hover. | *Slot por defecto:* sí. |
| `menubar-sub-content` | `uiMenubarSubContent()` | Submenú flotante del menubar anclado a la derecha con cierre por Escape o left. | *Slot por defecto:* sí. |
| `menubar-sub-trigger` | `uiMenubarSubTrigger()` | Disparador de submenú dentro de la menubar con chevron derecho y estado abierto/cerrado. | `inset` (bool, defecto `false`) — bool (defecto false); añade data-inset con sangría inicial extra<br>*Slot por defecto:* sí. |
| `menubar-trigger` | `uiMenubarTrigger()` | Botón disparador de menú en la menubar con roving tabindex, flechas y apertura por hover. | *Slot por defecto:* sí. |

## Familia `meteors`

> Capa decorativa de meteoros animados que caen en diagonal detrás del contenido del slot.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `meteors` | `uiMeteors()` | Capa decorativa de meteoros animados que caen en diagonal detrás del contenido del slot. | `count` (int, defecto `20`) — int 1-100 (defecto 20); número de meteoros<br>`color` (mixed\|null, defecto `null`) — string/null; color CSS válido de cabeza y estela; por defecto foreground translúcido<br>*Slot por defecto:* sí. |

## Familia `meter`

> Medidor accesible tipo meter con barra, etiqueta, valor visible y tono según umbrales.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `meter` | `uiMeter()` | Medidor accesible tipo meter con barra, etiqueta, valor visible y tono según umbrales. | `value` (int, defecto `0`) — float; valor actual dentro de [min,max]<br>`min` (int, defecto `0`) — float (defecto 0)<br>`max` (int, defecto `100`) — float (defecto 100)<br>`label` (mixed\|null, defecto `null`) — string/null; etiqueta visible y nombre accesible<br>`tone` (mixed\|null, defecto `null`) — good/warning/danger/default/null; fuerza el color<br>`low` (mixed\|null, defecto `null`) — float/null; borde inferior de la banda óptima<br>`high` (mixed\|null, defecto `null`) — float/null; borde superior de la banda óptima<br>`optimum` (mixed\|null, defecto `null`) — float/null; ideal que decide qué banda es buena<br>`showValue` (bool, defecto `true`) — bool (defecto true); muestra el valor numérico<br>`unit` (string, defecto `'%'`) — string (defecto %); sufijo del texto de valor |

## Familia `mini-cart`

> Carrito compacto: botón con badge de unidades, panel con líneas, stepper y subtotal.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `mini-cart` | `uiMiniCart()` | Carrito compacto: botón con badge de unidades, panel con líneas, stepper y subtotal. | `items` (array, defecto `[]`) — array de {name, variant?, price, qty, image?}<br>`currency` (string, defecto `'$'`) — símbolo monetario (defecto $)<br>`open` (bool, defecto `false`) — bool (defecto false); estado inicial del panel |

## Familia `navigation-menu`

> Raíz nav del navigation menu que guarda el panel activo y lo cierra al salir el ratón.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `navigation-menu` | `uiNavigationMenu()` | Raíz nav del navigation menu que guarda el panel activo y lo cierra al salir el ratón. | *Slot por defecto:* sí. |
| `navigation-menu-content` | `uiNavigationMenuContent()` | Panel de contenido del navigation menu que aparece con el ítem activo, anclado al trigger. | *Slot por defecto:* sí. |
| `navigation-menu-item` | `uiNavigationMenuItem()` | Ítem li del navigation menu que define el id compartido entre trigger y contenido. | *Slot por defecto:* sí. |
| `navigation-menu-link` | `uiNavigationMenuLink()` | Enlace del panel de navegación resaltado cuando marca la página activa. | `href` (string, defecto `'#'`) — URL destino (defecto #); saneada con safe_url<br>`active` (bool, defecto `false`) — bool (defecto false); marca aria-current=page<br>*Slot por defecto:* sí. |
| `navigation-menu-list` | `uiNavigationMenuList()` | Lista ul horizontal centrada que contiene los ítems del navigation menu. | *Slot por defecto:* sí. |
| `navigation-menu-trigger` | `uiNavigationMenuTrigger()` | Botón disparador que abre el panel al hacer clic o hover, con chevron rotatorio. | *Slot por defecto:* sí. |

## Familia `notification-center`

> Centro de notificaciones: campana con badge de no leídas y panel con marcar todo leído.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `notification-center` | `uiNotificationCenter()` | Centro de notificaciones: campana con badge de no leídas y panel con marcar todo leído. | `notifications` (array, defecto `[]`) — array de {title, body?, time, read?, icon?, avatar?}<br>`open` (bool, defecto `false`) — bool (defecto false); estado inicial del panel |

## Familia `number-input`

> Campo numérico spinbutton con botones menos/más, límites min/max y paso decimal preciso.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `number-input` | `uiNumberInput()` | Campo numérico spinbutton con botones menos/más, límites min/max y paso decimal preciso. | `name` (mixed\|null, defecto `null`) — string/null; nombre del input<br>`value` (int, defecto `0`) — numérico inicial (defecto 0)<br>`min` (mixed\|null, defecto `null`) — float/null; mínimo permitido<br>`max` (mixed\|null, defecto `null`) — float/null; máximo permitido<br>`step` (int, defecto `1`) — float (defecto 1); incremento<br>`size` (string, defecto `'default'`) — sm/default/lg; altura del campo<br>`disabled` (bool, defecto `false`) — bool (defecto false)<br>`id` (mixed\|null, defecto `null`) — string/null; id del input<br>`placeholder` (mixed\|null, defecto `null`) — string/null |

## Familia `number-ticker`

> Número animado count-up con easing al entrar en viewport, prefijos y decimales configurables.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `number-ticker` | `uiNumberTicker()` | Número animado count-up con easing al entrar en viewport, prefijos y decimales configurables. | `value` (int, defecto `0`) — float final a alcanzar<br>`from` (int, defecto `0`) — float inicial (defecto 0)<br>`duration` (int, defecto `1500`) — ms del conteo (defecto 1500)<br>`decimals` (int, defecto `0`) — int decimales (defecto 0)<br>`prefix` (string, defecto `''`) — string prefijo<br>`suffix` (string, defecto `''`) — string sufijo<br>`separator` (string, defecto `','`) — separador de miles (defecto ,) |

## Familia `onboarding-tour`

> Tour guiado con spotlight sobre cada target, tarjeta de paso y controles atrás/siguiente.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `onboarding-tour` | `uiOnboardingTour()` | Tour guiado con spotlight sobre cada target, tarjeta de paso y controles atrás/siguiente. | `steps` (array, defecto `[]`) — array de {target?, title, body, placement}<br>`open` (bool, defecto `false`) — bool (defecto false); inicia el tour abierto<br>*Slot por defecto:* sí. |

## Familia `org-chart`

> Organigrama jerárquico con conectores CSS entre padre e hijos y scroll horizontal.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `org-chart` | `uiOrgChart()` | Organigrama jerárquico con conectores CSS entre padre e hijos y scroll horizontal. | `root` (array, defecto `[]`) — nodo raíz {name,title?,avatar?,children?}<br>`data` (mixed\|null, defecto `null`) — alias de root; gana si no está vacío |
| `org-chart-node` | `uiOrgChartNode()` | Nodo recursivo del organigrama: avatar o iniciales, nombre, cargo e hijos anidados. | `node` (array, defecto `[]`) — array {name, title?, avatar?, children?}; nodo del árbol |

## Familia `page-header`

> Cabecera de página con título h1-h6, descripción, borde inferior opcional y acciones.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `page-header` | `uiPageHeader()` | Cabecera de página con título h1-h6, descripción, borde inferior opcional y acciones. | `title` (mixed\|null, defecto `null`) — string/null; título (también via slot por defecto)<br>`description` (mixed\|null, defecto `null`) — string/null; texto de apoyo<br>`separator` (bool, defecto `false`) — bool (defecto false); borde inferior<br>`as` (string, defecto `'h1'`) — h1..h6 (defecto h1); nivel del título<br>*Slots nombrados:* `breadcrumb`, `actions`. |

## Familia `pagination`

> Raíz nav role=navigation aria-label=pagination que centra la paginación.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `pagination` | `uiPagination()` | Raíz nav role=navigation aria-label=pagination que centra la paginación. | *Slot por defecto:* sí. |
| `pagination-content` | `uiPaginationContent()` | Lista ul en fila que envuelve los controles de la paginación. | *Slot por defecto:* sí. |
| `pagination-ellipsis` | `uiPaginationEllipsis()` | Elipsis decorativa de paginación con icono de más puntos y texto sr-only. | — |
| `pagination-item` | `uiPaginationItem()` | Elemento li contenedor de cada control de paginación. | *Slot por defecto:* sí. |
| `pagination-link` | `uiPaginationLink()` | Enlace de página con estado activo aria-current y tamaños sm/default/lg/icon. | `href` (string, defecto `'#'`) — URL destino (defecto #); saneada<br>`isActive` (bool, defecto `false`) — bool (defecto false); marca la página actual<br>`size` (string, defecto `'icon'`) — default/sm/lg/icon (defecto icon)<br>*Slot por defecto:* sí. |
| `pagination-next` | `uiPaginationNext()` | Enlace siguiente página con chevron-right y texto oculto bajo sm. | `href` (string, defecto `'#'`) — URL destino (defecto #)<br>`label` (mixed\|null, defecto `null`) — string/null; texto visible (defecto Next)<br>`ariaLabel` (mixed\|null, defecto `null`) — string/null; nombre accesible (defecto Go to next page) |
| `pagination-previous` | `uiPaginationPrevious()` | Enlace página anterior con chevron-left y texto oculto bajo sm. | `href` (string, defecto `'#'`) — URL destino (defecto #)<br>`label` (mixed\|null, defecto `null`) — string/null; texto visible (defecto Previous)<br>`ariaLabel` (mixed\|null, defecto `null`) — string/null; nombre accesible (defecto Go to previous page) |

## Familia `parallax`

> Parallax de scroll que traslada el contenido según speed y eje; respeta motion reducido.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `parallax` | `uiParallax()` | Parallax de scroll que traslada el contenido según speed y eje; respeta motion reducido. | `speed` (float, defecto `0.3`) — float típico -1..1 (defecto 0.3); 0 desactiva; positivo va contra el scroll<br>`axis` (string, defecto `'y'`) — y/x (defecto y); eje del desplazamiento<br>*Slot por defecto:* sí. |

## Familia `password-strength`

> Campo contraseña con ojo mostrar/ocultar, medidor de 4 barras y checklist de reglas.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `password-strength` | `uiPasswordStrength()` | Campo contraseña con ojo mostrar/ocultar, medidor de 4 barras y checklist de reglas. | `name` (string, defecto `'password'`) — nombre del campo (defecto password)<br>`id` (mixed\|null, defecto `null`) — string/null; id (usa name si falta)<br>`placeholder` (string, defecto `'••••••••'`) — texto guía (defecto puntos)<br>`showChecklist` (bool, defecto `true`) — bool (defecto true); lista de requisitos<br>`minLength` (int, defecto `8`) — int mínimo de caracteres (defecto 8)<br>`size` (string, defecto `'default'`) — sm/default/lg; altura del campo<br>`label` (string, defecto `'Password'`) — etiqueta sr-only (defecto Password) |

## Familia `phone-input`

> Input telefónico con selector buscable de país (bandera y prefijo) y campos ocultos.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `phone-input` | `uiPhoneInput()` | Input telefónico con selector buscable de país (bandera y prefijo) y campos ocultos. | `name` (mixed\|null, defecto `null`) — string/null; genera también name_country y name_dial<br>`id` (mixed\|null, defecto `null`) — string/null<br>`value` (string, defecto `''`) — string; número nacional inicial<br>`country` (string, defecto `'US'`) — código ISO inicial (defecto US)<br>`placeholder` (string, defecto `'Phone number'`) — texto guía (defecto Phone number) |

## Familia `popover`

> Raíz del popover que aloja el estado open mediante la isla hotPopover.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `popover` | `uiPopover()` | Raíz del popover que aloja el estado open mediante la isla hotPopover. | *Slot por defecto:* sí. |
| `popover-content` | `uiPopoverContent()` | Contenido del popover teleportado al body, anclado al trigger y con foco atrapado. | `align` (string, defecto `'center'`) — start/center/end (defecto center)<br>`side` (string, defecto `'bottom'`) — lado de anclaje (defecto bottom)<br>`sideOffset` (int, defecto `4`) — px de separación (defecto 4)<br>`label` (string, defecto `'Popover'`) — aria-label del diálogo (defecto Popover)<br>*Slot por defecto:* sí. |
| `popover-trigger` | `uiPopoverTrigger()` | Span disparador que alterna el estado open del popover. | *Slot por defecto:* sí. |

## Familia `presence`

> Indicador de presencia con punto coloreado, pulso opcional y etiqueta visible o sr-only.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `presence` | `uiPresence()` | Indicador de presencia con punto coloreado, pulso opcional y etiqueta visible o sr-only. | `status` (string, defecto `'online'`) — online/away/busy/offline (defecto online)<br>`size` (string, defecto `'default'`) — sm/default/lg; tamaño del punto<br>`pulse` (bool, defecto `false`) — bool (defecto false); ping solo con status online<br>`label` (mixed\|null, defecto `null`) — string/null; etiqueta personalizada<br>`showLabel` (bool, defecto `false`) — bool (defecto false); muestra el texto visible |

## Familia `price`

> Precio con importe anterior tachado y badge de porcentaje de descuento si hay oferta.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `price` | `uiPrice()` | Precio con importe anterior tachado y badge de porcentaje de descuento si hay oferta. | `amount` (int, defecto `0`) — float; precio actual<br>`compareAt` (mixed\|null, defecto `null`) — float/null; precio previo mayor activa la oferta<br>`currency` (string, defecto `'$'`) — símbolo (defecto $)<br>`size` (string, defecto `'default'`) — sm/default/lg; escala tipográfica<br>`showDiscount` (bool, defecto `true`) — bool (defecto true); muestra el badge -N% |

## Familia `product-card`

> Tarjeta de producto con imagen, badge, categoría, rating, precio y acción de compra.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `product-card` | `uiProductCard()` | Tarjeta de producto con imagen, badge, categoría, rating, precio y acción de compra. | `title` (mixed\|null, defecto `null`) — string/null; nombre y fallback del alt<br>`href` (mixed\|null, defecto `null`) — string/null; enlace del título<br>`image` (mixed\|null, defecto `null`) — string/null; URL de imagen<br>`imageAlt` (string, defecto `''`) — string; alt personalizado<br>`price` (mixed\|null, defecto `null`) — float/null<br>`compareAt` (mixed\|null, defecto `null`) — float/null; precio anterior<br>`currency` (string, defecto `'$'`) — símbolo (defecto $)<br>`badge` (mixed\|null, defecto `null`) — string/null; etiqueta sobre la imagen<br>`category` (mixed\|null, defecto `null`) — string/null<br>`rating` (mixed\|null, defecto `null`) — float/null; estrellas readonly<br>`reviews` (mixed\|null, defecto `null`) — número/null; contador entre paréntesis<br>`wishlist` (bool, defecto `false`) — bool (defecto false); corazón toggle<br>*Slot por defecto:* sí. |

## Familia `profile`

> Fila de perfil con avatar o iniciales, nombre y descripción, y chevron opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `profile` | `uiProfile()` | Fila de perfil con avatar o iniciales, nombre y descripción, y chevron opcional. | `name` (mixed\|null, defecto `null`) — string/null<br>`avatar` (mixed\|null, defecto `null`) — string/null; URL de imagen<br>`initials` (mixed\|null, defecto `null`) — string/null; iniciales manuales<br>`description` (mixed\|null, defecto `null`) — string/null; texto secundario<br>`as` (string, defecto `'button'`) — button/a/div (defecto button)<br>`href` (mixed\|null, defecto `null`) — string/null; URL si as=a<br>`size` (string, defecto `'default'`) — sm/default; tamaño del avatar<br>`chevron` (mixed\|null, defecto `null`) — bool/null; muestra chevrons-up-down (por defecto en button)<br>*Slot por defecto:* sí. |

## Familia `progress`

> Progreso lineal o circular con modo indeterminado y porcentaje central opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `progress` | `uiProgress()` | Progreso lineal o circular con modo indeterminado y porcentaje central opcional. | `value` (int, defecto `0`) — 0-100 porcentaje<br>`indeterminate` (bool, defecto `false`) — bool (defecto false)<br>`ariaLabel` (string, defecto `'Progress'`) — nombre accesible (defecto Progress)<br>`circular` (bool, defecto `false`) — bool (defecto false); anillo SVG<br>`size` (int, defecto `64`) — px del anillo (defecto 64)<br>`thickness` (int, defecto `6`) — px del trazo (defecto 6)<br>`showValue` (bool, defecto `false`) — bool (defecto false); % en el centro (circular) |

## Familia `prompt-input`

> Textarea tipo chat con auto-redimensionado, adjunto opcional y envío con Ctrl/Cmd+Enter.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `prompt-input` | `uiPromptInput()` | Textarea tipo chat con auto-redimensionado, adjunto opcional y envío con Ctrl/Cmd+Enter. | `name` (mixed\|null, defecto `null`) — string/null<br>`placeholder` (string, defecto `'Send a message…'`) — texto guía (defecto Send a message…)<br>`attachable` (bool, defecto `false`) — bool (defecto false); botón de clip<br>`disabled` (bool, defecto `false`) — bool (defecto false)<br>`id` (mixed\|null, defecto `null`) — string/null<br>`rows` (int, defecto `1`) — filas iniciales (defecto 1)<br>`maxRows` (int, defecto `6`) — tope de filas al crecer (defecto 6) |

## Familia `qr-code`

> Código QR SVG autogenerado sin dependencias, con nivel ECC configurable y alt accesible.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `qr-code` | `uiQrCode()` | Código QR SVG autogenerado sin dependencias, con nivel ECC configurable y alt accesible. | `value` (string, defecto `''`) — string a codificar<br>`size` (int, defecto `160`) — px del lienzo (defecto 160)<br>`ecc` (string, defecto `'M'`) — L/M/Q/H (defecto M); corrección de errores<br>`margin` (int, defecto `4`) — módulos de margen blanco (defecto 4)<br>`alt` (mixed\|null, defecto `null`) — string/null; aria-label alternativa |

## Familia `quote`

> Cita en blockquote con comillas decorativas y autor con avatar, rol y cite opcionales.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `quote` | `uiQuote()` | Cita en blockquote con comillas decorativas y autor con avatar, rol y cite opcionales. | `author` (mixed\|null, defecto `null`) — string/null<br>`role` (mixed\|null, defecto `null`) — string/null; cargo del autor<br>`avatar` (mixed\|null, defecto `null`) — string/null; URL de imagen<br>`cite` (mixed\|null, defecto `null`) — string/null; URL para el atributo cite<br>*Slot por defecto:* sí. |

## Familia `radio-group`

> Radiogroup accesible con flechas Home/End e input hidden opcional para formularios.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `radio-group` | `uiRadioGroup()` | Radiogroup accesible con flechas Home/End e input hidden opcional para formularios. | `name` (mixed\|null, defecto `null`) — string/null; input hidden con el valor<br>`value` (mixed\|null, defecto `null`) — valor seleccionado inicial<br>*Slot por defecto:* sí. |
| `radio-group-item` | `uiRadioGroupItem()` | Radio individual del grupo con indicador circular, roving tabindex y estado checked. | `value` (mixed\|null, defecto `null`) — string; valor que asigna al pulsar o enfocar<br>`id` (mixed\|null, defecto `null`) — string/null<br>`disabled` (bool, defecto `false`) — bool (defecto false) |

## Familia `rating`

> Valoración de estrellas interactiva o readonly con hover, teclado e icono configurable.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `rating` | `uiRating()` | Valoración de estrellas interactiva o readonly con hover, teclado e icono configurable. | `name` (mixed\|null, defecto `null`) — string/null; label y hidden input<br>`value` (int, defecto `0`) — float inicial (defecto 0)<br>`max` (int, defecto `5`) — int de iconos (defecto 5)<br>`readonly` (bool, defecto `false`) — bool (defecto false)<br>`size` (string, defecto `'default'`) — sm/default/lg<br>`icon` (string, defecto `'star'`) — nombre lucide (defecto star)<br>`color` (string, defecto `'text-amber-500'`) — clase Tailwind del relleno (defecto text-amber-500)<br>`id` (mixed\|null, defecto `null`) — string/null; id del hidden input |

## Familia `reasoning`

> Acordeón que pliega el razonamiento con cabecera brain y colapso animado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `reasoning` | `uiReasoning()` | Acordeón que pliega el razonamiento con cabecera brain y colapso animado. | `open` (bool, defecto `false`) — bool (defecto false)<br>`label` (string, defecto `'Reasoning'`) — texto de cabecera (defecto Reasoning)<br>`duration` (mixed\|null, defecto `null`) — string/null; muestra Thought for {duration}<br>`id` (mixed\|null, defecto `null`) — string/null; id fijo del contenido<br>*Slot por defecto:* sí. |

## Familia `repeater`

> Filas repetibles de formulario con alta/baja, límites min/max y columnas tipadas.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `repeater` | `uiRepeater()` | Filas repetibles de formulario con alta/baja, límites min/max y columnas tipadas. | `name` (string, defecto `'items'`) — base del name enviado como name[indice][clave] (defecto items)<br>`fields` (array, defecto `[]`) — array de {key,label,placeholder?,type?}; columnas<br>`value` (array, defecto `[]`) — array de filas semilla<br>`min` (int, defecto `1`) — int mínimo de filas (defecto 1)<br>`max` (mixed\|null, defecto `null`) — int/null máximo (null ilimitado)<br>`addLabel` (string, defecto `'Add row'`) — texto del botón añadir (defecto Add row) |

## Familia `resizable-panel`

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `resizable-panel-group` | `uiResizablePanelGroup()` | Contenedor flexible cuyo divisor reparte el espacio 10-90% arrastrando o con teclado. | `direction` (string, defecto `'horizontal'`) — horizontal/vertical (defecto horizontal)<br>*Slot por defecto:* sí. |

## Familia `resizable-panel-group`

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `resizable-handle` | `uiResizableHandle()` | Separador arrastrable y operable por teclado entre paneles, con grip opcional. | `withHandle` (bool, defecto `false`) — bool (defecto false); grip vertical visible |
| `resizable-panel` | `uiResizablePanel()` | Panel del grupo redimensionable; el primario toma flex-basis del tamaño calculado. | `primary` (bool, defecto `false`) — bool (defecto false); panel que se redimensiona<br>*Slot por defecto:* sí. |

## Familia `rich-text-editor`

> Editor WYSIWYG contenteditable con toolbar de formato y textarea espejo para el HTML.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `rich-text-editor` | `uiRichTextEditor()` | Editor WYSIWYG contenteditable con toolbar de formato y textarea espejo para el HTML. | `name` (mixed\|null, defecto `null`) — string/null; name del textarea espejo<br>`value` (string, defecto `''`) — HTML inicial<br>`placeholder` (string, defecto `'Write something…'`) — texto guía (defecto Write something…)<br>`id` (mixed\|null, defecto `null`) — string/null; se genera uno si falta |

## Familia `scheduler`

> Rejilla semanal o diaria con gutter horario y eventos posicionados con lanes anti-solape.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `scheduler` | `uiScheduler()` | Rejilla semanal o diaria con gutter horario y eventos posicionados con lanes anti-solape. | `events` (array, defecto `[]`) — array de {title, day, start, end, color?}<br>`days` (mixed\|null, defecto `null`) — array/null; etiquetas de columna (Mon-Sun por defecto)<br>`startHour` (int, defecto `8`) — hora inicial 0-24 (defecto 8)<br>`endHour` (int, defecto `18`) — hora final 0-24 (defecto 18)<br>`view` (string, defecto `'week'`) — week/day (defecto week)<br>`label` (string, defecto `'Schedule'`) — nombre accesible (defecto Schedule) |

## Familia `scroll-area`

> Contenedor con viewport scrollable, scrollbar fino temático y anillo de foco.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `scroll-area` | `uiScrollArea()` | Contenedor con viewport scrollable, scrollbar fino temático y anillo de foco. | *Slot por defecto:* sí. |

## Familia `scrollspy`

> Índice en esta página que resalta la sección visible mediante IntersectionObserver.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `scrollspy` | `uiScrollspy()` | Índice en esta página que resalta la sección visible mediante IntersectionObserver. | `items` (array, defecto `[]`) — array de {href, label, level?}; entradas del índice |

## Familia `segmented-control`

> Selector segmentado de radios nativos ocultos con pastilla elevada en la opción activa.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `segmented-control` | `uiSegmentedControl()` | Selector segmentado de radios nativos ocultos con pastilla elevada en la opción activa. | `name` (mixed\|null, defecto `null`) — string/null; agrupa radios y hace de aria-label<br>`options` (array, defecto `[]`) — array de string o {value,label,icon?}<br>`value` (mixed\|null, defecto `null`) — valor marcado inicial<br>`size` (string, defecto `'default'`) — sm/default/lg<br>`disabled` (bool, defecto `false`) — bool (defecto false) |

## Familia `select`

> Select raíz: listbox custom con chips múltiples o select nativo estilizado sin JS.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `select` | `uiSelect()` | Select raíz: listbox custom con chips múltiples o select nativo estilizado sin JS. | `name` (mixed\|null, defecto `null`) — string/null; envía hidden inputs (name[] en múltiple)<br>`value` (string, defecto `''`) — string/array de valores iniciales<br>`native` (bool, defecto `false`) — bool (defecto false); usa un <select> real<br>`size` (string, defecto `'default'`) — sm/default/lg (solo native)<br>`multiple` (bool, defecto `false`) — bool (defecto false); selección múltiple con chips<br>`options` (mixed\|null, defecto `null`) — map valor=>label; autocompone trigger e items<br>`placeholder` (string, defecto `''`) — texto del trigger vacío (defecto cadena vacía)<br>`color` (mixed\|null, defecto `null`) — string/null; personaliza ring y primario del trigger<br>`indicator` (string, defecto `'check'`) — check/checkbox/radio (defecto check; solo listbox custom)<br>*Slot por defecto:* sí. |
| `select-content` | `uiSelectContent()` | Listbox desplegable teleportado con typeahead, flechas y cierre por Escape o tab. | `align` (string, defecto `'start'`) — start/center/end (defecto start)<br>`side` (string, defecto `'bottom'`) — lado de anclaje (defecto bottom)<br>`sideOffset` (int, defecto `4`) — px de separación (defecto 4)<br>`indicator` (string, defecto `'check'`) — check/checkbox/radio heredado a los items (defecto check)<br>*Slot por defecto:* sí. |
| `select-group` | `uiSelectGroup()` | Agrupa opciones del select (role=group) asociándolas a su select-label. | *Slot por defecto:* sí. |
| `select-item` | `uiSelectItem()` | Opción del listbox con indicador check, checkbox o radio según el estado de selección. | `value` (string, defecto `''`) — string; valor de la opción<br>`disabled` (bool, defecto `false`) — bool (defecto false)<br>`indicator` (string, defecto `'check'`) — check/checkbox/radio (heredado del wrapper, defecto check)<br>*Slot por defecto:* sí. |
| `select-label` | `uiSelectLabel()` | Rótulo pequeño y muted para encabezados de grupo en el select. | *Slot por defecto:* sí. |
| `select-separator` | `uiSelectSeparator()` | Divisoria fina entre grupos de opciones del listbox. | — |
| `select-trigger` | `uiSelectTrigger()` | Botón combobox que abre el listbox y muestra el valor o placeholder con chevron. | `size` (string, defecto `'default'`) — sm/default/lg; altura del botón<br>`ariaLabel` (mixed\|null, defecto `null`) — string/null; nombre accesible explícito<br>*Slot por defecto:* sí. |
| `select-value` | `uiSelectValue()` | Muestra la etiqueta elegida, el placeholder o chips removibles en modo múltiple. | `placeholder` (string, defecto `''`) — texto cuando no hay selección (defecto cadena vacía) |

## Familia `separator`

> Línea divisoria horizontal o vertical; role none si es puramente decorativa.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `separator` | `uiSeparator()` | Línea divisoria horizontal o vertical; role none si es puramente decorativa. | `orientation` (string, defecto `'horizontal'`) — horizontal/vertical (defecto horizontal)<br>`decorative` (bool, defecto `true`) — bool (defecto true); quita la semántica de separador |

## Familia `server-table`

> Tabla server-side con búsqueda, orden, selección, acciones, columnas y paginación por eventos.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `server-table` | `uiServerTable()` | Tabla server-side con búsqueda, orden, selección, acciones, columnas y paginación por eventos. | `columns` (array, defecto `[]`) — array de {key,label,sortable,align,class,width,hideable}<br>`rows` (array, defecto `[]`) — array/Collection/paginator renderizado en servidor<br>`rowKey` (string, defecto `'id'`) — ruta dot de la PK (defecto id)<br>`sort` (mixed\|null, defecto `null`) — string/null; clave de orden activa<br>`direction` (string, defecto `'asc'`) — asc/desc<br>`actions` (array, defecto `[]`) — array de acciones por fila<br>`actionsView` (mixed\|null, defecto `null`) — vista custom por fila con $row<br>`actionsLabel` (string, defecto `'Actions'`) — sr-only de la columna (defecto Actions)<br>`actionsMode` (string, defecto `'inline'`) — inline/dropdown<br>`stickyActions` (bool, defecto `false`) — bool (defecto false); congela la columna derecha<br>`cellViews` (array, defecto `[]`) — map clave=>vista incluida con $value y $row<br>`selectable` (bool, defecto `false`) — bool (defecto false); checkboxes por fila<br>`selectModel` (string, defecto `'selected'`) — modelo del evento table:select (defecto selected)<br>`searchable` (bool, defecto `false`) — bool (defecto false); input de búsqueda<br>`searchModel` (string, defecto `'search'`) — modelo del evento table:search (defecto search)<br>`searchPlaceholder` (string, defecto `'Search...'`) — placeholder del buscador (defecto Search...)<br>`perPageModel` (string, defecto `'perPage'`) — modelo del evento table:per-page (defecto perPage)<br>`perPageOptions` (array, defecto `[]`) — array de tamaños; vacío oculta el select<br>`perPageLabel` (string, defecto `'Rows per page'`) — aria-label del select (defecto Rows per page)<br>`toggleableColumns` (bool, defecto `false`) — bool (defecto false); dropdown de visibilidad<br>`visibleColumns` (mixed\|null, defecto `null`) — array/null de claves visibles (null=todas)<br>`toggleColumnMethod` (string, defecto `'toggleColumn'`) — método del evento (defecto toggleColumn)<br>`columnsLabel` (string, defecto `'Columns'`) — texto del botón columnas (defecto Columns)<br>`caption` (mixed\|null, defecto `null`) — string/null; caption accesible<br>`captionVisible` (bool, defecto `false`) — bool (defecto false); muestra el caption<br>`emptyText` (string, defecto `'No results.'`) — texto sin resultados (defecto No results.)<br>`emptyIcon` (string, defecto `'search-x'`) — icono lucide vacío (defecto search-x)<br>`responsive` (string, defecto `'scroll'`) — scroll/stack (defecto scroll)<br>`variant` (string, defecto `'default'`) — default/card (defecto default)<br>`paginate` (bool, defecto `true`) — bool (defecto true); enlaces del paginator<br>*Slots nombrados:* `toolbar`. |

## Familia `sheet`

> Raíz del sheet que guarda el estado open y admite modo dispatchable con id.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `sheet` | `uiSheet()` | Raíz del sheet que guarda el estado open y admite modo dispatchable con id. | `open` (bool, defecto `false`) — bool (defecto false); estado inicial<br>`id` (mixed\|null, defecto `null`) — string/null; habilita open-sheet-{id}/close-sheet-{id}<br>*Slot por defecto:* sí. |
| `sheet-content` | `uiSheetContent()` | Panel del sheet con overlay, cierre por Escape o botón y lado configurable. | `side` (string, defecto `'right'`) — right/left/top/bottom (defecto right)<br>`showClose` (bool, defecto `true`) — bool (defecto true); botón de cerrar<br>`closeOnOverlay` (bool, defecto `true`) — bool (defecto true); el clic en el fondo cierra<br>*Slot por defecto:* sí. |
| `sheet-description` | `uiSheetDescription()` | Descripción muted del sheet dentro de un párrafo. | *Slot por defecto:* sí. |
| `sheet-footer` | `uiSheetFooter()` | Pie del sheet pegado abajo con acciones apiladas. | *Slot por defecto:* sí. |
| `sheet-header` | `uiSheetHeader()` | Cabecera del sheet que apila título y descripción con separación. | *Slot por defecto:* sí. |
| `sheet-title` | `uiSheetTitle()` | Título h2 del sheet que da nombre accesible al diálogo. | *Slot por defecto:* sí. |
| `sheet-trigger` | `uiSheetTrigger()` | Disparador del sheet; con for abre por dispatch un sheet remoto ya definido. | `for` (mixed\|null, defecto `null`) — string/null; id del sheet dispatchable<br>*Slot por defecto:* sí. |

## Familia `sidebar`

> Sidebar raíz: panel acoplado con colapso offcanvas/icono/none más cajón móvil según el lado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `sidebar` | `uiSidebar()` | Sidebar raíz: panel acoplado con colapso offcanvas/icono/none más cajón móvil según el lado. | `side` (string, defecto `'left'`) — 'left'/'right', por defecto 'left'<br>`variant` (string, defecto `'sidebar'`) — 'sidebar' por defecto (data-variant; CSS soporta 'inset')<br>`collapsible` (string, defecto `'offcanvas'`) — 'offcanvas'/'icon'/'none'<br>*Slot por defecto:* sí. |
| `sidebar-content` | `uiSidebarContent()` | Zona scrollable del sidebar que en modo rail oculta el scrollbar conservando el scroll. | *Slot por defecto:* sí. |
| `sidebar-footer` | `uiSidebarFooter()` | Pie del sidebar: contenedor flex vertical con padding para acciones o contenido final. | *Slot por defecto:* sí. |
| `sidebar-header` | `uiSidebarHeader()` | Cabecera del sidebar: contenedor flex vertical con padding para logo o acciones. | *Slot por defecto:* sí. |
| `sidebar-input` | `uiSidebarInput()` | Campo input estilizado (búsquedas u otros) dentro del sidebar. | — |
| `sidebar-inset` | `uiSidebarInset()` | Área principal main que acompaña al sidebar; se adapta con margen y sombra en variante inset. | *Slot por defecto:* sí. |
| `sidebar-provider` | `uiSidebarProvider()` | Wrapper que provee el estado Alpine del sidebar (abierto/móvil) y las variables CSS de ancho. | `defaultOpen` (bool, defecto `true`) — bool, por defecto true; estado inicial expandido<br>`mobileBreakpoint` (string, defecto `'767px'`) — ancho px o media query ('767px'); bajo él el sidebar es cajón móvil<br>*Slot por defecto:* sí. |
| `sidebar-rail` | `uiSidebarRail()` | Riel invisible en el borde del sidebar para expandir/colapsar con clic. | — |
| `sidebar-separator` | `uiSidebarSeparator()` | Separador horizontal fino entre grupos dentro del sidebar. | — |
| `sidebar-trigger` | `uiSidebarTrigger()` | Botón que alterna el sidebar con icono panel-left y nombre accesible configurable. | `label` (mixed\|null, defecto `null`) — string/null; nombre accesible, por defecto 'Toggle Sidebar' |

## Familia `sidebar-group`

> Agrupa secciones del sidebar (etiqueta y contenido) en columna con padding.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `sidebar-group` | `uiSidebarGroup()` | Agrupa secciones del sidebar (etiqueta y contenido) en columna con padding. | *Slot por defecto:* sí. |
| `sidebar-group-content` | `uiSidebarGroupContent()` | Contenido de un grupo del sidebar; envuelve menús u otros elementos a ancho completo. | *Slot por defecto:* sí. |
| `sidebar-group-label` | `uiSidebarGroupLabel()` | Etiqueta de título de un grupo del sidebar; se desvanece al colapsar a modo iconos. | *Slot por defecto:* sí. |

## Familia `sidebar-menu`

> Lista ul raíz del menú del sidebar; apila los ítems en columna con separación.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `sidebar-menu` | `uiSidebarMenu()` | Lista ul raíz del menú del sidebar; apila los ítems en columna con separación. | *Slot por defecto:* sí. |
| `sidebar-menu-action` | `uiSidebarMenuAction()` | Botón de acción posicionado junto a un ítem de menú del sidebar, visible al hover opcionalmente. | `showOnHover` (bool, defecto `false`) — bool, por defecto false; oculta la acción en md+ hasta hover/focus del botón<br>*Slot por defecto:* sí. |
| `sidebar-menu-badge` | `uiSidebarMenuBadge()` | Insignia numérica absoluta junto al botón de menú del sidebar; oculta en modo iconos. | *Slot por defecto:* sí. |
| `sidebar-menu-button` | `uiSidebarMenuButton()` | Botón o enlace de menú del sidebar con variantes, tamaños, estado activo y tooltip al colapsar. | `href` (mixed\|null, defecto `null`) — string/null; si se define renderiza <a> con URL segura<br>`isActive` (bool, defecto `false`) — bool, marca data-active y aria-current<br>`variant` (string, defecto `'default'`) — 'default'/'outline'<br>`size` (string, defecto `'default'`) — 'default'/'sm'/'lg'<br>`tooltip` (mixed\|null, defecto `null`) — string/null; etiqueta mostrada en tooltip cuando el sidebar está colapsado a iconos<br>*Slot por defecto:* sí. |
| `sidebar-menu-item` | `uiSidebarMenuItem()` | Ítem li contenedor de cada entrada del menú del sidebar. | *Slot por defecto:* sí. |
| `sidebar-menu-sub` | `uiSidebarMenuSub()` | Submenú ul anidado con borde lateral; se oculta cuando el sidebar colapsa a iconos. | *Slot por defecto:* sí. |
| `sidebar-menu-sub-button` | `uiSidebarMenuSubButton()` | Enlace de submenú (segundo nivel) del sidebar con estado activo y tamaño sm/md. | `href` (string, defecto `'#'`) — string, por defecto '#'; URL segura<br>`isActive` (bool, defecto `false`) — bool, marca data-active<br>`size` (string, defecto `'md'`) — 'md'/'sm' (controla el tamaño de texto)<br>*Slot por defecto:* sí. |
| `sidebar-menu-sub-item` | `uiSidebarMenuSubItem()` | Ítem li contenedor de cada entrada del submenú del sidebar. | *Slot por defecto:* sí. |

## Familia `signature-pad`

> Panel de firma dibujable en canvas con deshacer/limpiar y sincronización del data URL.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `signature-pad` | `uiSignaturePad()` | Panel de firma dibujable en canvas con deshacer/limpiar y sincronización del data URL. | `name` (mixed\|null, defecto `null`) — string/null; name del input oculto con el data URL<br>`height` (int, defecto `200`) — int px del lienzo, por defecto 200<br>`penColor` (mixed\|null, defecto `null`) — color CSS/null; por defecto currentColor del tema<br>`id` (mixed\|null, defecto `null`) — string/null; id del input oculto |

## Familia `skeleton`

> Bloque pulsante como marcador visual de carga de contenido pendiente.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `skeleton` | `uiSkeleton()` | Bloque pulsante como marcador visual de carga de contenido pendiente. | — |

## Familia `slider`

> Deslizador simple o de rango doble, horizontal o vertical, con teclado y envío de formulario.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `slider` | `uiSlider()` | Deslizador simple o de rango doble, horizontal o vertical, con teclado y envío de formulario. | `name` (mixed\|null, defecto `null`) — string/null; genera hidden input(s); en rango usa name[min] y name[max]<br>`min` (int, defecto `0`) — número, por defecto 0<br>`max` (int, defecto `100`) — número, por defecto 100<br>`step` (int, defecto `1`) — número, por defecto 1<br>`value` (int, defecto `0`) — número o [low,high] en modo rango<br>`range` (bool, defecto `false`) — bool, habilita dos pulgares<br>`orientation` (string, defecto `'horizontal'`) — 'horizontal'/'vertical'<br>`disabled` (bool, defecto `false`) — bool<br>`ariaLabel` (string, defecto `'Value'`) — string, por defecto 'Value'; etiqueta accesible del pulgar |

## Familia `sonner`

> Toaster estilo Sonner: pila apilada de notificaciones con posición configurable y a11y.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `sonner` | `uiSonner()` | Toaster estilo Sonner: pila apilada de notificaciones con posición configurable y a11y. | `position` (string, defecto `'bottom-right'`) — 'top-left'/'top-center'/'top-right'/'bottom-left'/'bottom-center'/'bottom-right'<br>`expand` (bool, defecto `false`) — bool, por defecto false; mantiene la pila siempre expandida |
| `sonner-flash` | `uiSonnerFlash()` | Puente de toasts: emite un evento toast por cada mensaje flash server-side recibido. | `toasts` (array, defecto `[]`) — array de {type:'success/error/warning/info', description} o strings (type info por defecto) |

## Familia `sparkline`

> Mini gráfico SVG de línea con área opcional para mostrar tendencias en KPIs.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `sparkline` | `uiSparkline()` | Mini gráfico SVG de línea con área opcional para mostrar tendencias en KPIs. | `data` (array, defecto `[]`) — array de números<br>`width` (int, defecto `100`) — int px, por defecto 100<br>`height` (int, defecto `28`) — int px, por defecto 28<br>`area` (bool, defecto `true`) — bool, rellena el área bajo la línea<br>`strokeWidth` (float, defecto `1.5`) — float, grosor del trazo (1.5)<br>`ariaLabel` (string, defecto `'Trend'`) — string, por defecto 'Trend' |

## Familia `speed-dial`

> FAB circular que despliega una pila escalonada de acciones hacia arriba o abajo.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `speed-dial` | `uiSpeedDial()` | FAB circular que despliega una pila escalonada de acciones hacia arriba o abajo. | `actions` (array, defecto `[]`) — array de {icon:lucideName, label:string, href:?}; href crea <a>, sin él <button><br>`direction` (string, defecto `'up'`) — 'up'/'down'<br>`open` (bool, defecto `false`) — bool, estado inicial abierto<br>`icon` (string, defecto `'plus'`) — nombre lucide del FAB, por defecto 'plus'<br>`label` (string, defecto `'Open actions'`) — string, etiqueta accesible del FAB |

## Familia `spinner`

> Indicador de carga circular animado basado en un icono lucide girando.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `spinner` | `uiSpinner()` | Indicador de carga circular animado basado en un icono lucide girando. | `icon` (string, defecto `'loader-circle'`) — nombre lucide, por defecto 'loader-circle' |

## Familia `spotlight-card`

> Tarjeta con brillo radial decorativo que sigue el cursor al pasar por encima.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `spotlight-card` | `uiSpotlightCard()` | Tarjeta con brillo radial decorativo que sigue el cursor al pasar por encima. | `color` (mixed\|null, defecto `null`) — color CSS/null del brillo; por defecto mezcla sutil del token foreground<br>`size` (int, defecto `350`) — int px del radio del foco, por defecto 350<br>*Slot por defecto:* sí. |

## Familia `stack`

> Layout flex genérico configurable: dirección, gap, alineación, justificado y wrap.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `stack` | `uiStack()` | Layout flex genérico configurable: dirección, gap, alineación, justificado y wrap. | `direction` (string, defecto `'col'`) — 'col'/'row'<br>`gap` (string, defecto `'4'`) — '0','1','2','3','4','5','6','8','10','12' (escala)<br>`align` (mixed\|null, defecto `null`) — 'start'/'center'/'end'/'stretch'/'baseline'/null<br>`justify` (mixed\|null, defecto `null`) — 'start'/'center'/'end'/'between'/'around'/'evenly'/null<br>`wrap` (bool, defecto `false`) — bool, activa flex-wrap<br>*Slot por defecto:* sí. |

## Familia `stat`

> Tarjeta KPI con etiqueta, valor grande, cambio con tendencia e icono o sparkline.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `stat` | `uiStat()` | Tarjeta KPI con etiqueta, valor grande, cambio con tendencia e icono o sparkline. | `label` (mixed\|null, defecto `null`) — string/null; texto superior<br>`value` (mixed\|null, defecto `null`) — string/null; valor grande; si es null usa el slot<br>`change` (mixed\|null, defecto `null`) — string/null; ej '+12.5%' o '-3.2%'<br>`trend` (mixed\|null, defecto `null`) — 'up'/'down'/'neutral'/null; inferido del signo de change<br>`icon` (mixed\|null, defecto `null`) — nombre lucide/null en recuadro atenuado<br>`caption` (mixed\|null, defecto `null`) — string/null; texto junto al cambio<br>`series` (mixed\|null, defecto `null`) — array de números/null; dibuja una sparkline<br>*Slots nombrados:* `leading`, `trailing`. |

## Familia `stepper`

> Contenedor raíz del stepper con el paso actual y orientación horizontal o vertical.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `stepper` | `uiStepper()` | Contenedor raíz del stepper con el paso actual y orientación horizontal o vertical. | `value` (int, defecto `1`) — int, paso activo inicial (1-based), por defecto 1<br>`orientation` (string, defecto `'horizontal'`) — 'horizontal'/'vertical'<br>*Slot por defecto:* sí. |
| `stepper-content` | `uiStepperContent()` | Panel de contenido de un paso del stepper; visible solo mientras su paso está activo. | `step` (int, defecto `1`) — int, número del paso al que pertenece (1-based)<br>*Slot por defecto:* sí. |
| `stepper-description` | `uiStepperDescription()` | Texto secundario descriptivo bajo el título de un paso del stepper. | *Slot por defecto:* sí. |
| `stepper-indicator` | `uiStepperIndicator()` | Círculo numerado del paso: muestra número o icono y un check al completarse. | *Slot por defecto:* sí. |
| `stepper-item` | `uiStepperItem()` | Ítem li de un paso; expone itemStep y estado completed/active/inactive a sus hijos. | `step` (int, defecto `1`) — int, posición del paso (1-based)<br>`disabled` (bool, defecto `false`) — bool<br>*Slot por defecto:* sí. |
| `stepper-nav` | `uiStepperNav()` | Riel ol que lista los pasos; cambia de dirección según la orientación del stepper. | *Slot por defecto:* sí. |
| `stepper-separator` | `uiStepperSeparator()` | Conector entre pasos que se pinta primario cuando el paso previo está completado. | — |
| `stepper-title` | `uiStepperTitle()` | Título del paso mostrado dentro del trigger del stepper. | *Slot por defecto:* sí. |
| `stepper-trigger` | `uiStepperTrigger()` | Botón clicable que salta el stepper al paso correspondiente. | *Slot por defecto:* sí. |

## Familia `streaming-text`

> Texto revelado token a token estilo LLM, con caret opcional y arranque automático.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `streaming-text` | `uiStreamingText()` | Texto revelado token a token estilo LLM, con caret opcional y arranque automático. | `text` (string, defecto `''`) — string; pasaje completo a revelar; si vacío usa el slot<br>`speed` (int, defecto `18`) — ms por fragmento, por defecto 18<br>`startDelay` (int, defecto `0`) — ms antes del primer fragmento, por defecto 0<br>`by` (string, defecto `'char'`) — 'char'/'word'<br>`caret` (bool, defecto `true`) — bool, muestra cursor parpadeante mientras emite<br>`autostart` (bool, defecto `true`) — bool, inicia al montar<br>*Slot por defecto:* sí. |

## Familia `switch`

> Interruptor on/off accesible con tamaños sm/default/lg y campo oculto opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `switch` | `uiSwitch()` | Interruptor on/off accesible con tamaños sm/default/lg y campo oculto opcional. | `id` (mixed\|null, defecto `null`) — string/null<br>`name` (mixed\|null, defecto `null`) — string/null; genera hidden input cuando está checked<br>`value` (string, defecto `'on'`) — string enviado por el campo, por defecto 'on'<br>`checked` (bool, defecto `false`) — bool, estado inicial<br>`disabled` (bool, defecto `false`) — bool<br>`size` (string, defecto `'default'`) — 'sm'/'default'/'lg' |

## Familia `table`

> Tabla responsiva con scroll horizontal; la variante card añade borde, fondo y sombra.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `table` | `uiTable()` | Tabla responsiva con scroll horizontal; la variante card añade borde, fondo y sombra. | `variant` (string, defecto `'default'`) — 'default'/'card'<br>*Slot por defecto:* sí. |
| `table-body` | `uiTableBody()` | Cuerpo tbody de la tabla; elimina el borde de la última fila. | *Slot por defecto:* sí. |
| `table-caption` | `uiTableCaption()` | Caption de la tabla con texto atenuado situado bajo la tabla. | *Slot por defecto:* sí. |
| `table-cell` | `uiTableCell()` | Celda td de datos con alineación media y ajustes para checkboxes. | *Slot por defecto:* sí. |
| `table-footer` | `uiTableFooter()` | Pie tfoot de la tabla con fondo tenue, borde superior y fuente media. | *Slot por defecto:* sí. |
| `table-head` | `uiTableHead()` | Celda th de encabezado con scope=col automático y alineación al inicio. | *Slot por defecto:* sí. |
| `table-header` | `uiTableHeader()` | Encabezado thead de la tabla; aplica borde inferior a sus filas. | *Slot por defecto:* sí. |
| `table-row` | `uiTableRow()` | Fila tr con hover tenue, borde inferior y resaltado en estado selected. | *Slot por defecto:* sí. |

## Familia `tabs`

> Contenedor raíz de pestañas con el tab inicial y orientación horizontal o vertical.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tabs` | `uiTabs()` | Contenedor raíz de pestañas con el tab inicial y orientación horizontal o vertical. | `value` (mixed\|null, defecto `null`) — tab activo inicial (valor del trigger)/null<br>`orientation` (string, defecto `'horizontal'`) — 'horizontal'/'vertical'<br>*Slot por defecto:* sí. |
| `tabs-content` | `uiTabsContent()` | Panel de contenido asociado a un tab; solo visible cuando su valor coincide con el activo. | `value` (mixed\|null, defecto `null`) — string/null; identificador del tab cuyo panel renderiza<br>*Slot por defecto:* sí. |
| `tabs-list` | `uiTabsList()` | Contenedor tablist con variantes segmented, underline o pills y navegación por flechas. | `variant` (string, defecto `'segmented'`) — 'segmented'/'underline'/'pills'<br>*Slot por defecto:* sí. |
| `tabs-trigger` | `uiTabsTrigger()` | Botón role=tab que activa su panel; estiliza según la variante heredada del tabs-list. | `value` (mixed\|null, defecto `null`) — string/null; identificador del tab que activa<br>`disabled` (bool, defecto `false`) — bool; deshabilita el trigger<br>*Slot por defecto:* sí. |

## Familia `tags-input`

> Input de etiquetas con chips removibles, alta por Enter/coma/blur y límite máximo.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tags-input` | `uiTagsInput()` | Input de etiquetas con chips removibles, alta por Enter/coma/blur y límite máximo. | `name` (mixed\|null, defecto `null`) — string/null; genera inputs hidden name[] por tag<br>`value` (array, defecto `[]`) — array inicial de strings<br>`placeholder` (string, defecto `'Add tag…'`) — string, por defecto 'Add tag…'<br>`max` (mixed\|null, defecto `null`) — int/null; tope de etiquetas<br>`disabled` (bool, defecto `false`) — bool<br>`id` (mixed\|null, defecto `null`) — string/null; id del campo de texto |

## Familia `terminal`

> Ventana de terminal oscura con barra de título, semáforos opcionales y cuerpo monoespaciado.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `terminal` | `uiTerminal()` | Ventana de terminal oscura con barra de título, semáforos opcionales y cuerpo monoespaciado. | `title` (mixed\|null, defecto `null`) — string/null; etiqueta de la barra de título<br>`buttons` (bool, defecto `true`) — bool, muestra los puntos rojo/ámbar/verde, por defecto true<br>*Slot por defecto:* sí. |

## Familia `text-reveal`

> Párrafo cuyas palabras se iluminan progresivamente al recorrerlo el scroll.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `text-reveal` | `uiTextReveal()` | Párrafo cuyas palabras se iluminan progresivamente al recorrerlo el scroll. | `as` (string, defecto `'p'`) — etiqueta HTML del wrapper, por defecto 'p'<br>*Slot por defecto:* sí. |

## Familia `textarea`

> Textarea autoexpandible con tamaños, color de foco personalizable y tope maxRows.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `textarea` | `uiTextarea()` | Textarea autoexpandible con tamaños, color de foco personalizable y tope maxRows. | `size` (string, defecto `'default'`) — 'sm'/'default'/'lg'<br>`color` (mixed\|null, defecto `null`) — color CSS/null; personaliza ring y primary locales<br>`rows` (mixed\|null, defecto `null`) — int/null; filas visibles iniciales<br>`maxRows` (mixed\|null, defecto `null`) — int/null; cap de crecimiento vía Alpine<br>*Slot por defecto:* sí. |

## Familia `tilt-card`

> Tarjeta con inclinación 3D hacia el cursor, escala al hover y destello opcional.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tilt-card` | `uiTiltCard()` | Tarjeta con inclinación 3D hacia el cursor, escala al hover y destello opcional. | `max` (int, defecto `12`) — grados máximos de inclinación por eje, por defecto 12<br>`scale` (float, defecto `1.03`) — factor de escala al hover, por defecto 1.03<br>`glare` (bool, defecto `false`) — bool, overlay de destello que sigue el puntero<br>*Slot por defecto:* sí. |

## Familia `time-field`

> Selector de hora nativo tipo input o tres selects, con ciclo 12/24 h y segundos opcionales.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `time-field` | `uiTimeField()` | Selector de hora nativo tipo input o tres selects, con ciclo 12/24 h y segundos opcionales. | `name` (mixed\|null, defecto `null`) — string/null; genera hidden input con el value<br>`value` (mixed\|null, defecto `null`) — 'HH:mm' o 'HH:mm:ss'/null<br>`variant` (string, defecto `'input'`) — 'input'/'select'<br>`hourCycle` (string, defecto `'auto'`) — 'auto'/'12'/'24'<br>`seconds` (bool, defecto `false`) — bool, añade select/input de segundos<br>`minuteStep` (int, defecto `1`) — int, incremento de minutos (1)<br>`secondStep` (int, defecto `1`) — int, incremento de segundos (1)<br>`min` (mixed\|null, defecto `null`) — 'HH:mm'/null (variante input)<br>`max` (mixed\|null, defecto `null`) — 'HH:mm'/null (variante input)<br>`disabled` (bool, defecto `false`) — bool<br>`id` (mixed\|null, defecto `null`) — string/null (variante input)<br>`part` (mixed\|null, defecto `null`) — string/null; etiqueta devuelta en el evento time-change |

## Familia `timeline`

> Lista ol vertical de línea temporal; oculta automáticamente el conector de la última entrada.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `timeline` | `uiTimeline()` | Lista ol vertical de línea temporal; oculta automáticamente el conector de la última entrada. | *Slot por defecto:* sí. |
| `timeline-item` | `uiTimelineItem()` | Entrada de línea temporal con punto marcador (icono o dot), hora, título y contenido. | `icon` (mixed\|null, defecto `null`) — nombre lucide/null en el punto; sin él, dot sólido<br>`time` (mixed\|null, defecto `null`) — string/null; hora o eyebrow sobre el título<br>`title` (mixed\|null, defecto `null`) — string/null; título del hito<br>`active` (bool, defecto `false`) — bool; resalta el punto en color primario<br>*Slot por defecto:* sí. |

## Familia `toggle`

> Botón de dos estados (presionado/no) con variantes default/outline y tamaños sm/default/lg.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `toggle` | `uiToggle()` | Botón de dos estados (presionado/no) con variantes default/outline y tamaños sm/default/lg. | `variant` (string, defecto `'default'`) — 'default'/'outline'<br>`size` (string, defecto `'default'`) — 'default'/'sm'/'lg'<br>`pressed` (bool, defecto `false`) — bool, estado inicial<br>`disabled` (bool, defecto `false`) — bool<br>*Slot por defecto:* sí. |

## Familia `toggle-group`

> Grupo de botones togglables de selección única o múltiple con navegación por flechas.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `toggle-group` | `uiToggleGroup()` | Grupo de botones togglables de selección única o múltiple con navegación por flechas. | `type` (string, defecto `'single'`) — 'single'/'multiple'<br>`value` (mixed\|null, defecto `null`) — valor activo: string/null en single, array en multiple<br>`variant` (string, defecto `'default'`) — 'default'/'outline'<br>`size` (string, defecto `'default'`) — 'default'/'sm'/'lg'<br>`orientation` (string, defecto `'horizontal'`) — 'horizontal'/'vertical'<br>*Slot por defecto:* sí. |
| `toggle-group-item` | `uiToggleGroupItem()` | Botón individual de un grupo de toggles; hereda variante y tamaño del grupo padre. | `value` (mixed\|null, defecto `null`) — string/null; valor que aporta al grupo<br>`disabled` (bool, defecto `false`) — bool<br>*Slot por defecto:* sí. |

## Familia `tool-call`

> Acordeón de llamada a herramienta IA: nombre, estado, argumentos y resultado en JSON.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tool-call` | `uiToolCall()` | Acordeón de llamada a herramienta IA: nombre, estado, argumentos y resultado en JSON. | `name` (string, defecto `'tool'`) — string del tool, por defecto 'tool'<br>`status` (string, defecto `'success'`) — 'pending'/'running'/'success'/'error'<br>`args` (mixed\|null, defecto `null`) — array/string/null; array se imprime como JSON formateado<br>`result` (mixed\|null, defecto `null`) — array/string/null; igual que args<br>`open` (bool, defecto `false`) — bool, cuerpo inicialmente expandido<br>`id` (mixed\|null, defecto `null`) — string/null; data-tool-id del trigger |

## Familia `tooltip`

> Envoltorio que abre un tooltip en hover/focus con retardo y cierre con Escape.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tooltip` | `uiTooltip()` | Envoltorio que abre un tooltip en hover/focus con retardo y cierre con Escape. | `delay` (int, defecto `0`) — ms antes de abrir, por defecto 0<br>*Slot por defecto:* sí. |
| `tooltip-content` | `uiTooltipContent()` | Burbuja de tooltip teletransportada con lado/alineación configurables y flecha opcional. | `side` (string, defecto `'top'`) — 'top'/'bottom'/'left'/'right'<br>`align` (string, defecto `'center'`) — 'center'/'start'/'end'<br>`sideOffset` (int, defecto `4`) — px de separación del disparador (4)<br>`arrow` (bool, defecto `true`) — bool, muestra la flecha<br>`state` (string, defecto `'open'`) — expresión Alpine de visibilidad, por defecto 'open'<br>*Slot por defecto:* sí. |
| `tooltip-trigger` | `uiTooltipTrigger()` | Span disparador del tooltip que sirve de anclaje al contenido teletransportado. | *Slot por defecto:* sí. |

## Familia `top-progress`

> Barra fina de progreso de carga estilo NProgress fijada arriba; controlada por eventos window.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `top-progress` | `uiTopProgress()` | Barra fina de progreso de carga estilo NProgress fijada arriba; controlada por eventos window. | `color` (mixed\|null, defecto `null`) — color CSS/null de la barra; por defecto token primario<br>`height` (int, defecto `2`) — grosor en px, por defecto 2<br>`demo` (bool, defecto `false`) — bool; render en flujo (no fixed) para vistas previas |

## Familia `tree`

> Árbol jerárquico colapsable desde datos con roles ARIA tree/treeitem y teclado de flechas.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tree` | `uiTree()` | Árbol jerárquico colapsable desde datos con roles ARIA tree/treeitem y teclado de flechas. | `items` (array, defecto `[]`) — lista de {label:string, icon:?lucideName, children:?array, expanded:?bool} |
| `tree-node` | `uiTreeNode()` | Nodo recursivo del árbol con chevron, icono carpeta/archivo e indentación segura RTL. | `item` (array, defecto `[]`) — {label:string, icon:?lucideName, children:?array, expanded:?bool}<br>`level` (int, defecto `1`) — int, profundidad 1-based para indentación y aria-level<br>`first` (bool, defecto `false`) — bool; primera fila global que recibe tabindex=0 |

## Familia `tree-table`

> Tabla jerárquica con filas expandibles por ruta, columnas configurables y copia markdown.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `tree-table` | `uiTreeTable()` | Tabla jerárquica con filas expandibles por ruta, columnas configurables y copia markdown. | `columns` (array, defecto `[]`) — lista de {key:string, label:string, align:?'left/center/right'}<br>`rows` (array, defecto `[]`) — lista de {<key>:valor..., children:?[mismo formato], expanded:?bool}<br>`copyable` (bool, defecto `false`) — bool; botón que copia la jerarquía como markdown ├──/└── |
| `tree-table-row` | `uiTreeTableRow()` | Fila recursiva del tree-table con chevron expandible e indentación según profundidad. | `row` (array, defecto `[]`) — datos de la fila {clave:valor...}; puede llevar 'children'=>[...]<br>`columns` (array, defecto `[]`) — config compartida de columnas heredada del padre<br>`path` (string, defecto `'0'`) — string id de ruta punteada ('0', '0.1', '0.1.2')<br>`depth` (int, defecto `0`) — int, profundidad 0-based que define la sangría |

## Familia `typewriter`

> Texto máquina de escribir que cicla palabras con borrado, pausa y cursor parpadeante.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `typewriter` | `uiTypewriter()` | Texto máquina de escribir que cicla palabras con borrado, pausa y cursor parpadeante. | `words` (array, defecto `[]`) — array de frases a ciclar; si vacío usa el slot<br>`typeSpeed` (int, defecto `90`) — ms por carácter tecleado (90)<br>`deleteSpeed` (int, defecto `40`) — ms por carácter borrado (40)<br>`pause` (int, defecto `1600`) — ms de pausa con la palabra completa (1600)<br>`loop` (bool, defecto `true`) — bool, cicla indefinidamente (true)<br>`cursor` (bool, defecto `true`) — bool, muestra caret parpadeante<br>*Slot por defecto:* sí. |

## Familia `typography`

> Bloque tipográfico semántico con variantes de encabezado, párrafo, cita, código y gradiente.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `typography` | `uiTypography()` | Bloque tipográfico semántico con variantes de encabezado, párrafo, cita, código y gradiente. | `variant` (string, defecto `'p'`) — 'h1'/'h2'/'h3'/'h4'/'p'/'lead'/'large'/'small'/'muted'/'blockquote'/'inline-code'/'list'/'gradient'<br>`as` (mixed\|null, defecto `null`) — etiqueta HTML/null; sobrescribe la etiqueta de la variante<br>*Slot por defecto:* sí. |

## Familia `variant-selector`

> Radiogroup de variantes como pills de texto o swatches de color con estados disabled.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `variant-selector` | `uiVariantSelector()` | Radiogroup de variantes como pills de texto o swatches de color con estados disabled. | `name` (mixed\|null, defecto `null`) — string/null; name del radio group<br>`options` (array, defecto `[]`) — array de strings o de {value,label,color,disabled}<br>`value` (mixed\|null, defecto `null`) — valor preseleccionado/null<br>`type` (string, defecto `'pill'`) — 'pill'/'color'<br>`label` (string, defecto `'Variant'`) — string, título del grupo ('Variant')<br>`disabled` (bool, defecto `false`) — bool; deshabilita todas las opciones |

## Familia `video`

> Reproductor HTML5 estilizado con póster, overlay de play y relación de aspecto configurable.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `video` | `uiVideo()` | Reproductor HTML5 estilizado con póster, overlay de play y relación de aspecto configurable. | `src` (mixed\|null, defecto `null`) — URL del vídeo/null; también acepta <source> vía slot<br>`poster` (mixed\|null, defecto `null`) — URL de imagen previa/null<br>`aspect` (string, defecto `'video'`) — 'video'/'square'/ratio custom como '4/3'<br>`controls` (bool, defecto `true`) — bool, controles nativos (true)<br>`autoplay` (bool, defecto `false`) — bool; omite overlay de play<br>`loop` (bool, defecto `false`) — bool<br>`muted` (bool, defecto `false`) — bool<br>`rounded` (string, defecto `'rounded-xl'`) — utilidad de redondeo, por defecto 'rounded-xl'<br>*Slot por defecto:* sí. |

## Familia `visually-hidden`

> Contenido oculto visualmente (sr-only); puede revelarse al enfocarlo como skip-link RTL-safe.

| Componente | Método | Descripción | Props / Slots |
|---|---|---|---|
| `visually-hidden` | `uiVisuallyHidden()` | Contenido oculto visualmente (sr-only); puede revelarse al enfocarlo como skip-link RTL-safe. | `as` (string, defecto `'span'`) — etiqueta HTML a renderizar, por defecto 'span'<br>`focusable` (bool, defecto `false`) — bool; al enfocar se muestra fijo arriba como enlace de salto<br>*Slot por defecto:* sí. |

---

## Ejemplos de composición

### Botones

```php
<?= ui()->uiButton([], 'Guardar') ?>
<?= ui()->uiButton(['variant' => 'outline', 'size' => 'sm'], 'Cancelar') ?>
<?= ui()->uiButton(['variant' => 'destructive', 'colorForeground' => '#fff'], 'Eliminar') ?>
<?= ui()->uiButton(['href' => '/docs'], 'Documentación') // <a> validado con safe_url() ?>
```

El prop `color` reescribe los tokens `--primary`/`--ring` solo en ese elemento, así las
clases token-driven lo recolorean sin CSS a medida.

### Tarjeta

```php
<?= ui()->uiCard(['variant' => 'sectioned'], function (): void { ?>
    <?= ui()->uiCardHeader([], function (): void { ?>
        <?= ui()->uiCardTitle([], 'Iniciar sesión') ?>
        <?= ui()->uiCardDescription([], 'Introduce tus credenciales') ?>
    <?php }) ?>
    <?= ui()->uiCardContent([], function (): void { ?>
        <?= ui()->uiInput(['type' => 'email', 'placeholder' => 'tu@correo.com']) ?>
    <?php }) ?>
    <?= ui()->uiCardFooter([], function (): void { ?>
        <?= ui()->uiButton(['class' => 'w-full'], 'Entrar') ?>
    <?php }) ?>
<?php }) ?>
```

### Alerta e insignia con tonos semánticos

```php
<?= ui()->uiAlert(['tone' => 'success'], function (): void { ?>
    <?= ui()->uiAlertTitle([], 'Guardado') ?>
    <?= ui()->uiAlertDescription([], 'Los cambios se publicaron correctamente.') ?>
<?php }) ?>

<?= ui()->uiBadge(['tone' => 'warning', 'variant' => 'soft'], 'Beta') ?>
```

### Select con opciones automáticas

```php
<?= ui()->uiSelect([
    'name' => 'framework',
    'options' => ['laravel' => 'Laravel', 'symfony' => 'Symfony'],
    'placeholder' => 'Elige framework',
]) ?>
```

En modo listbox los items admiten `indicator` (`check`, `checkbox`, `radio`), normalmente
fijado una vez en el wrapper y heredado por cada `select-item` mediante `share()`/`aware()`.

### Diálogo modal

```php
<?= ui()->uiDialog([], function (): void { ?>
    <?= ui()->uiDialogTrigger([], ui()->uiButton(['variant' => 'outline'], 'Abrir')) ?>
    <?= ui()->uiDialogContent([], function (): void { ?>
        <?= ui()->uiDialogHeader([], function (): void { ?>
            <?= ui()->uiDialogTitle([], 'Confirmar') ?>
            <?= ui()->uiDialogDescription([], 'Esta acción no se puede deshacer.') ?>
        <?php }) ?>
        <?= ui()->uiDialogFooter([], function (): void { ?>
            <?= ui()->uiDialogClose([], ui()->uiButton(['variant' => 'ghost'], 'Cerrar')) ?>
        <?php }) ?>
    <?php }) ?>
<?php }) ?>
```

Con `id`, el diálogo es dispatchable: `open-dialog-{id}` / `close-dialog-{id}` desde JS.

### Pestañas

```php
<?= ui()->uiTabs(['value' => 'account'], function (): void { ?>
    <?= ui()->uiTabsList(['variant' => 'segmented'], function (): void { ?>
        <?= ui()->uiTabsTrigger(['value' => 'account'], 'Cuenta') ?>
        <?= ui()->uiTabsTrigger(['value' => 'password'], 'Contraseña') ?>
    <?php }) ?>
    <?= ui()->uiTabsContent(['value' => 'account'], '…') ?>
    <?= ui()->uiTabsContent(['value' => 'password'], '…') ?>
<?php }) ?>
```

### KPI con sparkline

```php
<?= ui()->uiStat([
    'label' => 'Ingresos',
    'value' => '$45.2k',
    'change' => '+12.5%',
    'series' => [12, 18, 14, 22, 30, 28],
]) ?>
```

`stat` expone además los slots nombrados `leading` y `trailing`.
