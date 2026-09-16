function normalise(model, multiple) {
    const current = model.value;
    if (multiple) {
        if (Array.isArray(current))
            model.value = current.map(String);
        else if (current != null && current !== '')
            model.value = [String(current)];
        else
            model.value = [];
    }
    else {
        model.value = current == null ? '' : String(current);
    }
}
export function createSelect(config = {}) {
    return {
        multiple: config.multiple ?? false,
        open: false,
        // Externally owned values keep their own type; local ones normalise.
        _model: (() => {
            const fallback = { value: config.multiple ? [] : '' };
            const model = config.model ?? fallback;
            if (config.wired !== true && config.model)
                normalise(model, config.multiple ?? false);
            return model;
        })(),
        _labels: {},
        _list: null,
        _trigger: null,
        get value() {
            return this._model.value;
        },
        set value(next) {
            this._model.value = next;
        },
        get label() {
            if (this.multiple)
                return '';
            const v = this.value;
            return v === null || v === undefined || v === '' ? '' : this._labels[String(v)] ?? '';
        },
        get selected() {
            const values = this.multiple && Array.isArray(this.value) ? this.value : [];
            return values.map((v) => ({ value: String(v), label: this._labels[String(v)] ?? String(v) }));
        },
        get _options() {
            return this._list
                ? Array.from(this._list.querySelectorAll('[role="option"]')).filter((option) => option.getAttribute('aria-disabled') !== 'true' && option.offsetParent !== null)
                : [];
        },
        isSelected(candidate) {
            const val = String(candidate);
            return this.multiple
                ? (Array.isArray(this.value) ? this.value : []).map(String).includes(val)
                : String(this.value ?? '') === val;
        },
        openList() {
            this.open = true;
            this.$nextTick(() => {
                if (!this._list)
                    return;
                const options = this._options;
                const selected = options.find((option) => this.isSelected(option.dataset.value));
                (selected ?? options[0] ?? this._list).focus();
            });
        },
        toggleList() {
            if (this.open)
                this.close(false);
            else
                this.openList();
        },
        close(returnFocus = true) {
            if (!this.open)
                return;
            this.open = false;
            if (returnFocus && this._trigger)
                this.$nextTick(() => this._trigger?.focus());
        },
        selectOption(val, label) {
            const key = String(val);
            this._labels[key] = label;
            if (this.multiple) {
                // Assign rather than mutate: `value` may BE the bound property,
                // and a mutation nobody assigned is a change nothing reacts to.
                const current = (Array.isArray(this.value) ? this.value : []).map(String);
                this.value = current.includes(key) ? current.filter((v) => v !== key) : [...current, key];
                return; // stay open for further picks
            }
            this.value = key;
            this.close();
        },
        seedSelected(val, label) {
            this._labels[String(val)] = label;
        },
        remove(val) {
            const key = String(val);
            this.value = (Array.isArray(this.value) ? this.value : []).map(String).filter((v) => v !== key);
        },
    };
}
//# sourceMappingURL=select.js.map