export function createHotTimeField(config) {
    return {
        _model: config.model,
        cycle: config.hourCycle ?? 'auto',
        seconds: config.seconds ?? false,
        part: config.part ?? null,
        minStep: config.minuteStep ?? 1,
        secStep: config.secondStep ?? 1,
        h: null,
        m: null,
        s: 0,
        _onSet: null,
        get value() {
            return this._model.value;
        },
        set value(v) {
            this._model.value = v;
        },
        init() {
            const live = this;
            this.readValue();
            // The hours/minutes/seconds the dropdowns show are a decomposition of `value`, so
            // they have to be recomposed whenever it changes from outside — a bound store the
            // app just assigned, or a parent pushing one in below. Seeding them once at init is
            // only correct on a page where the value never changes underneath.
            live.$watch('value', () => this.readValue());
            // `time:set` is how a container drives this field, mirroring the calendar's own
            // calendar:set hook. Bound to this root, never bubbled, so one field on a page
            // full of them can be addressed on its own.
            live._onSet = (e) => {
                this.value = (e.detail ?? null);
            };
            live.$root.addEventListener('time:set', live._onSet);
        },
        destroy() {
            const live = this;
            if (live._onSet)
                live.$root.removeEventListener('time:set', live._onSet);
        },
        readValue() {
            const p = String(this.value ?? '').split(':').map(Number);
            const h = p[0];
            const m = p[1];
            const s = p[2];
            this.h = h !== undefined && Number.isFinite(h) ? h : null;
            this.m = m !== undefined && Number.isFinite(m) ? m : null;
            this.s = s !== undefined && Number.isFinite(s) ? s : 0;
        },
        get cyc() {
            if (this.cycle !== 'auto')
                return this.cycle;
            try {
                return Intl.DateTimeFormat().resolvedOptions().hour12 ? '12' : '24';
            }
            catch {
                return '24';
            }
        },
        get hourOpts() {
            return this.cyc === '12'
                ? Array.from({ length: 12 }, (_, i) => i + 1)
                : Array.from({ length: 24 }, (_, i) => i);
        },
        get minOpts() {
            const o = [];
            for (let i = 0; i < 60; i += this.minStep)
                o.push(i);
            return o;
        },
        get secOpts() {
            const o = [];
            for (let i = 0; i < 60; i += this.secStep)
                o.push(i);
            return o;
        },
        get period() {
            return this.h === null ? 'AM' : (this.h < 12 ? 'AM' : 'PM');
        },
        get hour12() {
            return this.h === null ? null : ((this.h + 11) % 12) + 1;
        },
        pad(n) {
            return String(n ?? 0).padStart(2, '0');
        },
        setH(v) {
            this.h = Number(v);
            if (this.m === null)
                this.m = 0;
            this.sync();
        },
        setH12(v) {
            const base = Number(v) % 12;
            this.h = this.period === 'PM' ? base + 12 : base;
            if (this.m === null)
                this.m = 0;
            this.sync();
        },
        setM(v) {
            this.m = Number(v);
            if (this.h === null)
                this.h = 0;
            this.sync();
        },
        setS(v) {
            this.s = Number(v);
            if (this.h === null)
                this.h = 0;
            if (this.m === null)
                this.m = 0;
            this.sync();
        },
        setPeriod(p) {
            if (this.h === null) {
                this.h = p === 'PM' ? 12 : 0;
            }
            else {
                const base = this.h % 12;
                this.h = p === 'PM' ? base + 12 : base;
            }
            if (this.m === null)
                this.m = 0;
            this.sync();
        },
        fromInput(v) {
            this.value = v || null;
            this.$dispatch('time-change', { value: this.value, part: this.part });
        },
        sync() {
            this.value = (this.h !== null && this.m !== null)
                ? this.pad(this.h) + ':' + this.pad(this.m) + (this.seconds ? ':' + this.pad(this.s) : '')
                : null;
            this.$dispatch('time-change', { value: this.value, part: this.part });
        },
    };
}
export default {
    name: 'time-field',
    register({ alpine }) {
        alpine.data('hotTimeField', createHotTimeField);
    },
};
//# sourceMappingURL=time-field.js.map