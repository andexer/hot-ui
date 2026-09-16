const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';
export function hotStreamingText(config) {
    return {
        full: config?.full ?? '',
        by: config?.by === 'word' ? 'word' : 'char',
        speed: Math.max(0, config?.speed ?? 18),
        startDelay: Math.max(0, config?.startDelay ?? 0),
        out: '',
        done: false,
        started: false,
        timer: null,
        units: [],
        idx: 0,
        init() {
            if (window.matchMedia(REDUCED_MOTION).matches || this.full === '') {
                this.out = this.full;
                this.done = true;
                return;
            }
            this.units = this.by === 'word'
                ? (this.full.match(/\s*\S+/g) ?? [])
                : Array.from(this.full);
            if (config?.autostart ?? true) {
                this.timer = window.setTimeout(() => this.start(), this.startDelay);
            }
        },
        destroy() {
            if (this.timer !== null)
                window.clearTimeout(this.timer);
            this.timer = null;
        },
        start() {
            if (this.started || this.done)
                return;
            this.started = true;
            this.step();
        },
        step() {
            if (this.idx >= this.units.length) {
                this.finish();
                return;
            }
            const unit = this.units[this.idx];
            this.idx += 1;
            this.out += unit ?? '';
            this.timer = window.setTimeout(() => this.step(), this.speed);
        },
        finish() {
            this.out = this.full;
            this.done = true;
        },
    };
}
export default {
    name: 'streaming-text',
    register({ alpine }) {
        alpine.data('hotStreamingText', hotStreamingText);
    },
};
//# sourceMappingURL=streaming-text.js.map