import { ymd } from './calendar.js';
export function createHotDatePicker(config) {
    return {
        open: false,
        mode: config.mode,
        _model: config.model,
        minNights: config.minNights ?? null,
        maxNights: config.maxNights ?? null,
        minDate: config.minDate ?? null,
        maxDate: config.maxDate ?? null,
        weekStart: config.weekStart ?? 0,
        get value() {
            return this._model.value;
        },
        set value(v) {
            this._model.value = v;
        },
        // The two ends of a range are the two halves of ONE bound value, so they are read
        // from it and written as a pair: one assignment per user action, and never a moment
        // where the property holds one end from before the pick and the other from after.
        get from() {
            const v = this._model.value;
            return v && typeof v === 'object' ? (v.from ?? null) : null;
        },
        set from(v) {
            this.setRange(v, this.to);
        },
        get to() {
            const v = this._model.value;
            return v && typeof v === 'object' ? (v.to ?? null) : null;
        },
        set to(v) {
            this.setRange(this.from, v);
        },
        setRange(from, to) {
            const f = from ?? null;
            const t = to ?? null;
            const now = this._model.value;
            // Writing an unchanged range would commit a request for nothing — and, because
            // the calendar is re-seeded from this value in repaintCalendar(), would bounce
            // between the two forever.
            if (now && typeof now === 'object' && now.from === f && now.to === t)
                return;
            this._model.value = { from: f, to: t };
        },
        init() {
            // A value assigned anywhere but here — the app layer, another component — has
            // to reach the calendar as well, else it keeps highlighting the days it opened
            // with.
            this.$watch('_model.value', () => this.repaintCalendar());
        },
        repaintCalendar() {
            const cal = this.$refs['cal']?.querySelector('[data-slot=calendar]');
            if (!cal)
                return;
            if (this.mode === 'range') {
                cal.dispatchEvent(new CustomEvent('calendar:set-range', {
                    detail: { from: this.from, to: this.to },
                    bubbles: false,
                }));
            }
            else {
                cal.dispatchEvent(new CustomEvent('calendar:set', {
                    detail: this.value || null,
                    bubbles: false,
                }));
            }
        },
        presetDates(p) {
            if (p.date)
                return { from: p.date, to: p.date };
            if (p.from || p.to)
                return { from: p.from ?? null, to: p.to ?? null };
            const now = new Date();
            const t = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const add = (d, n) => {
                const x = new Date(d);
                x.setDate(x.getDate() + n);
                return x;
            };
            const som = (d) => new Date(d.getFullYear(), d.getMonth(), 1);
            const sow = (d) => add(d, -(((d.getDay() - this.weekStart) % 7 + 7) % 7));
            const y = (d) => ymd(d);
            switch (p.key) {
                case 'today': return { from: y(t), to: y(t) };
                case 'yesterday': return { from: y(add(t, -1)), to: y(add(t, -1)) };
                case 'tomorrow': return { from: y(add(t, 1)), to: y(add(t, 1)) };
                case 'thisWeek': return { from: y(sow(t)), to: y(t) };
                case 'lastWeek': {
                    const s = add(sow(t), -7);
                    return { from: y(s), to: y(add(s, 6)) };
                }
                case 'last7Days': return { from: y(add(t, -6)), to: y(t) };
                case 'last14Days': return { from: y(add(t, -13)), to: y(t) };
                case 'last30Days': return { from: y(add(t, -29)), to: y(t) };
                case 'thisMonth': return { from: y(som(t)), to: y(t) };
                case 'lastMonth': {
                    return {
                        from: y(new Date(t.getFullYear(), t.getMonth() - 1, 1)),
                        to: y(new Date(t.getFullYear(), t.getMonth(), 0)),
                    };
                }
                case 'thisYear':
                case 'yearToDate': return { from: y(new Date(t.getFullYear(), 0, 1)), to: y(t) };
                case 'allTime': return { from: this.minDate || null, to: this.maxDate || y(t) };
                default: return { from: null, to: null };
            }
        },
        applyPreset(p) {
            const { from, to } = this.presetDates(p);
            // $refs.cal is a wrapper with no x-data of its own; the calendar owns one, so
            // we reach its root element via its data-slot.
            const cal = this.$refs['cal']?.querySelector('[data-slot=calendar]');
            if (!cal)
                return;
            // Non-bubbling dispatch on THIS calendar only — never leaks to other pickers on
            // the page. The seed reports back as calendar:updated with source
            // 'set'/'set-range', which the template's listener ignores for closing — so the
            // popover stays open on a preset click with no re-entrancy flag on our side.
            if (this.mode === 'range') {
                cal.dispatchEvent(new CustomEvent('calendar:set-range', { detail: { from, to }, bubbles: false }));
            }
            else {
                const d = to || from;
                if (d)
                    cal.dispatchEvent(new CustomEvent('calendar:set', { detail: d, bubbles: false }));
            }
        },
        isActivePreset(p) {
            const { from, to } = this.presetDates(p);
            return this.mode === 'range'
                ? (this.from === from && this.to === to)
                : (this.value === (to || from));
        },
        fmt(d) {
            return d
                ? new Date(`${d}T00:00:00`).toLocaleDateString('default', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
                : '';
        },
        nights() {
            return this.from && this.to
                ? Math.round((new Date(`${this.to}T00:00:00`).getTime() - new Date(`${this.from}T00:00:00`).getTime())
                    / 86400000)
                : null;
        },
        get errors() {
            const e = [];
            const n = this.nights();
            // Out-of-range dates — reachable only when outOfRange='flag' (else disabled).
            const lo = this.minDate;
            const hi = this.maxDate;
            const value = this.value;
            if (this.mode === 'range') {
                if (this.from && lo && this.from < lo)
                    e.push('Start is before the earliest allowed date.');
                if (this.to && hi && this.to > hi)
                    e.push('End is after the latest allowed date.');
            }
            else if (typeof value === 'string' && value) {
                if (lo && value < lo)
                    e.push('Date is before the earliest allowed.');
                if (hi && value > hi)
                    e.push('Date is after the latest allowed.');
            }
            if (n !== null && this.minNights !== null && n < this.minNights) {
                e.push(`Minimum ${String(this.minNights)} night${this.minNights > 1 ? 's' : ''}.`);
            }
            if (n !== null && this.maxNights !== null && n > this.maxNights) {
                e.push(`Maximum ${String(this.maxNights)} night${this.maxNights > 1 ? 's' : ''}.`);
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
                return `${this.fmt(this.from)} – ${this.to ? this.fmt(this.to) : '…'}`;
            }
            return typeof this.value === 'string' && this.value
                ? new Date(`${this.value}T00:00:00`).toLocaleDateString('default', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                })
                : '';
        },
    };
}
export default {
    name: 'date-picker',
    register({ alpine }) {
        alpine.data('hotDatePicker', createHotDatePicker);
    },
};
//# sourceMappingURL=date-picker.js.map