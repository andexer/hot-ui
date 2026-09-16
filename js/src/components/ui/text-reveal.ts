import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotTextReveal — controller for the scroll-linked word reveal.
 *
 * Words render server-side at full opacity (no-JS/crawlers get everything);
 * with JS they start dimmed and brighten one by one as the element travels
 * up through the viewport. The scroll/resize listeners are rAF-throttled and
 * passive; reduced motion leaves the text fully revealed and installs
 * nothing. Opacity only — the text stays readable by assistive tech.
 */

export interface TextRevealConfig {
    /** Number of `[data-w]` word spans rendered by the template. */
    total?: number;
}

export interface TextRevealController {
    total: number;
    revealed: number;
    /** Shared rAF-throttled listener installed in init(), removed in destroy(). */
    _onMove: (() => void) | null;

    apply(): void;
    update(): void;
    init(): void;
    destroy(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface TextRevealScope {
    $el: HTMLElement;
}

type Live = TextRevealController & TextRevealScope;

const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';

export function hotTextReveal(config?: TextRevealConfig): TextRevealController {
    return {
        // Server-rendered default is fully revealed; init() dims until scrolled
        // unless reduced motion wins.
        total: Math.max(0, config?.total ?? 0),
        revealed: Math.max(0, config?.total ?? 0),
        _onMove: null,

        apply(): void {
            const scope = this as Live;
            scope.$el.querySelectorAll<HTMLElement>('[data-w]').forEach((el, i) => {
                el.style.opacity = i < this.revealed ? '1' : '0.2';
            });
        },

        update() {
            const scope = this as Live;
            const r = scope.$el.getBoundingClientRect();
            const vh = window.innerHeight || document.documentElement.clientHeight;
            let p = (vh - r.top) / (vh - vh * 0.3);
            p = Math.max(0, Math.min(1, p));
            this.revealed = Math.round(p * this.total);
            this.apply();
        },

        init() {
            if (window.matchMedia(REDUCED_MOTION).matches) return;
            this.revealed = 0;
            this.apply();
            this._onMove = (): void => {
                window.requestAnimationFrame(() => this.update());
            };
            window.addEventListener('scroll', this._onMove, { passive: true });
            window.addEventListener('resize', this._onMove, { passive: true });
            this.update();
        },

        destroy() {
            if (this._onMove) {
                window.removeEventListener('scroll', this._onMove);
                window.removeEventListener('resize', this._onMove);
                this._onMove = null;
            }
        },
    };
}

export default {
    name: 'text-reveal',
    register({ alpine }: HotContext): void {
        alpine.data('hotTextReveal', hotTextReveal as never);
    },
} satisfies IslandPlugin;
