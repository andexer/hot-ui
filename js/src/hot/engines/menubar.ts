type MenuItem = HTMLElement;

interface TriggerEntry { id: string; el: HTMLElement }

export interface NextTick { $nextTick(callback: () => void): void }
type Live<T> = T & NextTick;

/**
 * hotMenubar — WAI-ARIA menubar engine. Triggers share one `active` (the open
 * menu's id) and a roving tabindex; ArrowLeft/Right move between triggers
 * (switching the open menu when one is open); Down/Enter open with first item
 * focused. Each menu's content keys off `active === id`.
 */
export interface HotMenubarController {
    active: string | null;
    rovingId: string | null;
    triggers: TriggerEntry[];
    register(id: string, el: HTMLElement): void;
    focusTrigger(id: string): void;
    openMenu(id: string, focusFirst?: boolean): void;
    toggleMenu(id: string): void;
    moveTrigger(dir: number, fromId: string): void;
    closeMenu(returnFocus?: boolean): void;
}

export function createMenubar(): HotMenubarController {
    return {
        active: null,
        rovingId: null,
        triggers: [],

        register(id, el): void {
            this.triggers.push({ id, el });
            if (this.rovingId === null) this.rovingId = id;
        },

        focusTrigger(id): void {
            const entry = this.triggers.find((candidate) => candidate.id === id);
            if (entry) {
                this.rovingId = id;
                entry.el.focus();
            }
        },

        openMenu(id, focusFirst = true): void {
            this.active = id;
            this.rovingId = id;
            if (!focusFirst) return;
            (this as Live<HotMenubarController>).$nextTick(() => {
                const items = menubarItems(document, id);
                (items[0] ?? document.getElementById(id))?.focus();
            });
        },

        toggleMenu(id): void {
            if (this.active === id) this.closeMenu(false);
            else this.openMenu(id);
        },

        moveTrigger(dir, fromId): void {
            if (this.triggers.length === 0) return;
            const index = this.triggers.findIndex((entry) => entry.id === fromId);
            if (index < 0) return;
            const next = this.triggers[(index + dir + this.triggers.length) % this.triggers.length]!.id;
            if (this.active !== null) this.openMenu(next);
            else this.focusTrigger(next);
        },

        closeMenu(returnFocus = true): void {
            const id = this.active;
            this.active = null;
            if (returnFocus && id) this.focusTrigger(id);
        },
    };
}

function menubarItems(doc: Document, menuId: string): MenuItem[] {
    const menu = doc.getElementById(menuId);
    if (!menu) return [];

    return Array.from(menu.querySelectorAll<MenuItem>('[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]')).filter(
        (item) => item.getAttribute('aria-disabled') !== 'true' && item.offsetParent !== null,
    );
}
