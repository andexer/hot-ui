// ---------------------------------------------------------------------------
// Shared date helpers — consumed by this island and by date-picker /
// datetime-picker via `import … from './calendar.js'`.
// ---------------------------------------------------------------------------
/** Date → 'Y-m-d' (local time, zero-padded). */
export function ymd(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
/** 'Y-m-d' | 'YYYY-MM' | Date → midnight-local Date; anything else → null. */
export function parseDate(value) {
    if (!value)
        return null;
    if (value instanceof Date)
        return new Date(value.getFullYear(), value.getMonth(), value.getDate());
    const p = String(value).split('-').map(Number);
    return new Date(p[0] ?? NaN, (p[1] || 1) - 1, p[2] || 1);
}
/** True when both arguments land on the same local calendar day. */
export function sameDay(a, b) {
    return Boolean(a && b && ymd(a) === ymd(b));
}
/** First of the month `n` months away from `d`'s month. */
export function addMonths(d, n) {
    return new Date(d.getFullYear(), d.getMonth() + n, 1);
}
/**
 * hotCalendar controller. IMPORTANT: every method MUST go through `this` —
 * Alpine wraps the factory result in a reactive proxy and binds methods to it;
 * writing through any other reference would bypass reactivity and freeze the UI.
 */
export function createHotCalendar(config = {}) {
    return {
        calendarId: config.calendarId ?? null,
        mode: config.mode ?? 'single',
        locale: config.locale || 'en-US',
        // aria-label fragments, localised by the template layer (never hardcoded English here).
        todayLabel: config.todayLabel || 'Today, :date',
        selectedLabel: config.selectedLabel || 'selected',
        numberOfMonths: config.numberOfMonths || 1,
        weekStart: config.weekStart || 0,
        captionLayout: config.captionLayout || 'label',
        showWeekNumber: Boolean(config.showWeekNumber),
        disableNavigation: Boolean(config.disableNavigation),
        minDays: config.minDays ?? config.min ?? null, // explicit minDays/maxDays preferred; min/max back-compat
        maxDays: config.maxDays ?? config.max ?? null,
        disabledCfg: config.disabled ?? null,
        minDate: config.minDate ? parseDate(config.minDate) : null,
        maxDate: config.maxDate ? parseDate(config.maxDate) : null,
        outOfRange: config.outOfRange ?? 'disable',
        modifiers: config.modifiers ?? {},
        modifiersClass: config.modifiersClass ?? {},
        required: config.required ?? false,
        startMonth: null,
        endMonth: null,
        view: new Date(),
        weekdays: [],
        single: null,
        multiple: [],
        rangeFrom: null,
        rangeTo: null,
        hover: null,
        focusedDate: new Date(),
        _hooks: null,
        _rootEl: null,
        init() {
            this.startMonth = config.startMonth ? parseDate(config.startMonth) : null;
            this.endMonth = config.endMonth ? parseDate(config.endMonth) : null;
            // Seed selection from the value prop.
            if (this.mode === 'single')
                this.single = config.value ? parseDate(config.value) : null;
            else if (this.mode === 'multiple') {
                const raw = Array.isArray(config.value) ? config.value : config.value ? [config.value] : [];
                this.multiple = raw
                    .filter(Boolean)
                    .map((x) => parseDate(x))
                    .filter((x) => x !== null);
            }
            else if (this.mode === 'range') {
                const seed = (config.value && typeof config.value === 'object' && !Array.isArray(config.value)
                    ? config.value
                    : {});
                this.rangeFrom = seed.from ? parseDate(seed.from) : null;
                this.rangeTo = seed.to ? parseDate(seed.to) : null;
            }
            // Default month: explicit prop, else first selected date / bounds / today.
            const seeded = config.defaultMonth && typeof config.defaultMonth === 'string'
                ? parseDate(config.defaultMonth.length === 7 ? `${config.defaultMonth}-01` : config.defaultMonth)
                : null;
            const base = seeded
                ?? this.single
                ?? this.rangeFrom
                ?? this.multiple[0]
                ?? this.startMonth
                ?? new Date();
            this.view = new Date(base.getFullYear(), base.getMonth(), 1);
            // Roving focus anchor for the grid (APG date-picker keyboard pattern).
            this.focusedDate =
                this.single
                    ?? this.rangeFrom
                    ?? this.multiple[0]
                    ?? new Date(base.getFullYear(), base.getMonth(), base.getDate());
            // Weekday headers, starting at the configured first day of week.
            const ref = new Date(2023, 0, 1);
            for (let i = 0; i < 7; i++) {
                const d = new Date(ref);
                d.setDate(ref.getDate() + ((this.weekStart + i + 7 - ref.getDay()) % 7));
                this.weekdays.push(d.toLocaleString(this.locale, { weekday: 'narrow' }));
            }
            // ---- External control hooks ---------------------------------------
            // CONTRACT — an incoming hook NEVER emits `calendar-change`. That event means
            // "the user picked a day"; seeding is not a pick. Programmatic changes are
            // observable through `calendar:updated` (source: 'set' | 'set-range' |
            // 'today' | 'clear' | 'value'), which never trips a "close when complete"
            // handler.
            //
            // MODE — every hook tests the mode FIRST, before touching the view.
            //
            // TARGETING — each hook listens on BOTH this root element and `window`:
            //   • dispatch on the element (`bubbles: false`) → this instance only;
            //   • dispatch on `window` with `detail.id`     → matching calendarId only;
            //   • dispatch on `window` without an id        → every calendar (legacy).
            const mine = (e) => {
                const d = e.detail;
                const id = d && typeof d === 'object' && !(d instanceof Date)
                    ? d['id']
                    : null;
                return !id || id === this.calendarId;
            };
            // Detail may be a bare 'YYYY-MM-DD' / Date, or { date, id } to target an instance.
            const payload = (e) => {
                const d = e.detail;
                if (d && typeof d === 'object' && !(d instanceof Date)) {
                    const rec = d;
                    return rec['date'] ?? rec['month'] ?? null;
                }
                return d;
            };
            const onToday = (e) => {
                if (this.mode !== 'single' || !mine(e))
                    return;
                if (this.setValue(ymd(new Date())))
                    this.notify('today');
            };
            const onSet = (e) => {
                if (this.mode !== 'single' || !mine(e))
                    return;
                const t = parseDate(payload(e));
                if (t && this.setValue(ymd(t)))
                    this.notify('set');
            };
            // Detail: { from: 'YYYY-MM-DD'|Date|null, to: 'YYYY-MM-DD'|Date|null, id? }.
            const onSetRange = (e) => {
                if (this.mode !== 'range' || !mine(e))
                    return;
                const d = (e.detail ?? {}) || {};
                if (this.setValue({ from: d.from ?? null, to: d.to ?? null }))
                    this.notify('set-range');
            };
            // View-only navigation — no selection, so it works in every mode.
            // Detail: 'YYYY-MM' | 'YYYY-MM-DD' | Date | { month, id? }.
            const onGoto = (e) => {
                if (!mine(e))
                    return;
                const raw = payload(e);
                const m = parseDate(typeof raw === 'string' && raw.length === 7 ? `${raw}-01` : raw);
                if (m)
                    this.view = new Date(m.getFullYear(), m.getMonth(), 1);
            };
            const onClear = (e) => {
                if (!mine(e))
                    return;
                const empty = this.mode === 'single' ? null : this.mode === 'multiple' ? [] : { from: null, to: null };
                if (this.setValue(empty))
                    this.notify('clear');
            };
            this._hooks = {
                'calendar:today': onToday,
                'calendar:set': onSet,
                'calendar:set-range': onSetRange,
                'calendar:goto': onGoto,
                'calendar:clear': onClear,
            };
            this._rootEl = this.$root;
            for (const target of [window, this._rootEl]) {
                for (const name in this._hooks)
                    target.addEventListener(name, this._hooks[name]);
            }
        },
        // Alpine calls this when the component leaves the DOM. The window-level hooks would
        // otherwise outlive it across re-renders / SPA navigations.
        destroy() {
            if (!this._hooks || !this._rootEl)
                return;
            for (const target of [window, this._rootEl]) {
                for (const name in this._hooks) {
                    target.removeEventListener(name, this._hooks[name]);
                }
            }
        },
        // ---- controlled value -------------------------------------------------
        // `value` is the whole selection in one reactive property, so the calendar can be
        // driven from outside with Alpine's own two-way binding (the root carries
        // x-modelable="value"). Shape per mode: single 'Y-m-d'|null · multiple ['Y-m-d',
        // …] · range { from, to }.
        get value() {
            if (this.mode === 'single')
                return this.single ? ymd(this.single) : null;
            if (this.mode === 'multiple')
                return this.multiple.map(ymd);
            return {
                from: this.rangeFrom ? ymd(this.rangeFrom) : null,
                to: this.rangeTo ? ymd(this.rangeTo) : null,
            };
        },
        set value(v) {
            if (this.setValue(v))
                this.notify('value');
        },
        // Seed the selection and scroll it into view. Returns whether anything actually
        // changed — the caller uses that both to skip a pointless event and to keep the
        // two-way binding from ping-ponging (a write of the value we already hold is a
        // no-op, not a new change).
        setValue(v) {
            if (this.mode === 'single') {
                const parsed = v ? parseDate(v) : null;
                const next = parsed ? ymd(parsed) : null;
                const changed = next !== (this.single ? ymd(this.single) : null);
                this.single = parsed;
                if (parsed)
                    this.reveal(parsed);
                return changed;
            }
            if (this.mode === 'multiple') {
                const raw = Array.isArray(v) ? v : v ? [v] : [];
                const list = [];
                for (const item of raw) {
                    const d = item ? parseDate(item) : null;
                    if (d)
                        list.push(d);
                }
                const changed = list.map(ymd).join(',') !== this.multiple.map(ymd).join(',');
                this.multiple = list;
                if (list[0])
                    this.reveal(list[0]);
                return changed;
            }
            const seed = (v && typeof v === 'object' && !Array.isArray(v) ? v : {});
            const from = seed.from ? parseDate(seed.from) : null;
            const to = seed.to ? parseDate(seed.to) : null;
            const key = (a, b) => `${this.fmt(a)}/${this.fmt(b)}`;
            const changed = key(from, to) !== key(this.rangeFrom, this.rangeTo);
            this.rangeFrom = from;
            this.rangeTo = to;
            this.hover = null;
            if (from)
                this.reveal(from);
            return changed;
        },
        // Bring a date into the visible month(s) — but only when it isn't already there, so
        // re-seeding the current selection never yanks a calendar the user just navigated.
        // Also re-anchors roving focus (APG: Tab lands on the selected day, not on day 1).
        reveal(d) {
            if (!this._viewContains(d))
                this.view = new Date(d.getFullYear(), d.getMonth(), 1);
            this.focusedDate = d;
        },
        // ---- grid building ----
        get months() {
            return Array.from({ length: this.numberOfMonths }, (_, i) => addMonths(this.view, i));
        },
        monthLabel(m) {
            return m.toLocaleString(this.locale, { month: 'long', year: 'numeric' });
        },
        weeksFor(m) {
            const year = m.getFullYear();
            const month = m.getMonth();
            const first = new Date(year, month, 1);
            const offset = (first.getDay() - this.weekStart + 7) % 7;
            const start = new Date(year, month, 1 - offset);
            const weeks = [];
            for (let w = 0; w < 6; w++) {
                const days = [];
                for (let d = 0; d < 7; d++) {
                    const day = new Date(start);
                    day.setDate(start.getDate() + w * 7 + d);
                    // Stamp prev/next-month status here, where the panel month is correct.
                    // The template reads day.__outside instead of isOutside(day, m): the
                    // outer-loop `m` goes stale in the nested per-cell bindings after a
                    // month navigation, which mislabels outside days.
                    day.__outside = day.getMonth() !== month;
                    days.push(day);
                }
                weeks.push(days);
            }
            return weeks;
        },
        weekNumber(week) {
            const head = week[0];
            if (!head)
                return 1;
            const d = new Date(head);
            d.setDate(d.getDate() + 3 - ((d.getDay() + 6) % 7));
            const firstThu = new Date(d.getFullYear(), 0, 4);
            return 1 + Math.round((d.getTime() - firstThu.getTime()) / 86400000 - 3 + ((firstThu.getDay() + 6) % 7)) / 7;
        },
        // ---- predicates ----
        isOutside(d, m) {
            return d.getMonth() !== m.getMonth();
        },
        isToday(d) {
            return sameDay(d, new Date());
        },
        isOutOfRange(d) {
            return Boolean((this.minDate && d.getTime() < this.minDate.getTime())
                || (this.maxDate && d.getTime() > this.maxDate.getTime()));
        },
        isDisabled(d) {
            // Out-of-range dates are disabled UNLESS outOfRange === 'flag' (then they stay
            // selectable but are flagged red via data-out-of-range).
            if (this.outOfRange !== 'flag' && this.isOutOfRange(d))
                return true;
            if (this.startMonth
                && d.getTime() < new Date(this.startMonth.getFullYear(), this.startMonth.getMonth(), 1).getTime())
                return true;
            if (this.endMonth
                && d.getTime() > new Date(this.endMonth.getFullYear(), this.endMonth.getMonth() + 1, 0).getTime())
                return true;
            const c = this.disabledCfg;
            if (!c)
                return false;
            if (Array.isArray(c))
                return c.some((x) => sameDay(parseDate(x), d));
            if (typeof c === 'object') {
                if (c.before) {
                    const before = parseDate(c.before);
                    if (before && d.getTime() < before.getTime())
                        return true;
                }
                if (c.after) {
                    const after = parseDate(c.after);
                    if (after && d.getTime() > after.getTime())
                        return true;
                }
                if (Array.isArray(c.dayOfWeek) && c.dayOfWeek.includes(d.getDay()))
                    return true;
            }
            return false;
        },
        isSelected(d) {
            if (this.mode === 'single')
                return sameDay(this.single, d);
            if (this.mode === 'multiple')
                return this.multiple.some((x) => sameDay(x, d));
            return this.rangeIs(d).selected;
        },
        rangeIs(d) {
            const from = this.rangeFrom;
            const to = this.rangeTo ?? (this.rangeFrom && this.hover ? this.hover : null);
            if (!from)
                return { selected: false, start: false, end: false, middle: false };
            const lo = to && to.getTime() < from.getTime() ? to : from;
            const hi = to && to.getTime() < from.getTime() ? from : to;
            const isStart = sameDay(d, lo);
            const isEnd = hi ? sameDay(d, hi) : isStart;
            const inMid = Boolean(hi && d.getTime() > lo.getTime() && d.getTime() < hi.getTime());
            return { selected: isStart || isEnd || inMid, start: isStart, end: isEnd, middle: inMid };
        },
        modifierClass(d) {
            let cls = '';
            for (const name in this.modifiers) {
                const list = this.modifiers[name];
                if (list?.some((x) => sameDay(parseDate(x), d)))
                    cls += ` ${(this.modifiersClass[name] ?? '')}`;
            }
            return cls;
        },
        // ---- interaction ----
        select(d) {
            if (this.isDisabled(d))
                return;
            if (this.mode === 'single') {
                this.single = sameDay(this.single, d) && !this.required ? null : d;
            }
            else if (this.mode === 'multiple') {
                const i = this.multiple.findIndex((x) => sameDay(x, d));
                if (i >= 0)
                    this.multiple.splice(i, 1);
                else if (!this.maxDays || this.multiple.length < this.maxDays)
                    this.multiple.push(d);
            }
            else if (!this.rangeFrom || this.rangeTo) {
                this.rangeFrom = d;
                this.rangeTo = null;
            }
            else {
                let from = this.rangeFrom;
                let to = d;
                if (to.getTime() < from.getTime())
                    [from, to] = [to, from];
                const span = Math.round((to.getTime() - from.getTime()) / 86400000) + 1;
                if ((this.minDays && span < this.minDays) || (this.maxDays && span > this.maxDays)) {
                    // Out of the allowed duration window → start a brand-new range here.
                    this.rangeFrom = d;
                    this.rangeTo = null;
                }
                else {
                    this.rangeFrom = from;
                    this.rangeTo = to;
                }
            }
            this.notify('select');
        },
        // ---- events -----------------------------------------------------------
        // Two events, on purpose:
        //   `calendar-change`   — the user picked a day. Detail is the bare value.
        //                         Programmatic seeds do NOT fire it, which is what lets
        //                         "close the popover when the range is complete" be
        //                         written without a re-entrancy flag.
        //   `calendar:updated`  — ANY change, including seeds. Detail is { id, mode,
        //                         value, source }, so a listener can tell a pick from a
        //                         seed and can tell which calendar spoke.
        // The names are deliberately NOT near-twins: `calendar:change` would have differed
        // from `calendar-change` by a single character. `calendar:*` is the structured API
        // (in: set / set-range / today / goto / clear — out: updated); `calendar-change` is
        // the historical user-pick event. Both bubble and are composed ($dispatch), so they
        // reach `window` even from a popover teleported into <body>.
        notify(source) {
            const value = this.value;
            this.$dispatch('calendar:updated', { id: this.calendarId, mode: this.mode, value, source });
            if (source === 'select')
                this.$dispatch('calendar-change', value);
        },
        // ---- navigation ----
        get canPrev() {
            if (this.disableNavigation)
                return false;
            if (!this.startMonth)
                return true;
            return addMonths(this.view, -1).getTime()
                >= new Date(this.startMonth.getFullYear(), this.startMonth.getMonth(), 1).getTime();
        },
        get canNext() {
            if (this.disableNavigation)
                return false;
            if (!this.endMonth)
                return true;
            return addMonths(this.view, this.numberOfMonths).getTime()
                <= new Date(this.endMonth.getFullYear(), this.endMonth.getMonth(), 1).getTime();
        },
        prev() {
            if (!this.canPrev)
                return;
            this.view = addMonths(this.view, -1);
            this.focusedDate = new Date(this.focusedDate.getFullYear(), this.focusedDate.getMonth() - 1, this.focusedDate.getDate());
        },
        next() {
            if (!this.canNext)
                return;
            this.view = addMonths(this.view, 1);
            this.focusedDate = new Date(this.focusedDate.getFullYear(), this.focusedDate.getMonth() + 1, this.focusedDate.getDate());
        },
        // ---- grid roving focus + keyboard (APG date-picker dialog grid) ----
        isFocused(d) {
            return sameDay(d, this.focusedDate);
        },
        dayLabel(d) {
            const base = d.toLocaleDateString(this.locale, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
            let label = this.isToday(d) ? this.todayLabel.replace(':date', base) : base;
            if (this.isSelected(d))
                label += `, ${this.selectedLabel}`;
            return label;
        },
        _viewContains(d) {
            const start = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
            const end = new Date(this.view.getFullYear(), this.view.getMonth() + this.numberOfMonths, 0);
            return d.getTime() >= start.getTime() && d.getTime() <= end.getTime();
        },
        _focus(d) {
            this.focusedDate = d;
            if (!this._viewContains(d))
                this.view = new Date(d.getFullYear(), d.getMonth(), 1);
            // Use $root (not $el): when invoked from a day button's @keydown, $el is the
            // button, but $root is always the calendar component element. Defer to a
            // macrotask so the moved focus isn't reverted to the event target.
            const key = ymd(d);
            const root = this.$root;
            setTimeout(() => {
                root.querySelector(`[data-day="${key}"]`)?.focus();
            }, 0);
        },
        moveFocus(days) {
            const d = new Date(this.focusedDate);
            d.setDate(d.getDate() + days);
            this._focus(d);
        },
        moveFocusMonths(n) {
            const d = new Date(this.focusedDate);
            d.setMonth(d.getMonth() + n);
            this._focus(d);
        },
        focusWeekEdge(end) {
            const d = new Date(this.focusedDate);
            const offset = (d.getDay() - this.weekStart + 7) % 7;
            d.setDate(d.getDate() + (end ? 6 - offset : -offset));
            this._focus(d);
        },
        // The grid is laid out with logical properties, so under `dir="rtl"` it renders
        // mirrored: the next day sits to the LEFT of the current one. Arrow keys are visual
        // in the APG grid pattern, so they have to mirror with it — Home/End do not (they
        // are "first/last day of the week", a logical position, not a screen side).
        isRtl() {
            const el = this._rootEl;
            return Boolean(el && el.nodeType === 1 && typeof getComputedStyle === 'function'
                && getComputedStyle(el).direction === 'rtl');
        },
        onDayKeydown(e, d) {
            const k = e.key;
            const step = this.isRtl() ? -1 : 1;
            if (k === 'ArrowLeft')
                this.moveFocus(-step);
            else if (k === 'ArrowRight')
                this.moveFocus(step);
            else if (k === 'ArrowUp')
                this.moveFocus(-7);
            else if (k === 'ArrowDown')
                this.moveFocus(7);
            else if (k === 'Home')
                this.focusWeekEdge(false);
            else if (k === 'End')
                this.focusWeekEdge(true);
            else if (k === 'PageUp')
                this.moveFocusMonths(-1);
            else if (k === 'PageDown')
                this.moveFocusMonths(1);
            else if (k === 'Enter' || k === ' ') {
                this.select(d);
                this.focusedDate = d;
            }
            else
                return;
            e.preventDefault();
        },
        // ---- dropdown caption ----
        get years() {
            const start = this.startMonth ? this.startMonth.getFullYear() : new Date().getFullYear() - 100;
            const end = this.endMonth ? this.endMonth.getFullYear() : new Date().getFullYear() + 10;
            return Array.from({ length: end - start + 1 }, (_, i) => start + i);
        },
        get monthNames() {
            return Array.from({ length: 12 }, (_, i) => new Date(2023, i, 1).toLocaleString(this.locale, { month: 'long' }));
        },
        setMonth(m) {
            this.view = new Date(this.view.getFullYear(), Number(m), 1);
        },
        setYear(y) {
            this.view = new Date(Number(y), this.view.getMonth(), 1);
        },
        // ---- form value helpers ----
        fmt(d) {
            return d ? ymd(d) : '';
        },
        get multipleValue() {
            return this.multiple.map(ymd).join(',');
        },
    };
}
export default {
    name: 'calendar',
    register({ alpine }) {
        alpine.data('hotCalendar', createHotCalendar);
    },
};
//# sourceMappingURL=calendar.js.map