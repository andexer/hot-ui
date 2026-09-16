import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';
import type { ModalController } from './dialog.js';

/**
 * hotCommandPalette — modal shell for the command-palette dialog.
 *
 * The base command engine already lives in the kernel as `hotCommand`
 * (query/activeId/roving focus) and plain uiCommand() consumes it directly —
 * no island needed there. This controller adds ONLY the palette's extra
 * state: the `open` flag of uiCommandDialog plus its focus trap
 * (`x-effect="trap($el, open)"` replacing the dead `x-trap.noscroll.inert`).
 * Templates compose both: root `x-data="hotCommandPalette()"`, inner
 * `x-data="hotCommand()"`.
 */

export interface CommandPaletteConfig {
    /** Initial state. */
    open?: boolean;
}

const TRAP_OPTIONS = { lockScroll: true, inert: true };

export function hotCommandPalette(config?: CommandPaletteConfig): ModalController {
    return {
        open: config?.open ?? false,
        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'command',
    register({ alpine }: HotContext): void {
        alpine.data('hotCommandPalette', hotCommandPalette as never);
    },
} satisfies IslandPlugin;
