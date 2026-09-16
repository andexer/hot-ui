# Convenciones de Hot-UI

Sustituye al antiguo CONVENTIONS.md. Estas reglas rigen todo el código nuevo.

## PHP (plantillas)

1. Toda plantilla de componente empieza con el preámbulo estándar:
   ```php
   <?php
   declare(strict_types=1);
   extract(props($__ctx, ['variant' => 'default']));
   ```
   Para herencia padre→hijo: `extract(aware($__ctx, [...]))` + `share([...])` en el padre.
2. Lógica arriba en un bloque `<?php ?>`; marcado abajo con sintaxis alternativa
   (`if:/endif;`, `foreach:/endforeach;`).
3. Escapado: texto dinámico SIEMPRE `e()`; URLs SIEMPRE `safe_url()`;
   los slots se imprimen crudos (`<?= $slot ?>`) porque ya son HTML.
4. `{{ $attributes->twMerge('...') }}` → `<?= $attributes->twMerge('...') ?>`
   (emite todos los atributos + class resuelta).
5. Nombres: archivo kebab-case (`alert-dialog-title.php`) ↔ llamada `$this->uiAlertDialogTitle(...)`.
6. Iconos Lucide como `<i data-lucide="nombre" ...></i>` (resuelve `lucide.createIcons()`).
7. PROHIBIDO: Laravel/Illuminate/Livewire/Carbon/collections — PHP vainilla.

## TypeScript / Alpine

1. Fuentes en `js/src/**`, compilación en sitio con `tsc` (ESM nativo, sin bundler).
2. Tipado estricto máximo ya forzado por tsconfig (strict,
   noUncheckedIndexedAccess, exactOptionalPropertyTypes…). Sin `any`.
3. Una isla = `components/ui/<componente>.ts` espejo del `.php`, export default
   `IslandPlugin`. Se añade SOLO si hay lógica sustanciosa (>~15 líneas).
4. Registro central: una línea de import + entrada en
   `js/src/components/islands.ts`.
5. Reactividad: métodos sobre `this`; jamás cierres sobre el objeto crudo.
6. Nombres de registro: `hot<Nombre>` en PascalCase (`hotDataTable`).
7. Binding externo: `data-hot-model="ruta.propiedad"` + `$hot.model(default)`;
   sin modificadores ni puentes a frameworks.
8. Directivas propias del kernel: `x-hot-trigger`, `x-hot-labelledby`,
   `x-hot-anchor`, `x-hot-dialog-layer`, `x-hot-field`, `x-hot-collapse`.
9. Magics: `$hot.model()`, `$hot.nav(event, opts?)`, `$hot.type(event, sel?)`,
   `$hot.number.step/snap/round/decimals`.

## Comandos

```bash
composer test        # PHPUnit
composer smoke       # render 384 componentes
npm run dev          # tsc --watch
npm run build        # tsc (emite .js junto a cada .ts)
npm run typecheck    # tsc --noEmit
php -S 127.0.0.1:8080 demo/router.php   # demo visual
```
