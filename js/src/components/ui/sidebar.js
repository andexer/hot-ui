import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: true, inert: true };
export function hotSidebar(config) {
    const query = config?.mobileQuery ?? '(max-width: 767px)';
    return {
        open: config?.defaultOpen ?? true,
        openMobile: false,
        isMobile: false,
        collapsed: false,
        _mq: null,
        _onChange: null,
        toggle() {
            if (this.isMobile)
                this.openMobile = !this.openMobile;
            else
                this.open = !this.open;
        },
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
        init() {
            // The MediaQueryList lives on `this` as a plain handle (set only now,
            // in init, so Alpine's proxy never has to wrap it reactively).
            this._mq = window.matchMedia(query);
            this.isMobile = this._mq.matches;
            this._onChange = (e) => {
                this.isMobile = e.matches;
            };
            this._mq.addEventListener('change', this._onChange);
        },
        destroy() {
            if (this._mq && this._onChange)
                this._mq.removeEventListener('change', this._onChange);
            this._mq = null;
            this._onChange = null;
        },
    };
}
export default {
    name: 'sidebar',
    register({ alpine }) {
        alpine.data('hotSidebar', hotSidebar);
    },
};
//# sourceMappingURL=sidebar.js.map