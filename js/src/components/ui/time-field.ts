import type { HotModel } from '../../hot/magic/model.js';
import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotTimeField — controller for the time-field.
 *
 * All state and logic extracted verbatim from the template's inline x-data:
 * `value` ('HH:mm' | 'HH:mm:ss') is decomposed into hours/minutes/seconds for
 * the select variant and recomposed on every pick, with the whole flowing
 * through $hot.model.
 */

export type TimeFieldHourCycle = 'auto' | '12' | '24';

export interface TimeFieldConfig {
    /** A $hot.model instance handed over by the template. */
    model: HotModel;
    hourCycle?: TimeFieldHourCycle;
    seconds?: boolean;
    /** Composition tag echoed back in every time-change event. */
    part?: string | null;
    minuteStep?: number;
    secondStep?: number;
}

export interface HotTimeFieldController {
    _model: HotModel;
    cycle: TimeFieldHourCycle;
    seconds: boolean;
    part: string | null;
    minStep: number;
    secStep: number;
    h: number | null;
    m: number | null;
    s: number;
    _onSet: ((e: Event) => void) | null;

    get value(): string | null;
    set value(v: string | null);
    init(): void;
    destroy(): void;
    readValue(): void;
    get cyc(): '12' | '24';
    get hourOpts(): number[];
    get minOpts(): number[];
    get secOpts(): number[];
    get period(): 'AM' | 'PM';
    get hour12(): number | null;
    pad(n: number | null | undefined): string;
    setH(v: string): void;
    setH12(v: string): void;
    setM(v: string): void;
    setS(v: string): void;
    setPeriod(p: 'AM' | 'PM'): void;
    fromInput(v: string): void;
    sync(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface TimeFieldScope {
    $watch(source: string, callback: (value: unknown) => void): void;
    $dispatch(name: string, detail?: unknown): void;
    $root: HTMLElement;
}

type Live = HotTimeFieldController & TimeFieldScope;

export function createHotTimeField(config: TimeFieldConfig): HotTimeFieldController {
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

        get value(): string | null {
            return this._model.value as string | null;
        },
        set value(v: string | null) {
            this._model.value = v;
        },

        init() {
            const live = this as Live;
            this.readValue();
            // The hours/minutes/seconds the dropdowns show are a decomposition of `value`, so
            // they have to be recomposed whenever it changes from outside — a bound store the
            // app just assigned, or a parent pushing one in below. Seeding them once at init is
            // only correct on a page where the value never changes underneath.
            live.$watch('value', () => this.readValue());
            // `time:set` is how a container drives this field, mirroring the calendar's own
            // calendar:set hook. Bound to this root, never bubbled, so one field on a page
            // full of them can be addressed on its own.
            live._onSet = (e: Event): void => {
                this.value = ((e as CustomEvent).detail ?? null) as string | null;
            };
            live.$root.addEventListener('time:set', live._onSet);
        },
        destroy() {
            const live = this as Live;

            if (live._onSet) live.$root.removeEventListener('time:set', live._onSet);
        },
        readValue(): void {
            const p = String(this.value ?? '').split(':').map(Number);
            const h = p[0];
            const m = p[1];
            const s = p[2];
            this.h = h !== undefined && Number.isFinite(h) ? h : null;
            this.m = m !== undefined && Number.isFinite(m) ? m : null;
            this.s = s !== undefined && Number.isFinite(s) ? s : 0;
        },
        get cyc(): '12' | '24' {
            if (this.cycle !== 'auto') return this.cycle;
            try {
                return Intl.DateTimeFormat().resolvedOptions().hour12 ? '12' : '24';
            } catch {
                return '24';
            }
        },
        get hourOpts(): number[] {
            return this.cyc === '12'
                ? Array.from({ length: 12 }, (_, i) => i + 1)
                : Array.from({ length: 24 }, (_, i) => i);
        },
        get minOpts(): number[] {
            const o: number[] = [];
            for (let i = 0; i < 60; i += this.minStep) o.push(i);

            return o;
        },
        get secOpts(): number[] {
            const o: number[] = [];
            for (let i = 0; i < 60; i += this.secStep) o.push(i);

            return o;
        },
        get period(): 'AM' | 'PM' {
            return this.h === null ? 'AM' : (this.h < 12 ? 'AM' : 'PM');
        },
        get hour12(): number | null {
            return this.h === null ? null : ((this.h + 11) % 12) + 1;
        },
        pad(n: number | null | undefined): string {
            return String(n ?? 0).padStart(2, '0');
        },
        setH(v: string): void {
            this.h = Number(v);
            if (this.m === null) this.m = 0;
            this.sync();
        },
        setH12(v: string): void {
            const base = Number(v) % 12;
            this.h = this.period === 'PM' ? base + 12 : base;
            if (this.m === null) this.m = 0;
            this.sync();
        },
        setM(v: string): void {
            this.m = Number(v);
            if (this.h === null) this.h = 0;
            this.sync();
        },
        setS(v: string): void {
            this.s = Number(v);
            if (this.h === null) this.h = 0;
            if (this.m === null) this.m = 0;
            this.sync();
        },
        setPeriod(p: 'AM' | 'PM'): void {
            if (this.h === null) {
                this.h = p === 'PM' ? 12 : 0;
            } else {
                const base = this.h % 12;
                this.h = p === 'PM' ? base + 12 : base;
            }
            if (this.m === null) this.m = 0;
            this.sync();
        },
        fromInput(v: string): void {
            this.value = v || null;
            (this as Live).$dispatch('time-change', { value: this.value, part: this.part });
        },
        sync(): void {
            this.value = (this.h !== null && this.m !== null)
                ? this.pad(this.h) + ':' + this.pad(this.m) + (this.seconds ? ':' + this.pad(this.s) : '')
                : null;
            (this as Live).$dispatch('time-change', { value: this.value, part: this.part });
        },
    };
}

export default {
    name: 'time-field',
    register({ alpine }: HotContext): void {
        alpine.data('hotTimeField', createHotTimeField as never);
    },
} satisfies IslandPlugin;
