import type { HotModel } from '../magic/model.js';

export interface SelectConfig {
    multiple?: boolean;
    /** A $hot.model instance from the component's x-data. */
    model?: HotModel;
    /** Marks the model as externally owned: values pass through verbatim. */
    wired?: boolean;
}

/**
 * hotSelect — WAI-ARIA listbox engine for <x-ui.select>. Opening focuses the
 * selected option (or first); arrow/typeahead navigation rides $hot.nav and
 * $hot.type on the listbox; Enter/Space selects and closes, restoring focus.
 *
 * The value lives in the caller's model (a $hot.model bound to data-hot-model,
 * or holding it locally). Externally owned values pass through verbatim —
 * coercing them would fight their type; local ones are normalised to strings.
 *
 * IMPORTANT: every method MUST go through `this`. Alpine wraps the factory
 * result in a reactive proxy and binds methods to it — writing through any
 * other reference would bypass reactivity and the UI would freeze.
 * `$nextTick` and friends are injected by Alpine at runtime.
 */
export interface HotSelectController {
    multiple: boolean;
    open: boolean;
    _model: HotModel | { value: unknown };
    value: unknown;
    _labels: Record<string, string>;
    readonly label: string;
    readonly selected: Array<{ value: string; label: string }>;
    _list: HTMLElement | null;
    _trigger: HTMLElement | null;
    readonly _options: HTMLElement[];
    isSelected(candidate: unknown): boolean;
    openList(): void;
    toggleList(): void;
    close(returnFocus?: boolean): void;
    selectOption(val: unknown, label: string): void;
    seedSelected(val: unknown, label: string): void;
    remove(val: unknown): void;
}

/** Alpine-injected magics available on the live proxy (`this`). */
export interface NextTick { $nextTick(callback: () => void): void }

type Live<T> = T & NextTick;

function normalise(model: HotModel | { value: unknown }, multiple: boolean): void {
    const current = model.value as unknown;
    if (multiple) {
        if (Array.isArray(current)) model.value = current.map(String);
        else if (current != null && current !== '') model.value = [String(current)];
        else model.value = [];
    } else {
        model.value = current == null ? '' : String(current);
    }
}

export function createSelect(config: SelectConfig = {}): HotSelectController {
    return {
        multiple: config.multiple ?? false,
        open: false,
        // Externally owned values keep their own type; local ones normalise.
        _model: (() => {
            const fallback = { value: config.multiple ? [] as string[] : '' };
            const model = config.model ?? fallback;
            if (config.wired !== true && config.model) normalise(model, config.multiple ?? false);
            return model;
        })(),

        _labels: {},
        _list: null,
        _trigger: null,

        get value(): unknown {
            return this._model.value;
        },

        set value(next: unknown) {
            this._model.value = next;
        },

        get label(): string {
            if (this.multiple) return '';
            const v = this.value;

            return v === null || v === undefined || v === '' ? '' : this._labels[String(v)] ?? '';
        },

        get selected(): Array<{ value: string; label: string }> {
            const values = this.multiple && Array.isArray(this.value) ? (this.value as unknown[]) : [];

            return values.map((v) => ({ value: String(v), label: this._labels[String(v)] ?? String(v) }));
        },

        get _options(): HTMLElement[] {
            return this._list
                ? Array.from(this._list.querySelectorAll<HTMLElement>('[role="option"]')).filter(
                      (option) => option.getAttribute('aria-disabled') !== 'true' && option.offsetParent !== null,
                  )
                : [];
        },

        isSelected(candidate: unknown): boolean {
            const val = String(candidate);

            return this.multiple
                ? (Array.isArray(this.value) ? (this.value as unknown[]) : []).map(String).includes(val)
                : String(this.value ?? '') === val;
        },

        openList(): void {
            this.open = true;
            (this as Live<HotSelectController>).$nextTick(() => {
                if (!this._list) return;
                const options = this._options;
                const selected = options.find(
                    (option) => this.isSelected((option as HTMLElement & { dataset: { value?: string } }).dataset.value),
                );
                (selected ?? options[0] ?? this._list).focus();
            });
        },

        toggleList(): void {
            if (this.open) this.close(false);
            else this.openList();
        },

        close(returnFocus = true): void {
            if (!this.open) return;
            this.open = false;
            if (returnFocus && this._trigger) (this as Live<HotSelectController>).$nextTick(() => this._trigger?.focus());
        },

        selectOption(val: unknown, label: string): void {
            const key = String(val);
            this._labels[key] = label;
            if (this.multiple) {
                // Assign rather than mutate: `value` may BE the bound property,
                // and a mutation nobody assigned is a change nothing reacts to.
                const current = (Array.isArray(this.value) ? (this.value as unknown[]) : []).map(String);
                this.value = current.includes(key) ? current.filter((v) => v !== key) : [...current, key];

                return; // stay open for further picks
            }
            this.value = key;
            this.close();
        },

        seedSelected(val: unknown, label: string): void {
            this._labels[String(val)] = label;
        },

        remove(val: unknown): void {
            const key = String(val);
            this.value = (Array.isArray(this.value) ? (this.value as unknown[]) : []).map(String).filter((v) => v !== key);
        },
    };
}
