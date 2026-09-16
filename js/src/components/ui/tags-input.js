export function createHotTagsInput(config) {
    return {
        _model: config.model,
        draft: '',
        max: config.max ?? null,
        disabled: config.disabled ?? false,
        get tags() {
            return this._model.value;
        },
        set tags(v) {
            this._model.value = v;
        },
        get atMax() {
            return this.max !== null && this.tags.length >= this.max;
        },
        get inputDisabled() {
            return this.disabled || this.atMax;
        },
        add() {
            const t = this.draft.trim();
            this.draft = '';
            if (!t || this.atMax)
                return;
            if (this.tags.includes(t))
                return;
            this.tags = [...this.tags, t];
        },
        remove(i) {
            if (this.disabled)
                return;
            this.tags = this.tags.filter((_, index) => index !== i);
        },
        backspace() {
            if (this.disabled)
                return;
            if (this.draft === '' && this.tags.length)
                this.tags = this.tags.slice(0, -1);
        },
    };
}
export default {
    name: 'tags-input',
    register({ alpine }) {
        alpine.data('hotTagsInput', createHotTagsInput);
    },
};
//# sourceMappingURL=tags-input.js.map