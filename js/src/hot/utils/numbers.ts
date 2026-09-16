/**
 * Stepping arithmetic shared by number-input, slider, knob and friends.
 *
 * `0.1 + 0.1 + 0.1` is `0.30000000000000004` in IEEE 754, so eight clicks of a
 * `+0.1` stepper starting at 1.1 would drift to 1.8666… Rounding back to the
 * precision the inputs actually carry keeps a stepper on the values the author
 * declared — while still allowing hand-typed off-step values.
 */

/** Decimal places carried by a number, exponent notation (1e-7) included. */
export function decimalsOf(value: number | null | undefined): number {
    if (value === null || value === undefined || !Number.isFinite(value)) return 0;
    const text = String(value);
    const exp = text.indexOf('e-');
    if (exp !== -1) {
        const mantissa = text.slice(0, exp);
        const dot = mantissa.indexOf('.');
        return Number(text.slice(exp + 2)) + (dot === -1 ? 0 : mantissa.length - dot - 1);
    }
    const dot = text.indexOf('.');

    return dot === -1 ? 0 : text.length - dot - 1;
}

/** Round to `decimals` places without binary float drift. */
export function roundTo(value: number, decimals: number): number {
    if (!Number.isFinite(value)) return value;
    const d = Math.min(Math.max(decimals, 0), 15);
    const text = String(value);
    // Shift the decimal point through exponent notation: `1.005 * 100` is
    // 100.49999999999999, so multiplying would round 1.005 DOWN to 1.
    if (text.includes('e') || text.includes('E')) {
        const p = 10 ** d;

        return Math.round(value * p) / p;
    }

    return Number(`${Math.round(Number(`${text}e${d}`))}e-${d}`);
}

/** `current + delta`, kept at the precision current and step imply. */
export function stepBy(current: number | string, delta: number, step: number | string): number {
    const from = Number(current) || 0;

    return roundTo(from + delta, Math.max(decimalsOf(Number(step)), decimalsOf(from)));
}

/** Nearest multiple of `step` counted from `origin`, at the step's precision. */
export function snapTo(raw: number, step: number, origin: number = 0): number {
    if (!Number.isFinite(raw) || !step) return raw;

    return roundTo(
        Math.round((raw - origin) / step) * step + origin,
        decimalsOf(step) + decimalsOf(origin),
    );
}

/** The `$hot.number` capability surface. */
export const numberToolkit = {
    decimals: decimalsOf,
    round: roundTo,
    step: stepBy,
    snap: snapTo,
} as const;
