export function hotInfiniteScroll(config) {
    return {
        loading: false,
        finished: false,
        threshold: Math.max(0, config?.threshold ?? 200),
        observer: null,
        init() {
            if (typeof IntersectionObserver === 'undefined')
                return; // button fallback still works
            const scope = this;
            const sentinel = scope.$refs['sentinel'];
            if (!sentinel)
                return;
            const root = this.scrollParent(sentinel);
            this.observer = new IntersectionObserver((entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting)
                        this.loadMore();
                }
            }, { root, rootMargin: `0px 0px ${this.threshold}px 0px`, threshold: 0 });
            this.observer.observe(sentinel);
        },
        // Nearest vertically-scrollable ancestor, or null (= viewport) if none.
        scrollParent(el) {
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
            if (this.loading || this.finished)
                return;
            this.loading = true;
            const event = new CustomEvent('load-more', {
                bubbles: true,
                cancelable: true,
                detail: { done: () => this.finish(), loaded: () => this.loaded() },
            });
            this.$el.dispatchEvent(event);
        },
        // Clear the loading state after a batch has been appended.
        loaded() {
            this.loading = false;
        },
        // Mark the list complete: hides the sentinel + button, shows the end message.
        finish() {
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
    register({ alpine }) {
        alpine.data('hotInfiniteScroll', hotInfiniteScroll);
    },
};
//# sourceMappingURL=infinite-scroll.js.map