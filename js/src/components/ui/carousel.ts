import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotCarousel — controller for the carousel family.
 *
 * Owns the slide `index` and the swipe machinery: pointer tracking on the
 * content panel (touch only — mouse drags stay click-through), a 40px
 * threshold mapped to prev()/next(), and the count seeded from the track's
 * children. Arrow-key navigation stays on the root template; buttons and
 * slides read state through scope inheritance.
 */

export type CarouselOrientation = 'horizontal' | 'vertical';

/** Pointer-drag bookkeeping — plain values, no DOM references. */
interface DragState {
    active: boolean;
    start: number;
}

export interface CarouselConfig {
    /** Axis of the track; also flips touch-action and button placement classes. */
    orientation?: CarouselOrientation;
    /** Touch-swipe navigation (mouse is never treated as a swipe). */
    swipe?: boolean;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface CarouselScope {
    $refs: Record<string, HTMLElement>;
}

type Live = CarouselController & CarouselScope;

export interface CarouselController {
    index: number;
    count: number;
    orientation: CarouselOrientation;
    swipe: boolean;
    drag: DragState;

    init(): void;
    get canPrev(): boolean;
    get canNext(): boolean;
    prev(): void;
    next(): void;
    onPointerDown(e: PointerEvent): void;
    onPointerUp(e: PointerEvent): void;
}

const SWIPE_THRESHOLD = 40;

export function hotCarousel(config?: CarouselConfig): CarouselController {
    return {
        index: 0,
        count: 0,
        orientation: config?.orientation === 'vertical' ? 'vertical' : 'horizontal',
        swipe: config?.swipe ?? true,
        drag: { active: false, start: 0 },

        init() {
            const track = (this as Live).$refs['track'];
            this.count = track ? track.children.length : 0;
        },

        get canPrev(): boolean {
            return this.index > 0;
        },
        get canNext(): boolean {
            return this.index < this.count - 1;
        },
        prev(): void {
            if (this.canPrev) this.index -= 1;
        },
        next(): void {
            if (this.canNext) this.index += 1;
        },

        onPointerDown(e: PointerEvent): void {
            if (!this.swipe || e.pointerType === 'mouse') return;
            this.drag.active = true;
            this.drag.start = this.orientation === 'vertical' ? e.clientY : e.clientX;
        },

        onPointerUp(e: PointerEvent): void {
            if (!this.drag.active) return;
            this.drag.active = false;
            const end = this.orientation === 'vertical' ? e.clientY : e.clientX;
            const d = end - this.drag.start;
            if (d <= -SWIPE_THRESHOLD) this.next();
            else if (d >= SWIPE_THRESHOLD) this.prev();
        },
    };
}

export default {
    name: 'carousel',
    register({ alpine }: HotContext): void {
        alpine.data('hotCarousel', hotCarousel as never);
    },
} satisfies IslandPlugin;
