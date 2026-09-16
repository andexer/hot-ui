import type { AlpineLike } from './types.js';
import type { ThemeStore } from './stores/theme.js';
import { createThemeStore } from './stores/theme.js';
import { createModelMagic, type HotModel } from './magic/model.js';
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
 * The `$hot` facade — ONE magic exposing every kernel capability:
 *
 *   $hot.model(default)   two-way binding via data-hot-model (or local)
 *   $hot.nav(event, opts) APG roving focus
 *   $hot.type(event, sel) APG typeahead
 *   $hot.number           stepping arithmetic toolkit
 */
export interface HotFacade {
    model: (el: HTMLElement) => (fallback?: unknown) => HotModel;
    nav: ReturnType<typeof createNavMagic>;
    type: ReturnType<typeof createTypeaheadMagic>;
    number: typeof numberToolkit;
}

export interface RegisterOptions {
    /** 'class' (default) | 'system' | false — see stores/theme.ts. */
    darkMode?: 'class' | 'system' | false;
}

export interface RegisteredKernel {
    theme: ThemeStore;
}

/**
 * Registers every Hot-UI kernel primitive into an Alpine instance. Call before
 * Alpine.start(); the single loader (js/src/app.ts) does exactly that.
 */
export function registerHotUI(alpine: AlpineLike, options: RegisterOptions = {}): RegisteredKernel {
    const theme = createThemeStore(options.darkMode ?? 'class');

    alpine.store('theme', theme);

    // Engines — one Alpine.data per controller; islands and templates consume
    // them as x-data="hotXxx({...})". Calendar lives in its own island
    // (components/ui/calendar.ts) because it is a full component, not an engine.
    alpine.data('hotMenu', createMenu as (config?: unknown) => unknown);
    alpine.data('hotMenubar', () => createMenubar());
    alpine.data('hotSelect', createSelect as (config?: unknown) => unknown);
    alpine.data('hotListbox', createListbox as (config?: unknown) => unknown);
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
