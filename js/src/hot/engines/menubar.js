export function createMenubar() {
    return {
        active: null,
        rovingId: null,
        triggers: [],
        register(id, el) {
            this.triggers.push({ id, el });
            if (this.rovingId === null)
                this.rovingId = id;
        },
        focusTrigger(id) {
            const entry = this.triggers.find((candidate) => candidate.id === id);
            if (entry) {
                this.rovingId = id;
                entry.el.focus();
            }
        },
        openMenu(id, focusFirst = true) {
            this.active = id;
            this.rovingId = id;
            if (!focusFirst)
                return;
            this.$nextTick(() => {
                const items = menubarItems(document, id);
                (items[0] ?? document.getElementById(id))?.focus();
            });
        },
        toggleMenu(id) {
            if (this.active === id)
                this.closeMenu(false);
            else
                this.openMenu(id);
        },
        moveTrigger(dir, fromId) {
            if (this.triggers.length === 0)
                return;
            const index = this.triggers.findIndex((entry) => entry.id === fromId);
            if (index < 0)
                return;
            const next = this.triggers[(index + dir + this.triggers.length) % this.triggers.length].id;
            if (this.active !== null)
                this.openMenu(next);
            else
                this.focusTrigger(next);
        },
        closeMenu(returnFocus = true) {
            const id = this.active;
            this.active = null;
            if (returnFocus && id)
                this.focusTrigger(id);
        },
    };
}
function menubarItems(doc, menuId) {
    const menu = doc.getElementById(menuId);
    if (!menu)
        return [];
    return Array.from(menu.querySelectorAll('[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]')).filter((item) => item.getAttribute('aria-disabled') !== 'true' && item.offsetParent !== null);
}
//# sourceMappingURL=menubar.js.map