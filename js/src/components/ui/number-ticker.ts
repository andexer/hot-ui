import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotNumberTicker — controller for the eased count-up number.
 *
 * Waits until the element scrolls into view (IntersectionObserver at 20%
 * visibility; immediate run when IO is unavailable), then animates `current`
 * from `from` to `value` over `duration` ms with a cubic ease-out on
 * requestAnimationFrame. Duration 0 (or reduced motion) snaps straight to
 * the final value. The template renders the final formatted value in an
 * sr-only span for assistive tech and no-JS.
 */

export interface NumberTickerConfig {
    /** Final value. */
    value?: number;
    /** Value to animate from. */
    from?: number;
    /** Animation length in ms; 0 snaps immediately. */
    duration?: number;
    /** Fixed decimal places. */
    decimals?: number;
    /** Thousands separator character. */
    separator?: string;
}

export interface NumberTickerController {
    value: number;
    from: number;
    duration: number;
    decimals: number;
    separator: string;
    current: number;
    started: boolean;
    raf: number | null;

    format(n: number): string;
    init(): void;
    destroy(): void;
    run(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface NumberTickerScope {
    $el: HTMLElement;
}

type Live = NumberTickerController & NumberTickerScope;

const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';

export function hotNumberTicker(config?: NumberTickerConfig): NumberTickerController {
    return {
        value: config?.value ?? 0,
        from: config?.from ?? 0,
        duration: Math.max(0, config?.duration ?? 1500),
        decimals: Math.max(0, config?.decimals ?? 0),
        separator: config?.separator ?? ',',
        current: config?.from ?? 0,
        started: false,
        raf: null,

        format(n: number): string {
            const parts = n.toFixed(this.decimals).split('.');
            const head = parts[0] ?? '0';
            parts[0] = head.replace(/\B(?=(\d{3})+(?!\d))/g, this.separator);

            return parts.join('.');
        },

        init() {
            if (window.matchMedia(REDUCED_MOTION).matches || this.duration === 0) {
                this.current = this.value;
                this.started = true;

                return;
            }
            if (typeof IntersectionObserver === 'undefined') {
                this.run();

                return;
            }
            const io = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting && !this.started) {
                            this.run();
                            io.disconnect();
                        }
                    }
                },
                { threshold: 0.2 },
            );
            io.observe((this as Live).$el);
        },

        destroy() {
            if (this.raf !== null) cancelAnimationFrame(this.raf);
            this.raf = null;
        },

        run() {
            this.started = true;
            const start = performance.now();
            const delta = this.value - this.from;
            const tick = (now: number): void => {
                const t = this.duration === 0 ? 1 : Math.min(1, (now - start) / this.duration);
                const eased = 1 - (1 - t) ** 3;
                this.current = this.from + delta * eased;
                if (t < 1) {
                    this.raf = requestAnimationFrame(tick);
                } else {
                    this.current = this.value;
                }
            };
            this.raf = requestAnimationFrame(tick);
        },
    };
}

export default {
    name: 'number-ticker',
    register({ alpine }: HotContext): void {
        alpine.data('hotNumberTicker', hotNumberTicker as never);
    },
} satisfies IslandPlugin;
