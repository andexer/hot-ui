export interface MenuConfig { open?: boolean }

interface MenuItem extends HTMLElement {
    focus(options?: FocusOptions): void;
}

/**
 * hotMenu — shared disclosure + roving-focus engine for menu widgets
 * (dropdown-menu, context-menu). Mirrors the WAI-ARIA menu-button pattern:
 * openMenu places initial focus ('first'|'last'|container); closeMenu restores
 * trigger focus. Item-to-item arrows are handled by $hot.nav on the content.
 */
export interface NextTick { $nextTick(callback: () => void): void }
type Live<T> = T & NextTick;

export interface HotMenuController {
    open: boolean;
    x: number;
    y: number;
    _menu: HTMLElement | null;
    _trigger: HTMLElement | null;
    _closeTimer?: ReturnType<typeof setTimeout>;
    readonly _items: MenuItem[];
    openAt(ev: Event & { clientX: number; clientY: number; currentTarget?: EventTarget | null }): void;
    openMenu(focus?: 'first' | 'last'): void;
    toggleMenu(): void;
    closeMenu(returnFocus?: boolean): void;
    closeSoon(delay?: number): void;
    cancelClose(): void;
}

const MENU_ITEM_SELECTOR =
    '[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]';

export function createMenu(config: MenuConfig = {}): HotMenuController {
    return {
        open: config.open ?? false,
        x: 0,
        y: 0,
        _menu: null,
        _trigger: null,

        get _items(): MenuItem[] {
            if (!this._menu) return [];

            return Array.from(this._menu.querySelectorAll<HTMLElement>(MENU_ITEM_SELECTOR)).filter(
                (item) => item.getAttribute('aria-disabled') !== 'true' && !item.hasAttribute('disabled') && item.offsetParent !== null,
            );
        },

        openAt(ev): void {
            ev.preventDefault();
            this.x = ev.clientX;
            this.y = ev.clientY;
            if (ev.currentTarget instanceof HTMLElement) this._trigger = ev.currentTarget;
            this.openMenu('first');
        },

        openMenu(focus): void {
            this.cancelClose();
            this.open = true;
            (this as Live<HotMenuController>).$nextTick(() => {
                if (!this._menu) return;
                const items = this._items;
                if (focus === 'first') (items[0] ?? this._menu).focus();
                else if (focus === 'last') (items[items.length - 1] ?? this._menu).focus();
                else this._menu.focus();
            });
        },

        toggleMenu(): void {
            if (this.open) this.closeMenu(false);
            else this.openMenu();
        },

        closeMenu(returnFocus = true): void {
            this.cancelClose();
            if (!this.open) return;
            this.open = false;
            // Submenus portal to <body>, so a short cancellable close lets the
            // pointer cross the gap from trigger to flyout without it snapping shut.
            if (returnFocus && this._trigger) (this as Live<HotMenuController>).$nextTick(() => this._trigger?.focus());
        },

        closeSoon(delay = 120): void {
            clearTimeout(this._closeTimer);
            this._closeTimer = setTimeout(() => this.closeMenu(false), delay);
        },

        cancelClose(): void {
            clearTimeout(this._closeTimer);
        },
    };
}
