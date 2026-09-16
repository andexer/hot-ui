import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: true, inert: true };
export function hotSheet(config) {
    return {
        open: config?.open ?? false,
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'sheet',
    register({ alpine }) {
        alpine.data('hotSheet', hotSheet);
    },
};
//# sourceMappingURL=sheet.js.map