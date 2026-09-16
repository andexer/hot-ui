const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';
export function hotNumberTicker(config) {
    return {
        value: config?.value ?? 0,
        from: config?.from ?? 0,
        duration: Math.max(0, config?.duration ?? 1500),
        decimals: Math.max(0, config?.decimals ?? 0),
        separator: config?.separator ?? ',',
        current: config?.from ?? 0,
        started: false,
        raf: null,
        format(n) {
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
            const io = new IntersectionObserver((entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting && !this.started) {
                        this.run();
                        io.disconnect();
                    }
                }
            }, { threshold: 0.2 });
            io.observe(this.$el);
        },
        destroy() {
            if (this.raf !== null)
                cancelAnimationFrame(this.raf);
            this.raf = null;
        },
        run() {
            this.started = true;
            const start = performance.now();
            const delta = this.value - this.from;
            const tick = (now) => {
                const t = this.duration === 0 ? 1 : Math.min(1, (now - start) / this.duration);
                const eased = 1 - (1 - t) ** 3;
                this.current = this.from + delta * eased;
                if (t < 1) {
                    this.raf = requestAnimationFrame(tick);
                }
                else {
                    this.current = this.value;
                }
            };
            this.raf = requestAnimationFrame(tick);
        },
    };
}
export default {
    name: 'number-ticker',
    register({ alpine }) {
        alpine.data('hotNumberTicker', hotNumberTicker);
    },
};
//# sourceMappingURL=number-ticker.js.map