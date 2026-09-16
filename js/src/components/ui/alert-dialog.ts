import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';
import type { ModalController } from './dialog.js';

/**
 * hotAlertDialog — controller for the confirm/alert-dialog family.
 *
 * Same modal shell as hotDialog (role="alertdialog" is decided by the
 * template): the root owns `open`, the panel toggles the trap via
 * `x-effect="trap($el, open)"`. Overlay clicks are intentionally NOT wired —
 * an alert forces a decision; Escape and the action buttons close it.
 */

export interface AlertDialogConfig {
    /** Initial state; templates pass `open: <?= js((bool) $open) ?>`. */
    open?: boolean;
}

const TRAP_OPTIONS = { lockScroll: true, inert: true };

export function hotAlertDialog(config?: AlertDialogConfig): ModalController {
    return {
        open: config?.open ?? false,
        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'alert-dialog',
    register({ alpine }: HotContext): void {
        alpine.data('hotAlertDialog', hotAlertDialog as never);
    },
} satisfies IslandPlugin;
