# TODO de Hotfire

Hotfire es la capa reactiva de Hot-UI para PHP/CodeIgniter. Esta lista apunta a
cerrar brechas de producto sin convertir la API publica en `wire:*`: el contrato
externo sigue siendo `hot:*`, eventos `hotfire:*` y componentes Hotfire.

## Prioridad 1 - base usable en apps reales

- [x] Directivas de accion y estado: `hot:submit`, `hot:change`, `hot:key`,
  `hot:loading`, `hot:dirty`, `hot:target`, `hot:confirm`, `hot:init`.
- [x] Modifiers de `hot:model`: `.live`, `.debounce`, `.lazy`, `.blur`,
  `.defer` y delays `300ms`.
- [x] Binding de formularios completo: radios, checkboxes agrupados, arrays,
  selects multiples, valores anidados tipo `user.name` y errores por campo.
- [x] Validacion integrada con CodeIgniter: `rules()`, `validate()`,
  `validateOnly()`, mensajes custom y bolsa de errores serializable.
- [x] Allowlist explicita de acciones: atributo/metodo protegido para evitar
  que cualquier metodo publico del componente sea invocable por accidente.
- [x] CSRF sin excluir la ruta: lectura automatica del token CI4 y envio en el
  header/request JSON.

## Prioridad 2 - experiencia de componente

- [x] Ciclo de vida completo: `boot`, `mount($params)`, `hydrate`,
  `dehydrate`, `updating*`, `updated*`, hooks antes/despues de accion.
- [x] Propiedades protegidas del cliente: locked state, computed state y state
  derivado que no viaje en el snapshot.
- [x] Eventos entre componentes: dispatch server/client, listeners globales,
  listeners dirigidos por nombre/id y browser events.
- [x] Respuestas especiales desde acciones: redirect, flash/toast, download,
  no-content y payloads auxiliares para el driver.
- [x] Componentes anidados con keys estables y re-render aislado.

## Prioridad 3 - navegacion, archivos y productividad

- [x] Navegacion Hotfire: enlaces con prefetch, history, scroll restoration y
  elementos persistentes.
- [x] Uploads: temporales, progreso, previews, validacion, limpieza y storage
  CI4/S3-compatible.
- [x] Paginacion/query-state: helpers para page/sort/search/perPage, reset de
  pagina al filtrar y sincronizacion opcional con URL.
- [x] Lazy components: placeholder inicial, IntersectionObserver y carga bajo
  demanda.
- [x] Ordenamiento: directiva de sort/reorder con payload estable para listas.

## Prioridad 4 - operacion y DX

- [x] Request bundling: agrupar cambios rapidos, cancelar requests obsoletas,
  ordenar respuestas y evitar doble submit.
- [x] API de interceptores JS: beforeRequest, afterResponse, beforeMorph,
  afterMorph, error y payload transform.
- [x] Devtools/debug: inspeccion de snapshot, acciones, tiempos de render y
  errores de hydration.
- [x] Tests de navegador para morphing, formularios, loading/dirty, polling,
  navegacion y uploads.
- [x] Comando `hot-ui:doctor` para rutas, assets, snapshot key, CSRF, permisos
  y version de bundle.
