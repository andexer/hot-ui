import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: true, inert: true };
export function hotDrawer(config) {
    return {
        open: config?.open ?? false,
        direction: config?.direction ?? 'bottom',
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'drawer',
    register({ alpine }) {
        alpine.data('hotDrawer', hotDrawer);
    },
};
//# sourceMappingURL=drawer.js.map