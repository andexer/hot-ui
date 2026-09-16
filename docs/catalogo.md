# Catálogo de componentes Hot-UI

384 componentes en `views/components/{ui,blocks}/`. Esta tabla mapea cada familia
con su isla TypeScript (cuando existe) y el motor del kernel que usa.

Leyenda: **Isla** = `js/src/components/ui/*.ts` · **Motor** = registrado por el
kernel (`hot/register.ts`) · **—** = solo CSS/directivas, sin JS propio.

| Familia | Isla / Motor | Notas |
|---|---|---|
| accordion + collapsible | Isla `accordion.ts` (`hotAccordion`, `hotCollapsible`) | usa `x-hot-collapse` |
| alert-dialog | Isla `alert-dialog.ts` (`hotAlertDialog`) | focus-trap compartido |
| autocomplete | — (delega en combobox) | alias PHP puro |
| avatar, badge, breadcrumb, button*, card*, separator, skeleton, spinner… | — | primitivos sin JS |
| calendar | Isla `calendar.ts` (`hotCalendar`) | single/multiple/range, teclado APG |
| carousel | Isla `carousel.ts` (`hotCarousel`) | swipe táctil |
| chart* | — (aplazado) | stub para ApexCharts futuro |
| color-picker | Isla `color-picker.ts` (`hotColorPicker`) | HSL↔HEX, paleta |
| command (+command-dialog) | Motor `hotCommand` + Isla `command.ts` (`hotCommandPalette`) | palette = shell modal |
| context-menu | Motor `hotMenu` (`openAt`) | pointer position |
| countdown | Isla `countdown.ts` (`hotCountdown`) | tick 1s |
| data-table | Isla `data-table.ts` (`hotDataTable`) | sort/filter/paginación |
| date-picker / datetime-picker | Islas propias (componen `hotCalendar` + popover) | |
| dialog | Isla `dialog.ts` (`hotDialog`) | trap + Esc + overlay |
| dock, drawer | `drawer` → Isla propia; dock → — | |
| dropdown-menu | Motor `hotMenu` | sin isla |
| file-upload | Isla `file-upload.ts` (`hotFileUpload`) | drag&drop nativo |
| gantt / scheduler | — (geometría server-side) | cero Alpine |
| hover-card / tooltip | — (timer inline mínimo) | |
| infinite-scroll | Isla `infinite-scroll.ts` | IntersectionObserver |
| input-otp | — (`$hot.model` + `$refs`) | sin isla |
| input-mask | Isla `input-mask.ts` (`hotInputMask`) | lee `data-mask` |
| json-viewer | — (recursión PHP) | |
| kanban | Isla `kanban.ts` (`hotKanban`) | drag&drop |
| markdown-editor | Isla `markdown-editor.ts` (`hotMarkdownEditor`) | renderer safe-subset |
| mention-input | Isla `mention-input.ts` (`hotMentionInput`) | |
| menubar | Motor `hotMenubar` | roving tabindex |
| number-ticker / typewriter / streaming-text / text-reveal | Islas propias | animaciones texto |
| onboarding-tour | Isla `onboarding-tour.ts` | spotlight + flip |
| org-chart, pagination*, price, profile… | — | server-rendered |
| popover | Isla `popover.ts` (`hotPopover`) | trap no-modal |
| progress, rating, skeleton, slider… | — | CSS/inline mínimo |
| rich-text-editor | Isla `rich-text-editor.ts` (`hotRichTextEditor`) | execCommand wrapper |
| scrollspy | Isla `scrollspy.ts` (`hotScrollspy`) | IO secciones |
| select (+items) | Motor `hotSelect` | seedSelected por ítem |
| sheet | Isla `sheet.ts` (`hotSheet`) | |
| signature-pad | Isla `signature-pad.ts` (`hotSignaturePad`) | canvas DPR |
| sidebar (+provider) | Isla `sidebar.ts` (`hotSidebar`) | matchMedia móvil |
| sonner (+flash) | — (escucha eventos `toast` de `window.toast`) | |
| stepper / tabs / navigation-menu / bottom-navigation | — (`$hot.nav` cubre teclado) | portadores triviales |
| tags-input | Isla `tags-input.ts` (`hotTagsInput`) | |
| time-field | Isla `time-field.ts` (`hotTimeField`) | HH:mm(:ss) |
| toggle-group, tree-table | tree → Isla `tree.ts` (`hotTree`,`hotTreeTable`); toggle-group → — | |
| tooltip-trigger/content | — | ancla `x-hot-anchor` |
| tree | Isla `tree.ts` | roving focus |
| blocks/file-tree | consume `hotTree`/`hotTreeTable` | espejo block/ |

## Motores del kernel (registrados siempre)

| Registro | Archivo | Uso |
|---|---|---|
| `hotMenu` | hot/engines/menu.ts | disclosure menus, submenús |
| `hotMenubar` | hot/engines/menubar.ts | barra de menús ARIA |
| `hotSelect` | hot/engines/select.ts | listbox <select> |
| `hotListbox` | hot/engines/listbox.ts | combobox/autocomplete |
| `hotCommand` | hot/engines/command.ts | paleta de comandos |

## Directivas y magic

`x-hot-trigger` · `x-hot-labelledby` · `x-hot-anchor` · `x-hot-dialog-layer` ·
`x-hot-field` · `x-hot-collapse` · `$hot.model/.nav/.type/.number` · store
`theme`.
