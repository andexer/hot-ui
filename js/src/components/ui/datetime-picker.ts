import type { HotModel } from '../../hot/magic/model.js';
import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotDatetimePicker — controller for the datetime-picker (calendar +
 * time-field in one popover).
 *
 * All state and logic extracted verbatim from the template's inline x-data.
 * A date and a time are the two halves of one value ('Y-m-d\TH:i'), and a
 * range is two of those; every field below is a VIEW over the bound value
 * rather than a copy of it.
 */

export type HourCycle = 'auto' | '12' | '24';

/** 'Y-m-d\TH:i' pair, either half possibly empty. */
export interface DatetimeRange {
    from?: string | null;
    to?: string | null;
}

export type DatetimeValue = string | DatetimeRange | null;

export interface DatetimePickerConfig {
    mode: 'single' | 'range';
    /** A $hot.model instance handed over by the template. */
    model: HotModel;
    hourCycle?: HourCycle;
    seconds?: boolean;
    /** Date part of the min bound ("Y-m-d"); the time part bounds validation. */
    minDate?: string | null;
    minTime?: string | null;
    maxDate?: string | null;
    maxTime?: string | null;
    minNights?: number | null;
    maxNights?: number | null;
}

interface SplitParts {
    date: string | null;
    time: string | null;
}

export interface HotDatetimePickerController {
    open: boolean;
    mode: 'single' | 'range';
    cycle: HourCycle;
    seconds: boolean;
    minDate: string | null;
    minTime: string | null;
    maxDate: string | null;
    maxTime: string | null;
    minNights: number | null;
    maxNights: number | null;
    _model: HotModel;
    _pendingTime: string | null;
    _pendingFrom: string | null;
    _pendingTo: string | null;

