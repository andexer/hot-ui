/**
 * hotListbox — shared filtered-listbox engine behind <x-ui.combobox> (and its
 * inline-input variant, formerly the autocomplete). Both render the SAME
 * option list + selection model; they differ only in how the list opens:
 *
 *   trigger 'button' — a button opens a popover with the search input inside
 *                      it (classic Combobox). `query` resets on open; focus
 *                      returns to the trigger on close.
 *   trigger 'input'  — the field itself IS the text input ("autocomplete"
 *                      shape). `query` mirrors the selected label; typing
 *                      filters; picking fills the input and closes.
 *
 * IMPORTANT: methods go through `this` — Alpine's reactive proxy is what makes
 * the UI update; `$nextTick`/`$refs` are injected at runtime.
 */

import type { HotModel } from '../magic/model.js';

export interface ListboxOption { value: string; label: string }

export interface ListboxConfig {
    trigger?: 'button' | 'input';
    multiple?: boolean;
    model?: HotModel;
    value?: unknown;
    options?: ListboxOption[];
    query?: string;
}

export interface HotListboxController {
    trigger: 'button' | 'input';
    open: boolean;
    filtering: boolean;
    multiple: boolean;
    _model: HotModel | { value: unknown };
    value: string[];
    activeValue: string | null;
    options: ListboxOption[];
    query: string;
    isSelected(candidate: string): boolean;
    readonly selected: ListboxOption[];
    readonly label: string;
    matches(label: string): boolean;
    readonly visible: ListboxOption[];
    readonly visibleCount: number;
    ensureActive(): void;
    move(dir: number): void;
    edge(pos: 'first' | 'last'): void;
    openList(): void;
    onInput(): void;
    toggle(): void;
    close(returnFocus?: boolean): void;
    selectActive(): void;
    select(candidate: string): void;
    remove(candidate: string): void;
    backspace(): void;
}

interface RuntimeMagics {
    $nextTick(callback: () => void): void;
    $refs: Record<string, HTMLElement | undefined>;
}

type Live<T> = T & RuntimeMagics;

function asArray(value: unknown, multiple: boolean): string[] {
    if (multiple) return Array.isArray(value) ? value.map(String) : [];
    const single = value == null ? '' : String(value);

    return single === '' ? [] : [single];
}

export function createListbox(config: ListboxConfig = {}): HotListboxController {
    return {
        trigger: config.trigger ?? 'button',
        open: false,
        filtering: false,
        multiple: config.multiple ?? false,
        _model: config.model ?? { value: config.value ?? (config.multiple ? [] : '') },
        activeValue: null,
        options: config.options ?? [],
        query: config.query ?? '',

        get value(): string[] {
            return asArray(this._model.value as unknown, this.multiple);
        },

        set value(next: string[]) {
            this._model.value = this.multiple ? next : next[0] ?? '';
        },

        isSelected(candidate): boolean {
            if (this.multiple) return this.value.includes(candidate);

            return this.value[0] === candidate;
        },

        get selected(): ListboxOption[] {
            return this.options.filter((option) => this.isSelected(option.value));
        },

        get label(): string {
            const current = this.value[0] ?? '';
            const match = this.options.find((option) => option.value === current);

            return match ? match.label : '';
        },

        matches(label): boolean {
            return label.toLowerCase().includes(this.query.toLowerCase());
        },

        get visible(): ListboxOption[] {
            // Input trigger only filters once the user actually types.
            if (this.trigger === 'input' && !this.filtering) return this.options;

            return this.options.filter((option) => this.matches(option.label));
        },

        get visibleCount(): number {
            return this.visible.length;
        },

        ensureActive(): void {
            const visible = this.visible;
            if (visible.length === 0) {
                this.activeValue = null;

                return;
            }
            if (!visible.some((option) => option.value === this.activeValue)) {
                this.activeValue = (visible.find((option) => this.isSelected(option.value)) ?? visible[0])!.value;
            }
        },

        move(dir): void {
            if (this.trigger === 'input' && !this.open) {
                this.openList();

                return;
            }
            const visible = this.visible;
            if (visible.length === 0) return;
            const index = visible.findIndex((option) => option.value === this.activeValue);
            const next = index < 0 ? 0 : (index + dir + visible.length) % visible.length;
            this.activeValue = visible[next]!.value;
        },

        edge(pos): void {
            const visible = this.visible;
            if (visible.length === 0) return;
            this.activeValue = (pos === 'last' ? visible[visible.length - 1] : visible[0])!.value;
        },

        openList(): void {
            this.open = true;
            (this as Live<HotListboxController>).$nextTick(() => {
                if (this.trigger === 'button') {
                    this.query = '';
                    this.ensureActive();
                    ((this as Live<HotListboxController>).$refs['search'] ?? (this as Live<HotListboxController>).$refs['list'])?.focus();
                } else {
                    this.filtering = false;
                    this.ensureActive();
                }
            });
        },

        onInput(): void {
            if (!this.multiple) this.value = [];
            this.open = true;
            this.filtering = true;
            (this as Live<HotListboxController>).$nextTick(() => this.ensureActive());
        },

        toggle(): void {
            if (this.open) this.close(false);
            else this.openList();
        },

        close(returnFocus = true): void {
            if (!this.open) return;
            this.open = false;
            this.filtering = false;
            if (this.trigger === 'button' && returnFocus) {
                (this as Live<HotListboxController>).$nextTick(() => (this as Live<HotListboxController>).$refs['trigger']?.focus());
            }
        },

        selectActive(): void {
            if (this.activeValue != null) this.select(this.activeValue);
        },

        select(candidate): void {
            if (this.multiple) {
                // Assign rather than mutate — same rule as every bound value.
                const current = [...this.value];
                this.value = current.includes(candidate) ? current.filter((v) => v !== candidate) : [...current, candidate];
                if (this.trigger === 'input') {
                    this.query = '';
                    this.filtering = false;
                    (this as Live<HotListboxController>).$nextTick(() => (this as Live<HotListboxController>).$refs['input']?.focus());
                }

                return; // stay open for further picks
            }
            if (this.trigger === 'input') {
                const option = this.options.find((candidate_) => candidate_.value === candidate);
                if (option) {
                    this._model.value = option.value;
                    this.query = option.label;
                }
                this.close();

                return;
            }
            // Toggle off in single button mode (matches shadcn).
            this._model.value = this.value[0] === candidate ? '' : candidate;
            this.close();
            this.query = '';
        },

        remove(candidate): void {
            this.value = this.value.filter((v) => v !== candidate);
        },

        backspace(): void {
            if (this.multiple && this.query === '' && this.value.length > 0) this.value = this.value.slice(0, -1);
        },
    };
}
