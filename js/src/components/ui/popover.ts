import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';
import type { ModalController } from './dialog.js';

/**
 * hotPopover — controller for the popover family.
 *
 * Non-modal dialog: the old `x-trap="open"` (no `.noscroll`, no `.inert`)
 * maps to a bare Tab loop + initial focus + restore — page scroll and the
 * rest of the document stay live. Escape and click.outside remain in the
 * template exactly as before.
 */

export interface PopoverConfig {
    /** Initial state. */
    open?: boolean;
}

const TRAP_OPTIONS = { lockScroll: false, inert: false };

export function hotPopover(config?: PopoverConfig): ModalController {
    return {
        open: config?.open ?? false,
        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'popover',
    register({ alpine }: HotContext): void {
        alpine.data('hotPopover', hotPopover as never);
    },
} satisfies IslandPlugin;
