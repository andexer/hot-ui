export function createHotColorPicker(config) {
    return {
        open: false,
        disabled: config.disabled ?? false,
        inline: config.inline ?? false,
        _model: config.model,
        hue: 0,
        input: config.input ?? '',
        swatches: config.swatches ?? [],
        get hex() {
            return this._model.value;
        },
        set hex(v) {
            this._model.value = v;
        },
        init() {
            // Seed the visible text input + hue from the resolved starting colour.
            const norm = this.normalize(this.hex);
            if (norm) {
                this.hex = norm;
                this.input = norm;
            }
            this.hue = this.hexToHue(this.hex);
        },
        // Accept #rgb / #rrggbb (with or without leading #); return a normalized
        // lower-case #rrggbb string, or null when invalid.
        normalize(v) {
            if (typeof v !== 'string')
                return null;
            let s = v.trim().replace(/^#/, '').toLowerCase();
            if (/^[0-9a-f]{3}$/.test(s)) {
                s = s.split('').map((c) => c + c).join('');
            }
            if (/^[0-9a-f]{6}$/.test(s))
                return '#' + s;
            return null;
        },
        // HSL(h, 90%, 50%) → #rrggbb. Used while dragging the hue slider.
        hslToHex(h) {
            const s = 0.9;
            const l = 0.5;
            const c = (1 - Math.abs(2 * l - 1)) * s;
            const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
            const m = l - c / 2;
            let r = 0;
            let g = 0;
            let b = 0;
            if (h < 60) {
                r = c;
                g = x;
                b = 0;
            }
            else if (h < 120) {
                r = x;
                g = c;
                b = 0;
            }
            else if (h < 180) {
                r = 0;
                g = c;
                b = x;
            }
            else if (h < 240) {
                r = 0;
                g = x;
                b = c;
            }
            else if (h < 300) {
                r = x;
                g = 0;
                b = c;
            }
            else {
                r = c;
                g = 0;
                b = x;
            }
            const to = (n) => Math.round((n + m) * 255).toString(16).padStart(2, '0');
            return '#' + to(r) + to(g) + to(b);
        },
        // #rrggbb → hue (0..360) so a typed/picked colour positions the slider.
        hexToHue(hex) {
            const n = this.normalize(hex);
            if (!n)
                return 0;
            const r = parseInt(n.slice(1, 3), 16) / 255;
            const g = parseInt(n.slice(3, 5), 16) / 255;
            const b = parseInt(n.slice(5, 7), 16) / 255;
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            const d = max - min;
            if (d === 0)
                return 0;
            let h = 0;
            if (max === r)
                h = ((g - b) / d) % 6;
            else if (max === g)
                h = (b - r) / d + 2;
            else
                h = (r - g) / d + 4;
            h = Math.round(h * 60);
            return h < 0 ? h + 360 : h;
        },
        setHue(h) {
            this.hue = Number(h);
            this.hex = this.hslToHex(this.hue);
            this.input = this.hex;
        },
        // Live-validate the text field; only commit when it parses.
        commit(v) {
            const n = this.normalize(v);
            if (n) {
                this.hex = n;
                this.hue = this.hexToHue(n);
            }
        },
        // On blur, snap the visible text back to the last valid colour.
        sync() {
            this.input = this.hex;
        },
        pick(c) {
            const n = this.normalize(c);
            if (!n)
                return;
            this.hex = n;
            this.input = n;
            this.hue = this.hexToHue(n);
        },
        get isValid() {
            return this.normalize(this.input) !== null;
        },
    };
}
export default {
    name: 'color-picker',
    register({ alpine }) {
        alpine.data('hotColorPicker', createHotColorPicker);
    },
};
//# sourceMappingURL=color-picker.js.map