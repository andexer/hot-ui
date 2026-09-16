import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';

/**
 * hotDialog — controller for the dialog family.
 *
 * The root owns `open`; the teleported content panel toggles the focus
 * machinery with `x-effect="trap($el, open)"`. This replaces the old
 * `x-trap.noscroll.inert` (Alpine focus plugin is not part of the kernel
 * build) while keeping every class, event and DOM node identical.
 */

export interface DialogConfig {
    /** Initial state; templates pass `open: <?= js((bool) $open) ?>`. */
    open?: boolean;
}

export interface ModalController {
    open: boolean;
    /**
     * Engage/release the modal focus machinery for one panel. Idempotent per
     * element; the x-effect re-runs on every `open` flip.
     */
    trap(el: HTMLElement, active: boolean): void;
}

const TRAP_OPTIONS = { lockScroll: true, inert: true };

export function hotDialog(config?: DialogConfig): ModalController {
    return {
        open: config?.open ?? false,
        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'dialog',
    register({ alpine }: HotContext): void {
        alpine.data('hotDialog', hotDialog as never);
    },
} satisfies IslandPlugin;
