import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: true, inert: true };
export function hotAlertDialog(config) {
    return {
        open: config?.open ?? false,
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'alert-dialog',
    register({ alpine }) {
        alpine.data('hotAlertDialog', hotAlertDialog);
    },
};
//# sourceMappingURL=alert-dialog.js.map