export function createHotSignaturePad(config) {
    return {
        pen: config?.pen ?? null,
        height: config?.height ?? 200,
        ctx: null,
        canvas: null,
        drawing: false,
        hasInk: false,
        last: { x: 0, y: 0 },
        strokes: [], // history of completed strokes for Undo (each = array of points)
        current: null,
        _ro: null,
        init() {
            const live = this;
            this.canvas = live.$refs['canvas'];
            this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
            this.resize();
            // Re-fit on container resize so the pad stays crisp & responsive.
            if (window.ResizeObserver && this.canvas) {
                this._ro = new ResizeObserver(() => this.resize());
                this._ro.observe(this.canvas);
            }
        },
        // Resolve the pen colour: explicit prop, else the root's computed text colour.
        penColor() {
            if (this.pen)
                return this.pen;
            return getComputedStyle(this.$root).color || '#000';
        },
        // Size the backing store to devicePixelRatio for sharp lines, then replay history.
        resize() {
            if (!this.canvas || !this.ctx)
                return;
            const dpr = window.devicePixelRatio || 1;
            const rect = this.canvas.getBoundingClientRect();
            if (!rect.width)
                return;
            this.canvas.width = Math.round(rect.width * dpr);
            this.canvas.height = Math.round(rect.height * dpr);
            this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            this.redraw();
        },
        point(e) {
            const rect = this.canvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        },
        start(e) {
            e.preventDefault();
            this.canvas?.setPointerCapture?.(e.pointerId);
            this.drawing = true;
            this.hasInk = true;
            const p = this.point(e);
            this.last = p;
            this.current = [p];
        },
        move(e) {
            if (!this.drawing || !this.canvas)
                return;
            e.preventDefault();
            const p = this.point(e);
            this.stroke(this.last, p);
            this.last = p;
            this.current?.push(p);
        },
        end(_e) {
            if (!this.drawing)
                return;
            this.drawing = false;
            if (this.current && this.current.length)
                this.strokes.push(this.current);
            this.current = null;
            this.sync();
        },
        // Draw one smooth segment between two points.
        stroke(a, b) {
            const c = this.ctx;
            if (!c)
                return;
            c.strokeStyle = this.penColor();
            c.lineWidth = 2;
            c.lineCap = 'round';
            c.lineJoin = 'round';
            c.beginPath();
            c.moveTo(a.x, a.y);
            c.lineTo(b.x, b.y);
            c.stroke();
        },
        // Repaint the whole history (used after resize / undo).
        redraw() {
            const c = this.ctx;
            if (!c || !this.canvas)
                return;
            const rect = this.canvas.getBoundingClientRect();
            c.clearRect(0, 0, rect.width, rect.height);
            for (const s of this.strokes) {
                for (let i = 1; i < s.length; i++)
                    this.stroke(s[i - 1], s[i]);
            }
            this.hasInk = this.strokes.length > 0;
        },
        undo() {
            this.strokes.pop();
            this.redraw();
            this.sync();
        },
        clear() {
            this.strokes = [];
            this.current = null;
            this.drawing = false;
            this.redraw();
            this.sync();
        },
        // Write the current canvas as a data URL into the hidden input (empty when blank).
        sync() {
            const field = this.$refs['field'];
            if (!field || !this.canvas)
                return;
            field.value = this.hasInk ? this.canvas.toDataURL('image/png') : '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
        },
    };
}
export default {
    name: 'signature-pad',
    register({ alpine }) {
        alpine.data('hotSignaturePad', createHotSignaturePad);
    },
};
//# sourceMappingURL=signature-pad.js.map