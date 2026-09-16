export function hotCountdown(config) {
    return {
        target: config?.target ?? null,
        now: Date.now(),
        _t: null,
        get diff() {
            return this.target === null ? 0 : Math.max(0, this.target - this.now);
        },
        get done() {
            return this.diff <= 0;
        },
        get days() {
            return Math.floor(this.diff / 86400000);
        },
        get hours() {
            return Math.floor(this.diff / 3600000) % 24;
        },
        get minutes() {
            return Math.floor(this.diff / 60000) % 60;
        },
        get seconds() {
            return Math.floor(this.diff / 1000) % 60;
        },
        pad(n) {
            return String(n).padStart(2, '0');
        },
        init() {
            this._t = window.setInterval(() => {
                this.now = Date.now();
            }, 1000);
        },
        destroy() {
            if (this._t !== null)
                window.clearInterval(this._t);
            this._t = null;
        },
    };
}
export default {
    name: 'countdown',
    register({ alpine }) {
        alpine.data('hotCountdown', hotCountdown);
    },
};
//# sourceMappingURL=countdown.js.map