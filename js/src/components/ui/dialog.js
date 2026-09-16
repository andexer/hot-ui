import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: true, inert: true };
export function hotDialog(config) {
    return {
        open: config?.open ?? false,
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'dialog',
    register({ alpine }) {
        alpine.data('hotDialog', hotDialog);
    },
};
//# sourceMappingURL=dialog.js.map