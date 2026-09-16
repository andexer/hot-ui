import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';
import type { ModalController } from './dialog.js';

/**
 * hotDrawer — controller for the drawer family.
 *
 * Owns `open` plus the `direction` the content panel reads for its
 * position classes (`:data-vaul-drawer-direction="direction"`). The panel
 * toggles the focus machinery with `x-effect="trap($el, open)"`, replacing
 * the dead `x-trap.noscroll.inert` while keeping markup identical.
 */

export type DrawerDirection = 'bottom' | 'top' | 'left' | 'right';

export interface DrawerConfig {
    /** Initial state. Drawers start closed unless told otherwise. */
    open?: boolean;
    /** Which edge the drawer attaches to (mirrors data-vaul-drawer-direction). */
    direction?: DrawerDirection;
}

export interface DrawerController extends ModalController {
    direction: DrawerDirection;
}

const TRAP_OPTIONS = { lockScroll: true, inert: true };

export function hotDrawer(config?: DrawerConfig): DrawerController {
    return {
        open: config?.open ?? false,
        direction: config?.direction ?? 'bottom',
        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'drawer',
    register({ alpine }: HotContext): void {
        alpine.data('hotDrawer', hotDrawer as never);
    },
} satisfies IslandPlugin;
