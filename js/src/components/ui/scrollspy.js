export function hotScrollspy(config) {
    return {
        active: null,
        items: config?.items ?? [],
        observer: null,
        init() {
            const targets = this.items
                .map((item) => {
                try {
                    return document.querySelector(item.href);
                }
                catch {
                    return null;
                }
            })
                .filter((el) => el !== null);
            if (!targets.length)
                return;
            // Default the active section to the first available target.
            this.active = targets[0]?.id ?? null;
            this.observer = new IntersectionObserver((entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting && entry.target.id) {
                        this.active = entry.target.id;
                    }
                }
            }, { rootMargin: '0px 0px -70% 0px', threshold: 0 });
            for (const el of targets)
                this.observer.observe(el);
        },
        destroy() {
            this.observer?.disconnect();
            this.observer = null;
        },
        idFor(href) {
            return (href ?? '').replace(/^#/, '');
        },
    };
}
export default {
    name: 'scrollspy',
    register({ alpine }) {
        alpine.data('hotScrollspy', hotScrollspy);
    },
};
//# sourceMappingURL=scrollspy.js.map