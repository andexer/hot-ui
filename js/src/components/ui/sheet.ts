import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';
import type { ModalController } from './dialog.js';

/**
 * hotSheet — controller for the sheet family (edge-attached modal panels).
 *
 * The root owns `open`; the teleported content panel toggles the focus
 * machinery with `x-effect="trap($el, open)"`, replacing the dead
 * `x-trap.noscroll.inert` while keeping markup and events identical.
 */

export interface SheetConfig {
    /** Initial state; templates pass `open: <?= js((bool) $open) ?>`. */
    open?: boolean;
}

const TRAP_OPTIONS = { lockScroll: true, inert: true };

export function hotSheet(config?: SheetConfig): ModalController {
    return {
        open: config?.open ?? false,
        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'sheet',
    register({ alpine }: HotContext): void {
        alpine.data('hotSheet', hotSheet as never);
    },
} satisfies IslandPlugin;
