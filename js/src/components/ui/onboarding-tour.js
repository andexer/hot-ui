import { trapFocus } from './focus-trap.js';
const TRAP_OPTIONS = { lockScroll: false, inert: false };
const VIEWPORT_MARGIN = 12;
const NO_RECT = { top: 0, left: 0, width: 0, height: 0 };
export function hotOnboardingTour(config) {
    return {
        steps: config?.steps ?? [],
        index: 0,
        active: config?.open ?? false,
        rect: { ...NO_RECT },
        card: { top: 0, left: 0, placement: 'center' },
        gap: 12,
        get step() {
            return this.steps[this.index] ?? {};
        },
        get count() {
            return this.steps.length;
        },
        get isFirst() {
            return this.index === 0;
        },
        get isLast() {
            return this.index >= this.count - 1;
        },
        get hasTarget() {
            return this.rect.width > 0 || this.rect.height > 0;
        },
        start() {
            if (!this.count)
                return;
            this.index = 0;
            this.active = true;
            this.$nextTick(() => this.go());
        },
        go() {
            const sel = this.step.target;
            const el = sel ? document.querySelector(sel) : null;
            if (el) {
                try {
                    el.scrollIntoView({ block: 'center', inline: 'center', behavior: 'smooth' });
                }
                catch {
                    el.scrollIntoView();
                }
                this.measure(el);
            }
            else {
                // No target found: center the card and skip the spotlight for this step.
                this.rect = { ...NO_RECT };
            }
            this.recompute();
            this.$nextTick(() => this.focusCard());
        },
        measure(el) {
            const r = el.getBoundingClientRect();
            const pad = 6;
            this.rect = {
                top: r.top - pad,
                left: r.left - pad,
                width: r.width + pad * 2,
                height: r.height + pad * 2,
            };
        },
        recompute() {
            // Re-measure the live target (rect can drift after scroll/resize) and place the card.
            const scope = this;
            const sel = this.step.target;
            const el = sel ? document.querySelector(sel) : null;
            if (el)
                this.measure(el);
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
            const preferred = this.step.placement ?? 'bottom';
            const place = (p) => {
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
            }
            else if (placement === 'top' && p.top < m) {
                placement = 'bottom';
                p = place('bottom');
            }
            else if (placement === 'right' && p.left + cw > vw - m) {
                placement = 'left';
                p = place('left');
            }
            else if (placement === 'left' && p.left < m) {
                placement = 'right';
                p = place('right');
            }
            const top = Math.min(Math.max(m, p.top), vh - ch - m);
            const left = Math.min(Math.max(m, p.left), vw - cw - m);
            this.card = { top, left, placement };
        },
        focusCard() {
            const c = this.$refs['cardEl'];
            c?.focus({ preventScroll: true });
        },
        next() {
            if (this.isLast)
                this.end();
            else {
                this.index += 1;
                this.go();
            }
        },
        back() {
            if (this.isFirst)
                return;
            this.index -= 1;
            this.go();
        },
        end() {
            this.active = false;
        },
        trap(el, active) {
            trapFocus(el, active, TRAP_OPTIONS);
        },
    };
}
export default {
    name: 'onboarding-tour',
    register({ alpine }) {
        alpine.data('hotOnboardingTour', hotOnboardingTour);
    },
};
//# sourceMappingURL=onboarding-tour.js.map