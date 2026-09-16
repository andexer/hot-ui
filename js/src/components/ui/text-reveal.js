const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';
export function hotTextReveal(config) {
    return {
        // Server-rendered default is fully revealed; init() dims until scrolled
        // unless reduced motion wins.
        total: Math.max(0, config?.total ?? 0),
        revealed: Math.max(0, config?.total ?? 0),
        _onMove: null,
        apply() {
            const scope = this;
            scope.$el.querySelectorAll('[data-w]').forEach((el, i) => {
                el.style.opacity = i < this.revealed ? '1' : '0.2';
            });
        },
        update() {
            const scope = this;
            const r = scope.$el.getBoundingClientRect();
            const vh = window.innerHeight || document.documentElement.clientHeight;
            let p = (vh - r.top) / (vh - vh * 0.3);
            p = Math.max(0, Math.min(1, p));
            this.revealed = Math.round(p * this.total);
            this.apply();
        },
        init() {
            if (window.matchMedia(REDUCED_MOTION).matches)
                return;
            this.revealed = 0;
            this.apply();
            this._onMove = () => {
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
    register({ alpine }) {
        alpine.data('hotTextReveal', hotTextReveal);
    },
};
//# sourceMappingURL=text-reveal.js.map