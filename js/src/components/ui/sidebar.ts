import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';

/**
 * hotSidebar — controller for the sidebar family (sidebar-provider root).
 *
 * Owns the docked `open` state, the mobile drawer's `openMobile`, the
 * `isMobile` flag tracked against a configurable breakpoint media query
 * (kept live via matchMedia), and the derived `collapsed` rail flag the
 * template syncs with its own x-effect (deliberately a plain property there,
 * never a getter — see sidebar-provider.php).
 *
 * The teleported mobile panel toggles the modal focus machinery with
 * `x-effect="trap($el, openMobile)"`, replacing the dead `x-trap.noscroll.inert`
 * while keeping markup and events identical.
 */

export interface SidebarConfig {
    /** Initial docked state; templates pass `defaultOpen: <?= js((bool) $defaultOpen) ?>`. */
    defaultOpen?: boolean;
    /** Full media query string built server-side from mobileBreakpoint. */
    mobileQuery?: string;
}

export interface SidebarController {
    open: boolean;
    openMobile: boolean;
    isMobile: boolean;
    collapsed: boolean;
    /** Live breakpoint query — set in init(), read by destroy(). */
    _mq: MediaQueryList | null;
    /** Change listener installed in init(), removed in destroy(). */
    _onChange: ((e: MediaQueryListEvent) => void) | null;

    toggle(): void;
    /** Modal focus machinery for the mobile drawer panel. */
    trap(el: HTMLElement, active: boolean): void;
    init(): void;
    destroy(): void;
}

const TRAP_OPTIONS = { lockScroll: true, inert: true };

export function hotSidebar(config?: SidebarConfig): SidebarController {
    const query = config?.mobileQuery ?? '(max-width: 767px)';

    return {
        open: config?.defaultOpen ?? true,
        openMobile: false,
        isMobile: false,
        collapsed: false,
        _mq: null,
        _onChange: null,

        toggle(): void {
            if (this.isMobile) this.openMobile = !this.openMobile;
            else this.open = !this.open;
        },

        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },

        init() {
            // The MediaQueryList lives on `this` as a plain handle (set only now,
            // in init, so Alpine's proxy never has to wrap it reactively).
            this._mq = window.matchMedia(query);
            this.isMobile = this._mq.matches;
            this._onChange = (e: MediaQueryListEvent): void => {
                this.isMobile = e.matches;
            };
            this._mq.addEventListener('change', this._onChange);
        },

        destroy() {
            if (this._mq && this._onChange) this._mq.removeEventListener('change', this._onChange);
            this._mq = null;
            this._onChange = null;
        },
    };
}

export default {
    name: 'sidebar',
    register({ alpine }: HotContext): void {
        alpine.data('hotSidebar', hotSidebar as never);
    },
} satisfies IslandPlugin;
