/**
 * hotCommand — command-palette engine implementing the editable-combobox +
 * listbox APG pattern with aria-activedescendant. Focus stays on the input;
 * the "active" option is tracked by id, moved with Arrow/Home/End and
 * triggered with Enter. Items register themselves and filter on the query.
 *
 * IMPORTANT: every method goes through `this`. Alpine wraps the factory
 * result in a reactive proxy — writing through any other reference would
 * bypass reactivity and the UI would freeze.
 */

export interface CommandEntry {
    id: string;
    el: HTMLElement;
    keyword: string;
    disabled: boolean;
}

let commandCounter = 0;

function mintCommandId(): string {
    commandCounter += 1;

    return `hot-cmd-item-${String(commandCounter)}-${Math.random().toString(36).slice(2, 7)}`;
}

export interface HotCommandController {
    query: string;
    activeId: string | null;
    _entries: CommandEntry[];
    registerItem(el: HTMLElement, keyword?: string, disabled?: boolean): string;
    matches(keyword?: string): boolean;
    readonly _visible: CommandEntry[];
    readonly visibleCount: number;
    ensureActive(): void;
    move(dir: number): void;
    edge(pos: 'first' | 'last'): void;
    selectActive(): void;
}

export function createCommand(): HotCommandController {
    return {
        query: '',
        activeId: null,
        _entries: [],

        registerItem(el, keyword = '', disabled = false): string {
            el.id ||= mintCommandId();
            this._entries.push({ id: el.id, el, keyword: keyword.toLowerCase(), disabled });

            return el.id;
        },

        matches(keyword?: string): boolean {
            return (keyword ?? '').toLowerCase().includes(this.query.toLowerCase());
        },

        get _visible(): CommandEntry[] {
            return this._entries.filter(
                (entry) => !entry.disabled && this.matches(entry.keyword) && entry.el.offsetParent !== null,
            );
        },

        get visibleCount(): number {
            return this._entries.filter((entry) => this.matches(entry.keyword)).length;
        },

        ensureActive(): void {
            const visible = this._visible;
            if (visible.length === 0) {
                this.activeId = null;
            } else if (!visible.some((entry) => entry.id === this.activeId)) {
                this.activeId = visible[0]?.id ?? null;
            }
        },

        move(dir: number): void {
            const visible = this._visible;
            if (visible.length === 0) return;
            const index = visible.findIndex((entry) => entry.id === this.activeId);
            const next = index < 0
                ? (dir > 0 ? 0 : visible.length - 1)
                : (index + dir + visible.length) % visible.length;
            const target = visible[next];
            if (!target) return;
            this.activeId = target.id;
            target.el.scrollIntoView({ block: 'nearest' });
        },

        edge(pos): void {
            const visible = this._visible;
            if (visible.length === 0) return;
            const target = pos === 'last' ? visible[visible.length - 1] : visible[0];
            if (!target) return;
            this.activeId = target.id;
            target.el.scrollIntoView({ block: 'nearest' });
        },

        selectActive(): void {
            const entry = this._entries.find((candidate) => candidate.id === this.activeId);
            if (entry && !entry.disabled) entry.el.click();
        },
    };
}