    get model(): DatetimeValue;
    set model(v: DatetimeValue);
    _split(v: unknown): SplitParts;
    _part(part: 'from' | 'to'): string | null;
    get date(): string | null;
    set date(v: string | null);
    get time(): string | null;
    set time(v: string | null);
    get from(): string | null;
    set from(v: string | null);
    get timeFrom(): string | null;
    set timeFrom(v: string | null);
    get to(): string | null;
    set to(v: string | null);
    get timeTo(): string | null;
    set timeTo(v: string | null);
    onTime(d: { part?: string | null; value?: string | null }): void;
    setRange(from: string | null, to: string | null): void;
    init(): void;
    repaintCalendar(): void;
    combined(d: string | null, t: string | null): string;
    ms(d: string | null, t: string | null): number | null;
    readonly loMs: number | null;
    readonly hiMs: number | null;
    nights(a: string | null, b: string | null): number | null;
    plural(n: number): string;
    fmt(d: string | null, t: string | null): string;
    readonly errors: string[];
    readonly invalid: boolean;
    readonly label: string;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface DatetimePickerScope {
    $watch(source: string, callback: (value: unknown) => void): void;
    $refs: Record<string, HTMLElement>;
}

type Live = HotDatetimePickerController & DatetimePickerScope;

export function createHotDatetimePicker(config: DatetimePickerConfig): HotDatetimePickerController {
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

        get model(): DatetimeValue {
            return this._model.value as DatetimeValue;
        },
        set model(v: DatetimeValue) {
            this._model.value = v;
        },

        _split(v: unknown): SplitParts {
            if (!v) return { date: null, time: null };
            const p = String(v).replace(' ', 'T').split('T');

            return { date: p[0] || null, time: p[1] || null };
        },
        _part(part: 'from' | 'to'): string | null {
            const m = this.model;

            return m && typeof m === 'object' ? (m[part] ?? null) : null;
        },

        get date(): string | null {
            return this._split(this.model).date;
        },
        set date(v: string | null) {
            this.model = this.combined(v, this.time);
        },
        get time(): string | null {
            return this._split(this.model).time ?? this._pendingTime;
        },
        set time(v: string | null) {
            this._pendingTime = v;
            this.model = this.combined(this.date, v);
        },

        get from(): string | null {
            return this._split(this._part('from')).date;
        },
        set from(v: string | null) {
            this.setRange(this.combined(v, this.timeFrom), this._part('to'));
        },
        get timeFrom(): string | null {
            return this._split(this._part('from')).time ?? this._pendingFrom;
        },
        set timeFrom(v: string | null) {
            this._pendingFrom = v;
            this.setRange(this.combined(this.from, v), this._part('to'));
        },
        get to(): string | null {
            return this._split(this._part('to')).date;
        },
        set to(v: string | null) {
            this.setRange(this._part('from'), this.combined(v, this.timeTo));
        },
        get timeTo(): string | null {
            return this._split(this._part('to')).time ?? this._pendingTo;
        },
        set timeTo(v: string | null) {
            this._pendingTo = v;
            this.setRange(this._part('from'), this.combined(this.to, v));
        },

        onTime(d: { part?: string | null; value?: string | null }): void {
            if (this.mode === 'range') {
                if (d.part === 'to') this.timeTo = d.value ?? null;
                else this.timeFrom = d.value ?? null;
            } else {
                this.time = d.value ?? null;
            }
        },

        setRange(from: string | null, to: string | null): void {
            const f = from ?? '';
            const t = to ?? '';
            const now = this.model;
            // Writing an unchanged range would commit a request for nothing — and, because
            // the calendar is re-seeded from this value in repaintCalendar(), would bounce
            // between the two forever.
            if (now && typeof now === 'object' && (now.from ?? '') === f && (now.to ?? '') === t) return;
            this.model = { from: f, to: t };
        },

        init() {
            // A value assigned anywhere but here has to reach the calendar as well, else it
            // keeps highlighting the days it opened with.
            (this as Live).$watch('_model.value', () => this.repaintCalendar());
        },

        repaintCalendar(): void {
            const refs = (this as Live).$refs;
            const cal = refs['cal']?.querySelector<HTMLElement>('[data-slot=calendar]');
            // Each ref is a wrapper with no x-data of its own (a ref on the component root
            // would register into ITS scope, not ours), so the field is reached by its
            // data-slot.
            const push = (ref: string, v: string | null): void => {
                const field = refs[ref]?.querySelector<HTMLElement>('[data-slot=time-field]');
                if (field) field.dispatchEvent(new CustomEvent('time:set', { detail: v ?? null, bubbles: false }));
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
            } else {
                if (cal) {
                    cal.dispatchEvent(new CustomEvent('calendar:set', { detail: this.date || null, bubbles: false }));
                }
                push('tOne', this.time);
            }
        },

        combined(d: string | null, t: string | null): string {
            return d ? `${d}T${t || '00:00'}` : '';
        },
        ms(d: string | null, t: string | null): number | null {
            return d ? new Date(`${d}T${t || '00:00'}`).getTime() : null;
        },
        get loMs(): number | null {
            return this.minDate ? this.ms(this.minDate, this.minTime || '00:00') : null;
        },
        get hiMs(): number | null {
            return this.maxDate ? this.ms(this.maxDate, this.maxTime || '23:59:59') : null;
        },
        nights(a: string | null, b: string | null): number | null {
            return a && b
                ? Math.round((new Date(`${b}T00:00:00`).getTime() - new Date(`${a}T00:00:00`).getTime()) / 86400000)
                : null;
        },
        plural(n: number): string {
            return n > 1 ? 's' : '';
        },
        fmt(d: string | null, t: string | null): string {
            if (!d) return '';
            const dt = new Date(`${d}T${t || '00:00'}`);
            if (Number.isNaN(dt.getTime())) return '';
            const ds = dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            if (!t) return ds;
            const opts: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit' };
            if (this.seconds) opts.second = '2-digit';
            if (this.cycle !== 'auto') opts.hourCycle = this.cycle === '12' ? 'h12' : 'h23';

            return `${ds}, ${dt.toLocaleTimeString(undefined, opts)}`;
        },

        get errors(): string[] {
            const e: string[] = [];
            const lo = this.loMs;
            const hi = this.hiMs;
            if (this.mode === 'range') {
                const f = this.ms(this.from, this.timeFrom);
                const t = this.ms(this.to, this.timeTo);
                if (f !== null && lo !== null && f < lo) e.push('Start is before the earliest allowed date/time.');
                if (t !== null && hi !== null && t > hi) e.push('End is after the latest allowed date/time.');
                if (f !== null && t !== null && t < f) e.push('End is before start.');
                const n = this.nights(this.from, this.to);
                if (n !== null && this.minNights !== null && n < this.minNights) {
                    e.push(`Minimum ${String(this.minNights)} night${this.plural(this.minNights)}.`);
                }
                if (n !== null && this.maxNights !== null && n > this.maxNights) {
                    e.push(`Maximum ${String(this.maxNights)} night${this.plural(this.maxNights)}.`);
                }
            } else {
                const v = this.ms(this.date, this.time);
                if (v !== null && lo !== null && v < lo) e.push('Before the earliest allowed date/time.');
                if (v !== null && hi !== null && v > hi) e.push('After the latest allowed date/time.');
            }

            return e;
        },
        get invalid(): boolean {
            return this.errors.length > 0;
        },
        get label(): string {
            if (this.mode === 'range') {
                if (!this.from) return '';

                return `${this.fmt(this.from, this.timeFrom)} → ${this.to ? this.fmt(this.to, this.timeTo) : '…'}`;
            }

            return this.fmt(this.date, this.time);
        },
    };
}

export default {
    name: 'datetime-picker',
    register({ alpine }: HotContext): void {
        alpine.data('hotDatetimePicker', createHotDatetimePicker as never);
    },
} satisfies IslandPlugin;
