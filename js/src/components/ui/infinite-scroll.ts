import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotInfiniteScroll — controller for the sentinel-based infinite list.
 *
 * An IntersectionObserver watches the sentinel; its scroll root is auto-
 * detected by walking up to the nearest vertically-scrollable ancestor, so
 * the same markup works in the viewport or inside an overflow box. When the
 * sentinel enters, `load-more` fires (cancelable) with `done()`/`loaded()`
 * callbacks in the detail: the app appends rows and calls one of them — or
 * dispatches `load-more-done` with `{ done }` on the root, which the
 * template already routes to the same two methods.
 */

export interface InfiniteScrollConfig {
    /** px before the edge at which loading starts; mapped to rootMargin. */
    threshold?: number;
}

/** Detail carried by every outgoing `load-more` event. */
export interface LoadMoreDetail {
    /** Mark the list complete: hides sentinel + button, shows the end message. */
    done(): void;
    /** Clear the loading state after a batch has been appended. */
    loaded(): void;
}

export interface InfiniteScrollController {
    loading: boolean;
    finished: boolean;
    threshold: number;
    observer: IntersectionObserver | null;

    init(): void;
    destroy(): void;
    scrollParent(el: Element | null): Element | null;
    loadMore(): void;
    loaded(): void;
    finish(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface InfiniteScrollScope {
    $el: HTMLElement;
    $refs: Record<string, HTMLElement>;
}

type Live = InfiniteScrollController & InfiniteScrollScope;

export function hotInfiniteScroll(config?: InfiniteScrollConfig): InfiniteScrollController {
    return {
        loading: false,
        finished: false,
        threshold: Math.max(0, config?.threshold ?? 200),
        observer: null,

        init() {
            if (typeof IntersectionObserver === 'undefined') return; // button fallback still works

            const scope = this as Live;
            const sentinel = scope.$refs['sentinel'];
            if (!sentinel) return;

            const root = this.scrollParent(sentinel);
            this.observer = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting) this.loadMore();
                    }
                },
                { root, rootMargin: `0px 0px ${this.threshold}px 0px`, threshold: 0 },
            );
            this.observer.observe(sentinel);
        },

        // Nearest vertically-scrollable ancestor, or null (= viewport) if none.
        scrollParent(el): Element | null {
            let node = el?.parentElement ?? null;
            while (node) {
                const oy = getComputedStyle(node).overflowY;
                if ((oy === 'auto' || oy === 'scroll') && node.scrollHeight > node.clientHeight) {
                    return node;
                }
                node = node.parentElement;
            }

            return null;
        },

        loadMore() {
            if (this.loading || this.finished) return;
            this.loading = true;

            const event = new CustomEvent<LoadMoreDetail>('load-more', {
                bubbles: true,
                cancelable: true,
                detail: { done: () => this.finish(), loaded: () => this.loaded() },
            });
            (this as Live).$el.dispatchEvent(event);
        },

        // Clear the loading state after a batch has been appended.
        loaded(): void {
            this.loading = false;
        },

        // Mark the list complete: hides the sentinel + button, shows the end message.
        finish(): void {
            this.loading = false;
            this.finished = true;
        },

        destroy() {
            this.observer?.disconnect();
            this.observer = null;
        },
    };
}

export default {
    name: 'infinite-scroll',
    register({ alpine }: HotContext): void {
        alpine.data('hotInfiniteScroll', hotInfiniteScroll as never);
    },
} satisfies IslandPlugin;
