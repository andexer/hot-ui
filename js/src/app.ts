import Alpine from 'alpinejs';
import { registerHotUI } from './hot/register.js';
import { installThemeExporter, installToast } from './hot/globals.js';
import { type HotContext } from './hot/plugin.js';
import { ISLANDS } from './components/islands.js';

// ---------------------------------------------------------------------------
// Hot-UI — cargador único.
//
//   js/src/app.ts  →(tsc)→  js/app.js
//
// Una sola etiqueta en la página:
//     <script type="module" src="/js/app.js"></script>
//
// Si tu app ya corre su propio Alpine, importa registerHotUI + las islas y
// regístralo todo dentro de tu propio listener 'alpine:init'.
// ---------------------------------------------------------------------------

installToast();
installThemeExporter();

document.addEventListener('alpine:init', () => {
    const engine = (window.Alpine ?? Alpine) as unknown as import('./hot/types.js').AlpineLike;
    const theme = registerHotUI(engine);

    // Expose the store handle for programmatic theming (theme.toggle() etc.).
    (window as unknown as Record<string, unknown>)['__hotTheme'] = theme;

    for (const island of ISLANDS) {
        island.register({
            alpine: engine,
            directive: (name, handler) => engine.directive(name, handler),
            magic: (name, handler) => engine.magic(name, handler),
            data: (name, factory) => engine.data(name, factory as never),
        } satisfies HotContext);
    }
});

// Nadie trajo un Alpine → traemos el nuestro. start() dispara 'alpine:init',
// así que el listener de arriba sigue siendo quien registra todo: un solo
// camino de ejecución, no dos.
if (!window.Alpine) {
    window.Alpine = Alpine as unknown as never;
    Alpine.start();
}
