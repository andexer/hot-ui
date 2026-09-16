import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotTypewriter — controller for the cycling typewriter headline.
 *
 * Types the current word one character per tick, holds it for `pause`, then
 * deletes it and moves on — forever when `loop`, otherwise stopping on the
 * last word. Reduced motion (or an empty word list) renders the first word
 * statically; the template keeps the full list in a visually-hidden span for
 * assistive tech.
 */

export interface TypewriterConfig {
    words?: string[];
    /** ms per typed character. */
    typeSpeed?: number;
    /** ms per deleted character. */
    deleteSpeed?: number;
    /** ms to hold a fully-typed word before deleting. */
    pause?: number;
    /** Cycle forever; when false, stop on the last word. */
    loop?: boolean;
}

export interface TypewriterController {
    words: string[];
    i: number;
    out: string;
    del: boolean;
    done: boolean;
    _t: number | null;

    init(): void;
    destroy(): void;
    tick(): void;
}

const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';

export function hotTypewriter(config?: TypewriterConfig): TypewriterController {
    const words = config?.words ?? [];
    const typeSpeed = config?.typeSpeed ?? 90;
    const deleteSpeed = config?.deleteSpeed ?? 40;
    const pause = config?.pause ?? 1600;
    const loop = config?.loop ?? true;

    return {
        words,
        i: 0,
        out: '',
        del: false,
        done: false,
        _t: null,

        init() {
            if (window.matchMedia(REDUCED_MOTION).matches || this.words.length === 0) {
                this.out = this.words[0] ?? '';
                this.done = true;

                return;
            }
            this.tick();
        },

        destroy() {
            if (this._t !== null) window.clearTimeout(this._t);
            this._t = null;
        },

        tick() {
            if (this.words.length === 0) return;
            const word = this.words[this.i % this.words.length] ?? '';
            if (!this.del) {
                this.out = word.slice(0, this.out.length + 1);
                if (this.out === word) {
                    if (!loop && this.i === this.words.length - 1) {
                        this.done = true;

                        return;
                    }
                    this.del = true;
                    this._t = window.setTimeout(() => this.tick(), pause);

                    return;
                }
                this._t = window.setTimeout(() => this.tick(), typeSpeed);
            } else {
                this.out = word.slice(0, this.out.length - 1);
                if (this.out === '') {
                    this.del = false;
                    this.i += 1;
                }
                this._t = window.setTimeout(() => this.tick(), deleteSpeed);
            }
        },
    };
}

export default {
    name: 'typewriter',
    register({ alpine }: HotContext): void {
        alpine.data('hotTypewriter', hotTypewriter as never);
    },
} satisfies IslandPlugin;
