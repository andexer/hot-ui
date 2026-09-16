export function createHotDatetimePicker(config) {
    return {
        open: false,
        mode: config.mode,
        cycle: config.hourCycle ?? 'auto',
        seconds: config.seconds ?? false,
        minDate: config.minDate ?? null,
        minTime: config.minTime ?? null,
        maxDate: config.maxDate ?? null,
        maxTime: config.maxTime ?? null,
        minNights: config.minNights ?? null,
        maxNights: config.maxNights ?? null,
        _model: config.model,
        // A time picked before its date has nowhere to live in the value yet ('' carries no
        // time), so it waits here until a date arrives. Never a competing copy: the moment
        // the value can hold the time, the value is what every read returns.
        _pendingTime: null,
        _pendingFrom: null,
        _pendingTo: null,
        get model() {
            return this._model.value;
        },
        set model(v) {
            this._model.value = v;
        },
        _split(v) {
            if (!v)
                return { date: null, time: null };
            const p = String(v).replace(' ', 'T').split('T');
            return { date: p[0] || null, time: p[1] || null };
        },
        _part(part) {
            const m = this.model;
            return m && typeof m === 'object' ? (m[part] ?? null) : null;
        },
        get date() {
            return this._split(this.model).date;
        },
        set date(v) {
            this.model = this.combined(v, this.time);
        },
        get time() {
            return this._split(this.model).time ?? this._pendingTime;
        },
        set time(v) {
            this._pendingTime = v;
            this.model = this.combined(this.date, v);
        },
        get from() {
            return this._split(this._part('from')).date;
        },
        set from(v) {
            this.setRange(this.combined(v, this.timeFrom), this._part('to'));
        },
        get timeFrom() {
            return this._split(this._part('from')).time ?? this._pendingFrom;
        },
        set timeFrom(v) {
            this._pendingFrom = v;
            this.setRange(this.combined(this.from, v), this._part('to'));
        },
        get to() {
            return this._split(this._part('to')).date;
        },
        set to(v) {
            this.setRange(this._part('from'), this.combined(v, this.timeTo));
        },
        get timeTo() {
            return this._split(this._part('to')).time ?? this._pendingTo;
        },
        set timeTo(v) {
            this._pendingTo = v;
            this.setRange(this._part('from'), this.combined(this.to, v));
        },
        onTime(d) {
            if (this.mode === 'range') {
                if (d.part === 'to')
                    this.timeTo = d.value ?? null;
                else
                    this.timeFrom = d.value ?? null;
            }
            else {
                this.time = d.value ?? null;
            }
        },
        setRange(from, to) {
            const f = from ?? '';
            const t = to ?? '';
            const now = this.model;
            // Writing an unchanged range would commit a request for nothing — and, because
            // the calendar is re-seeded from this value in repaintCalendar(), would bounce
            // between the two forever.
            if (now && typeof now === 'object' && (now.from ?? '') === f && (now.to ?? '') === t)
                return;
            this.model = { from: f, to: t };
        },
        init() {
            // A value assigned anywhere but here has to reach the calendar as well, else it
            // keeps highlighting the days it opened with.
            this.$watch('_model.value', () => this.repaintCalendar());
        },
        repaintCalendar() {
            const refs = this.$refs;
            const cal = refs['cal']?.querySelector('[data-slot=calendar]');
            // Each ref is a wrapper with no x-data of its own (a ref on the component root
            // would register into ITS scope, not ours), so the field is reached by its
            // data-slot.
            const push = (ref, v) => {
                const field = refs[ref]?.querySelector('[data-slot=time-field]');
                if (field)
                    field.dispatchEvent(new CustomEvent('time:set', { detail: v ?? null, bubbles: false }));
            };
            if (this.mode === 'range') {
                if (cal) {
                    cal.dispatchEvent(new CustomEvent('calendar:set-range', {
                        detail: { from: this.from, to: this.to },
                        bubbles: false,
                    }));
                }
                push('tFrom', this.timeFrom);
                push('tTo', this.timeTo);
            }
            else {
                if (cal) {
                    cal.dispatchEvent(new CustomEvent('calendar:set', { detail: this.date || null, bubbles: false }));
                }
                push('tOne', this.time);
            }
        },
        combined(d, t) {
            return d ? `${d}T${t || '00:00'}` : '';
        },
        ms(d, t) {
            return d ? new Date(`${d}T${t || '00:00'}`).getTime() : null;
        },
        get loMs() {
            return this.minDate ? this.ms(this.minDate, this.minTime || '00:00') : null;
        },
        get hiMs() {
            return this.maxDate ? this.ms(this.maxDate, this.maxTime || '23:59:59') : null;
        },
        nights(a, b) {
            return a && b
                ? Math.round((new Date(`${b}T00:00:00`).getTime() - new Date(`${a}T00:00:00`).getTime()) / 86400000)
                : null;
        },
        plural(n) {
            return n > 1 ? 's' : '';
        },
        fmt(d, t) {
            if (!d)
                return '';
            const dt = new Date(`${d}T${t || '00:00'}`);
            if (Number.isNaN(dt.getTime()))
                return '';
            const ds = dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            if (!t)
                return ds;
            const opts = { hour: '2-digit', minute: '2-digit' };
            if (this.seconds)
                opts.second = '2-digit';
            if (this.cycle !== 'auto')
                opts.hourCycle = this.cycle === '12' ? 'h12' : 'h23';
            return `${ds}, ${dt.toLocaleTimeString(undefined, opts)}`;
        },
        get errors() {
            const e = [];
            const lo = this.loMs;
            const hi = this.hiMs;
            if (this.mode === 'range') {
                const f = this.ms(this.from, this.timeFrom);
                const t = this.ms(this.to, this.timeTo);
                if (f !== null && lo !== null && f < lo)
                    e.push('Start is before the earliest allowed date/time.');
                if (t !== null && hi !== null && t > hi)
                    e.push('End is after the latest allowed date/time.');
                if (f !== null && t !== null && t < f)
                    e.push('End is before start.');
                const n = this.nights(this.from, this.to);
                if (n !== null && this.minNights !== null && n < this.minNights) {
                    e.push(`Minimum ${String(this.minNights)} night${this.plural(this.minNights)}.`);
                }
                if (n !== null && this.maxNights !== null && n > this.maxNights) {
                    e.push(`Maximum ${String(this.maxNights)} night${this.plural(this.maxNights)}.`);
                }
            }
            else {
                const v = this.ms(this.date, this.time);
                if (v !== null && lo !== null && v < lo)
                    e.push('Before the earliest allowed date/time.');
                if (v !== null && hi !== null && v > hi)
                    e.push('After the latest allowed date/time.');
            }
            return e;
        },
        get invalid() {
            return this.errors.length > 0;
        },
        get label() {
            if (this.mode === 'range') {
                if (!this.from)
                    return '';
                return `${this.fmt(this.from, this.timeFrom)} → ${this.to ? this.fmt(this.to, this.timeTo) : '…'}`;
            }
            return this.fmt(this.date, this.time);
        },
    };
}
export default {
    name: 'datetime-picker',
    register({ alpine }) {
        alpine.data('hotDatetimePicker', createHotDatetimePicker);
    },
};
//# sourceMappingURL=datetime-picker.js.map