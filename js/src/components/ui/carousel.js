const SWIPE_THRESHOLD = 40;
export function hotCarousel(config) {
    return {
        index: 0,
        count: 0,
        orientation: config?.orientation === 'vertical' ? 'vertical' : 'horizontal',
        swipe: config?.swipe ?? true,
        drag: { active: false, start: 0 },
        init() {
            const track = this.$refs['track'];
            this.count = track ? track.children.length : 0;
        },
        get canPrev() {
            return this.index > 0;
        },
        get canNext() {
            return this.index < this.count - 1;
        },
        prev() {
            if (this.canPrev)
                this.index -= 1;
        },
        next() {
            if (this.canNext)
                this.index += 1;
        },
        onPointerDown(e) {
            if (!this.swipe || e.pointerType === 'mouse')
                return;
            this.drag.active = true;
            this.drag.start = this.orientation === 'vertical' ? e.clientY : e.clientX;
        },
        onPointerUp(e) {
            if (!this.drag.active)
                return;
            this.drag.active = false;
            const end = this.orientation === 'vertical' ? e.clientY : e.clientX;
            const d = end - this.drag.start;
            if (d <= -SWIPE_THRESHOLD)
                this.next();
            else if (d >= SWIPE_THRESHOLD)
                this.prev();
        },
    };
}
export default {
    name: 'carousel',
    register({ alpine }) {
        alpine.data('hotCarousel', hotCarousel);
    },
};
//# sourceMappingURL=carousel.js.map