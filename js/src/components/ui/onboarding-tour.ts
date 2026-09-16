import type { HotContext, IslandPlugin } from '../../hot/plugin.js';
import { trapFocus } from './focus-trap.js';

/**
 * hotOnboardingTour — controller for the step-by-step product tour.
 *
 * Owns the step list and cursor, measures the live target element to place
 * the spotlight cutout (a transparent box whose huge box-shadow dims the
 * rest of the page), and positions the coachmark card with viewport-aware
 * flipping: the preferred placement flips to the opposite side when it would
 * overflow, then clamps inside a 12px margin. Steps without a findable
 * target fall back to a centered card over a flat dimming overlay.
 *
 * The card toggles the focus machinery with `x-effect="trap($el, active)"`
 * — a bare Tab loop + initial focus + restore, matching the old plain
 * `x-trap` (no noscroll, no inert). Re-measure hooks (resize/scroll/escape)
 * stay on the template exactly as before.
 */

export type TourPlacement = 'top' | 'bottom' | 'left' | 'right';
export type CardPlacement = TourPlacement | 'center';

/** One tour step, normalised server-side. */
export interface TourStep {
    target?: string | null;
    title?: string;
    body?: string;
    placement?: TourPlacement;
}

/** Target highlight box, viewport coordinates + padding already applied. */
export interface TourRect {
    top: number;
    left: number;
    width: number;
    height: number;
}

/** Resolved card position; `placement` reflects any flip that happened. */
export interface TourCard {
    top: number;
    left: number;
    placement: CardPlacement;
}

export interface OnboardingTourConfig {
    steps?: TourStep[];
    /** Initial state; templates pass `open: <?= js((bool) $open) ?>`. */
    open?: boolean;
}

export interface OnboardingTourController {
    steps: TourStep[];
    index: number;
    active: boolean;
    rect: TourRect;
    card: TourCard;
    /** Spotlight breathing room around the target, px. */
    gap: number;

    get step(): TourStep;
    get count(): number;
    get isFirst(): boolean;
    get isLast(): boolean;
    get hasTarget(): boolean;

    start(): void;
    go(): void;
    measure(el: Element): void;
    recompute(): void;
    focusCard(): void;
    next(): void;
    back(): void;
    end(): void;
    trap(el: HTMLElement, active: boolean): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface TourScope {
    $nextTick(callback: () => void): void;
    $refs: Record<string, HTMLElement>;
}

type Live = OnboardingTourController & TourScope;

const TRAP_OPTIONS = { lockScroll: false, inert: false };
const VIEWPORT_MARGIN = 12;

const NO_RECT: TourRect = { top: 0, left: 0, width: 0, height: 0 };

export function hotOnboardingTour(config?: OnboardingTourConfig): OnboardingTourController {
    return {
        steps: config?.steps ?? [],
        index: 0,
        active: config?.open ?? false,
        rect: { ...NO_RECT },
        card: { top: 0, left: 0, placement: 'center' },
        gap: 12,

        get step(): TourStep {
            return this.steps[this.index] ?? {};
        },
        get count(): number {
            return this.steps.length;
        },
        get isFirst(): boolean {
            return this.index === 0;
        },
        get isLast(): boolean {
            return this.index >= this.count - 1;
        },
        get hasTarget(): boolean {
            return this.rect.width > 0 || this.rect.height > 0;
        },

        start(): void {
            if (!this.count) return;
            this.index = 0;
            this.active = true;
            (this as Live).$nextTick(() => this.go());
        },

        go(): void {
            const sel = this.step.target;
            const el = sel ? document.querySelector(sel) : null;
            if (el) {
                try {
                    el.scrollIntoView({ block: 'center', inline: 'center', behavior: 'smooth' });
                } catch {
                    el.scrollIntoView();
                }
                this.measure(el);
            } else {
                // No target found: center the card and skip the spotlight for this step.
                this.rect = { ...NO_RECT };
            }
            this.recompute();
            (this as Live).$nextTick(() => this.focusCard());
        },

        measure(el: Element): void {
            const r = el.getBoundingClientRect();
            const pad = 6;
            this.rect = {
                top: r.top - pad,
                left: r.left - pad,
                width: r.width + pad * 2,
                height: r.height + pad * 2,
            };
        },

        recompute(): void {
            // Re-measure the live target (rect can drift after scroll/resize) and place the card.
            const scope = this as Live;
            const sel = this.step.target;
            const el = sel ? document.querySelector(sel) : null;
            if (el) this.measure(el);

            const cardEl = scope.$refs['cardEl'];
            const cw = cardEl ? cardEl.offsetWidth : 320;
            const ch = cardEl ? cardEl.offsetHeight : 160;
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            const m = VIEWPORT_MARGIN;

            if (!this.hasTarget) {
                this.card = { top: Math.max(m, (vh - ch) / 2), left: Math.max(m, (vw - cw) / 2), placement: 'center' };

                return;
            }

            const r = this.rect;
            const preferred: TourPlacement = this.step.placement ?? 'bottom';

            const place = (p: TourPlacement): { top: number; left: number } => {
                switch (p) {
                    case 'top': return { top: r.top - ch - this.gap, left: r.left + r.width / 2 - cw / 2 };
                    case 'left': return { top: r.top + r.height / 2 - ch / 2, left: r.left - cw - this.gap };
                    case 'right': return { top: r.top + r.height / 2 - ch / 2, left: r.left + r.width + this.gap };
                    default: return { top: r.top + r.height + this.gap, left: r.left + r.width / 2 - cw / 2 };
                }
            };
            // Flip to the opposite side if the preferred placement overflows the viewport.
            let placement = preferred;
            let p = place(placement);
            if (placement === 'bottom' && p.top + ch > vh - m) {
                placement = 'top';
                p = place('top');
            } else if (placement === 'top' && p.top < m) {
                placement = 'bottom';
                p = place('bottom');
            } else if (placement === 'right' && p.left + cw > vw - m) {
                placement = 'left';
                p = place('left');
            } else if (placement === 'left' && p.left < m) {
                placement = 'right';
                p = place('right');
            }

            const top = Math.min(Math.max(m, p.top), vh - ch - m);
            const left = Math.min(Math.max(m, p.left), vw - cw - m);
            this.card = { top, left, placement };
        },

        focusCard(): void {
            const c = (this as Live).$refs['cardEl'];
            c?.focus({ preventScroll: true });
        },

        next(): void {
            if (this.isLast) this.end();
            else {
                this.index += 1;
                this.go();
            }
        },

        back(): void {
            if (this.isFirst) return;
            this.index -= 1;
            this.go();
        },

        end(): void {
            this.active = false;
        },

        trap(el, active): void {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}

export default {
    name: 'onboarding-tour',
    register({ alpine }: HotContext): void {
        alpine.data('hotOnboardingTour', hotOnboardingTour as never);
    },
} satisfies IslandPlugin;
