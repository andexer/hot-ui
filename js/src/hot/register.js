import { createThemeStore } from './stores/theme.js';
import { createModelMagic } from './magic/model.js';
import { createNavMagic, createTypeaheadMagic } from './magic/navigation.js';
import { numberToolkit } from './utils/numbers.js';
import { anchorDirective } from './directives/anchor.js';
import { collapseDirective } from './directives/collapse.js';
import { dialogLayerDirective } from './directives/dialog-layer.js';
import { fieldDirective } from './directives/field.js';
import { labelledByDirective } from './directives/labelledby.js';
import { triggerDirective } from './directives/trigger.js';
import { createCommand } from './engines/command.js';
import { createListbox } from './engines/listbox.js';
import { createMenu } from './engines/menu.js';
import { createMenubar } from './engines/menubar.js';
import { createSelect } from './engines/select.js';
/**
 * Registers every Hot-UI kernel primitive into an Alpine instance. Call before
 * Alpine.start(); the single loader (js/src/app.ts) does exactly that.
 */
export function registerHotUI(alpine, options = {}) {
    const theme = createThemeStore(options.darkMode ?? 'class');
    alpine.store('theme', theme);
    // Engines — one Alpine.data per controller; islands and templates consume
    // them as x-data="hotXxx({...})". Calendar lives in its own island
    // (components/ui/calendar.ts) because it is a full component, not an engine.
    alpine.data('hotMenu', createMenu);
    alpine.data('hotMenubar', () => createMenubar());
    alpine.data('hotSelect', createSelect);
    alpine.data('hotListbox', createListbox);
    alpine.data('hotCommand', () => createCommand());
    // Directives.
    alpine.directive('hot-trigger', triggerDirective);
    alpine.directive('hot-labelledby', labelledByDirective);
    alpine.directive('hot-anchor', anchorDirective);
    alpine.directive('hot-dialog-layer', dialogLayerDirective);
    alpine.directive('hot-field', fieldDirective);
    alpine.directive('hot-collapse', collapseDirective);
    // The single `$hot` magic facade.
    const nav = createNavMagic();
    const type = createTypeaheadMagic();
    const model = createModelMagic(alpine);
    alpine.magic('hot', (el) => ({
        model: model(el),
        nav: nav(el),
        type: type(el),
        number: numberToolkit,
    }));
    return { theme };
}
//# sourceMappingURL=register.js.map