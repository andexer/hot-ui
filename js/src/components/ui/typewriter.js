const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';
export function hotTypewriter(config) {
    const words = config?.words ?? [];
    const typeSpeed = config?.typeSpeed ?? 90;
    const deleteSpeed = config?.deleteSpeed ?? 40;
    const pause = config?.pause ?? 1600;
    const loop = config?.loop ?? true;
    return {
        words,
        i: 0,
        out: '',
        del: false,
        done: false,
        _t: null,
        init() {
            if (window.matchMedia(REDUCED_MOTION).matches || this.words.length === 0) {
                this.out = this.words[0] ?? '';
                this.done = true;
                return;
            }
            this.tick();
        },
        destroy() {
            if (this._t !== null)
                window.clearTimeout(this._t);
            this._t = null;
        },
        tick() {
            if (this.words.length === 0)
                return;
            const word = this.words[this.i % this.words.length] ?? '';
            if (!this.del) {
                this.out = word.slice(0, this.out.length + 1);
                if (this.out === word) {
                    if (!loop && this.i === this.words.length - 1) {
                        this.done = true;
                        return;
                    }
                    this.del = true;
                    this._t = window.setTimeout(() => this.tick(), pause);
                    return;
                }
                this._t = window.setTimeout(() => this.tick(), typeSpeed);
            }
            else {
                this.out = word.slice(0, this.out.length - 1);
                if (this.out === '') {
                    this.del = false;
                    this.i += 1;
                }
                this._t = window.setTimeout(() => this.tick(), deleteSpeed);
            }
        },
    };
}
export default {
    name: 'typewriter',
    register({ alpine }) {
        alpine.data('hotTypewriter', hotTypewriter);
    },
};
//# sourceMappingURL=typewriter.js.map