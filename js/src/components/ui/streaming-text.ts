import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotStreamingText — controller for the LLM-style one-shot text reveal.
 *
 * Splits the full passage into chunks (characters or whitespace-preserving
 * words) and appends one per tick after an optional start delay. Reduced
 * motion (or empty text) renders everything immediately. The template keeps
 * the complete passage in an aria-live region up front; `start()` also lets
 * a parent trigger the stream manually when autostart is off.
 */

export type StreamingGranularity = 'char' | 'word';

export interface StreamingTextConfig {
    /** The full passage to stream. */
    full?: string;
    /** Reveal granularity: 'char' or 'word'. */
    by?: StreamingGranularity;
    /** ms per revealed chunk. */
    speed?: number;
    /** ms to wait before the first chunk appears. */
    startDelay?: number;
    /** Begin streaming on init. */
    autostart?: boolean;
}

export interface StreamingTextController {
    full: string;
    by: StreamingGranularity;
    speed: number;
    startDelay: number;
    out: string;
    done: boolean;
    started: boolean;
    timer: number | null;
    units: string[];
    idx: number;

    init(): void;
    destroy(): void;
    start(): void;
    step(): void;
    finish(): void;
}

const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';

export function hotStreamingText(config?: StreamingTextConfig): StreamingTextController {
    return {
        full: config?.full ?? '',
        by: config?.by === 'word' ? 'word' : 'char',
        speed: Math.max(0, config?.speed ?? 18),
        startDelay: Math.max(0, config?.startDelay ?? 0),
        out: '',
        done: false,
        started: false,
        timer: null,
        units: [],
        idx: 0,

        init() {
            if (window.matchMedia(REDUCED_MOTION).matches || this.full === '') {
                this.out = this.full;
                this.done = true;

                return;
            }
            this.units = this.by === 'word'
                ? (this.full.match(/\s*\S+/g) ?? [])
                : Array.from(this.full);
            if (config?.autostart ?? true) {
                this.timer = window.setTimeout(() => this.start(), this.startDelay);
            }
        },

        destroy() {
            if (this.timer !== null) window.clearTimeout(this.timer);
            this.timer = null;
        },

        start() {
            if (this.started || this.done) return;
            this.started = true;
            this.step();
        },

        step() {
            if (this.idx >= this.units.length) {
                this.finish();

                return;
            }
            const unit = this.units[this.idx];
            this.idx += 1;
            this.out += unit ?? '';
            this.timer = window.setTimeout(() => this.step(), this.speed);
        },

        finish() {
            this.out = this.full;
            this.done = true;
        },
    };
}

export default {
    name: 'streaming-text',
    register({ alpine }: HotContext): void {
        alpine.data('hotStreamingText', hotStreamingText as never);
    },
} satisfies IslandPlugin;
