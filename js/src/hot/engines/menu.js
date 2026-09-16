const MENU_ITEM_SELECTOR = '[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]';
export function createMenu(config = {}) {
    return {
        open: config.open ?? false,
        x: 0,
        y: 0,
        _menu: null,
        _trigger: null,
        get _items() {
            if (!this._menu)
                return [];
            return Array.from(this._menu.querySelectorAll(MENU_ITEM_SELECTOR)).filter((item) => item.getAttribute('aria-disabled') !== 'true' && !item.hasAttribute('disabled') && item.offsetParent !== null);
        },
        openAt(ev) {
            ev.preventDefault();
            this.x = ev.clientX;
            this.y = ev.clientY;
            if (ev.currentTarget instanceof HTMLElement)
                this._trigger = ev.currentTarget;
            this.openMenu('first');
        },
        openMenu(focus) {
            this.cancelClose();
            this.open = true;
            this.$nextTick(() => {
                if (!this._menu)
                    return;
                const items = this._items;
                if (focus === 'first')
                    (items[0] ?? this._menu).focus();
                else if (focus === 'last')
                    (items[items.length - 1] ?? this._menu).focus();
                else
                    this._menu.focus();
            });
        },
        toggleMenu() {
            if (this.open)
                this.closeMenu(false);
            else
                this.openMenu();
        },
        closeMenu(returnFocus = true) {
            this.cancelClose();
            if (!this.open)
                return;
            this.open = false;
            // Submenus portal to <body>, so a short cancellable close lets the
            // pointer cross the gap from trigger to flyout without it snapping shut.
            if (returnFocus && this._trigger)
                this.$nextTick(() => this._trigger?.focus());
        },
        closeSoon(delay = 120) {
            clearTimeout(this._closeTimer);
            this._closeTimer = setTimeout(() => this.closeMenu(false), delay);
        },
        cancelClose() {
            clearTimeout(this._closeTimer);
        },
    };
}
//# sourceMappingURL=menu.js.map