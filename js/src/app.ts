import Alpine from 'alpinejs';
import { registerHotUI } from './hot/register.js';
import { installThemeExporter, installToast } from './hot/globals.js';
import { installHotfire } from './hot/hotfire/driver.js';
import { type HotContext } from './hot/plugin.js';
import { ISLANDS } from './components/islands.js';

// ---------------------------------------------------------------------------
// Hot-UI — single entry point.
//
//   js/src/app.ts  →(tsc)→  js/app.js
//
// One tag on the page:
//     <script type="module" src="/js/app.js"></script>
//
// If your app already runs its own Alpine, import registerHotUI + the islands
// and register everything inside your own 'alpine:init' listener.
// ---------------------------------------------------------------------------

installToast();
installThemeExporter();
installHotfire();

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

// No Alpine was mounted by the host → mount ours. start() fires 'alpine:init',
// so the listener above is still what registers everything: one code path.
if (!window.Alpine) {
    window.Alpine = Alpine as unknown as never;
    Alpine.start();
}
