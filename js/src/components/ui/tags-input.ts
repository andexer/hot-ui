import type { HotModel } from '../../hot/magic/model.js';
import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotTagsInput — controller for the tags-input.
 *
 * All state and logic extracted verbatim from the template's inline x-data:
 * a draft text box feeding an add/remove/backspace lifecycle over ONE bound
 * array. Each edit replaces the array rather than mutating it in place: `tags`
 * IS the bound state itself, and every write goes through the setter so
 * external watchers hear about it.
 */

export interface TagsInputConfig {
    /** A $hot.model instance handed over by the template. */
    model: HotModel;
    max?: number | null;
    disabled?: boolean;
}

export interface HotTagsInputController {
    _model: HotModel;
    draft: string;
    max: number | null;
    disabled: boolean;

    get tags(): string[];
    set tags(v: string[]);
    get atMax(): boolean;
    get inputDisabled(): boolean;
    add(): void;
    remove(i: number): void;
    backspace(): void;
}

export function createHotTagsInput(config: TagsInputConfig): HotTagsInputController {
    return {
        _model: config.model,
        draft: '',
        max: config.max ?? null,
        disabled: config.disabled ?? false,

        get tags(): string[] {
            return this._model.value as string[];
        },
        set tags(v: string[]) {
            this._model.value = v;
        },
        get atMax(): boolean {
            return this.max !== null && this.tags.length >= this.max;
        },
        get inputDisabled(): boolean {
            return this.disabled || this.atMax;
        },

        add(): void {
            const t = this.draft.trim();
            this.draft = '';
            if (!t || this.atMax) return;
            if (this.tags.includes(t)) return;
            this.tags = [...this.tags, t];
        },
        remove(i: number): void {
            if (this.disabled) return;
            this.tags = this.tags.filter((_, index) => index !== i);
        },
        backspace(): void {
            if (this.disabled) return;
            if (this.draft === '' && this.tags.length) this.tags = this.tags.slice(0, -1);
        },
    };
}

export default {
    name: 'tags-input',
    register({ alpine }: HotContext): void {
        alpine.data('hotTagsInput', createHotTagsInput as never);
    },
} satisfies IslandPlugin;
