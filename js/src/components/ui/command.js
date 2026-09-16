import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: true, inert: true };
export function hotCommandPalette(config) {
    return {
        open: config?.open ?? false,
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'command',
    register({ alpine }) {
        alpine.data('hotCommandPalette', hotCommandPalette);
    },
};
//# sourceMappingURL=command.js.map