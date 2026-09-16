import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotCountdown — controller for the ticking countdown.
 *
 * The target is resolved to a millisecond epoch server-side (timezone-safe);
 * the controller holds `now` on a 1s interval and derives the remaining
 * days/hours/minutes/seconds as getters. The expired slot and the unit grid
 * read `done` and the padded units through scope inheritance.
 */

export interface CountdownConfig {
    /** Target epoch milliseconds; null renders as already expired. */
    target?: number | null;
}

export interface CountdownController {
    target: number | null;
    now: number;
    _t: number | null;

    get diff(): number;
    get done(): boolean;
    get days(): number;
    get hours(): number;
    get minutes(): number;
    get seconds(): number;
    pad(n: number): string;
    init(): void;
    destroy(): void;
}

export function hotCountdown(config?: CountdownConfig): CountdownController {
    return {
        target: config?.target ?? null,
        now: Date.now(),
        _t: null,

        get diff(): number {
            return this.target === null ? 0 : Math.max(0, this.target - this.now);
        },
        get done(): boolean {
            return this.diff <= 0;
        },
        get days(): number {
            return Math.floor(this.diff / 86400000);
        },
        get hours(): number {
            return Math.floor(this.diff / 3600000) % 24;
        },
        get minutes(): number {
            return Math.floor(this.diff / 60000) % 60;
        },
        get seconds(): number {
            return Math.floor(this.diff / 1000) % 60;
        },

        pad(n: number): string {
            return String(n).padStart(2, '0');
        },

        init() {
            this._t = window.setInterval(() => {
                this.now = Date.now();
            }, 1000);
        },

        destroy() {
            if (this._t !== null) window.clearInterval(this._t);
            this._t = null;
        },
    };
}

export default {
    name: 'countdown',
    register({ alpine }: HotContext): void {
        alpine.data('hotCountdown', hotCountdown as never);
    },
} satisfies IslandPlugin;
