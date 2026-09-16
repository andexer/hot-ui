import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: false, inert: false };
export function hotPopover(config) {
    return {
        open: config?.open ?? false,
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'popover',
    register({ alpine }) {
        alpine.data('hotPopover', hotPopover);
    },
};
//# sourceMappingURL=popover.js.map