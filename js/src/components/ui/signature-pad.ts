import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotSignaturePad — controller for the signature-pad.
 *
 * All behaviour extracted verbatim from the template's inline x-data: pointer
 * drawing onto a DPR-scaled canvas, stroke history with undo/clear, and a
 * hidden data-URL input kept in step on every change.
 */

/** One sampled pointer position in CSS px, relative to the canvas. */
export interface SignaturePoint {
    x: number;
    y: number;
}

export interface SignaturePadConfig {
    /** Explicit ink colour; when absent the root's computed text colour is used. */
    pen?: string | null;
    height?: number;
}

export interface HotSignaturePadController {
    pen: string | null;
    height: number;
    ctx: CanvasRenderingContext2D | null;
    canvas: HTMLCanvasElement | null;
    drawing: boolean;
    hasInk: boolean;
    last: SignaturePoint;
    strokes: SignaturePoint[][];
    current: SignaturePoint[] | null;
    _ro: ResizeObserver | null;

    init(): void;
    penColor(): string;
    resize(): void;
    point(e: PointerEvent): SignaturePoint;
    start(e: PointerEvent): void;
    move(e: PointerEvent): void;
    end(_e: PointerEvent): void;
    stroke(a: SignaturePoint, b: SignaturePoint): void;
    redraw(): void;
    undo(): void;
    clear(): void;
    sync(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface SignaturePadScope {
    $refs: Record<string, HTMLElement>;
    $root: HTMLElement;
}

type Live = HotSignaturePadController & SignaturePadScope;

export function createHotSignaturePad(config?: SignaturePadConfig): HotSignaturePadController {
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
            const live = this as Live;
            this.canvas = live.$refs['canvas'] as HTMLCanvasElement;
            this.ctx = this.canvas ? this.canvas.getContext('2d') : null;
            this.resize();
            // Re-fit on container resize so the pad stays crisp & responsive.
            if (window.ResizeObserver && this.canvas) {
                this._ro = new ResizeObserver(() => this.resize());
                this._ro.observe(this.canvas);
            }
        },

        // Resolve the pen colour: explicit prop, else the root's computed text colour.
        penColor(): string {
            if (this.pen) return this.pen;

            return getComputedStyle((this as Live).$root).color || '#000';
        },

        // Size the backing store to devicePixelRatio for sharp lines, then replay history.
        resize(): void {
            if (!this.canvas || !this.ctx) return;
            const dpr = window.devicePixelRatio || 1;
            const rect = this.canvas.getBoundingClientRect();
            if (!rect.width) return;
            this.canvas.width = Math.round(rect.width * dpr);
            this.canvas.height = Math.round(rect.height * dpr);
            this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            this.redraw();
        },

        point(e: PointerEvent): SignaturePoint {
            const rect = (this.canvas as HTMLCanvasElement).getBoundingClientRect();

            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        },

        start(e: PointerEvent): void {
            e.preventDefault();
            this.canvas?.setPointerCapture?.(e.pointerId);
            this.drawing = true;
            this.hasInk = true;
            const p = this.point(e);
            this.last = p;
            this.current = [p];
        },

        move(e: PointerEvent): void {
            if (!this.drawing || !this.canvas) return;
            e.preventDefault();
            const p = this.point(e);
            this.stroke(this.last, p);
            this.last = p;
            this.current?.push(p);
        },

        end(_e: PointerEvent): void {
            if (!this.drawing) return;
            this.drawing = false;
            if (this.current && this.current.length) this.strokes.push(this.current);
            this.current = null;
            this.sync();
        },

        // Draw one smooth segment between two points.
        stroke(a: SignaturePoint, b: SignaturePoint): void {
            const c = this.ctx;
            if (!c) return;
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
        redraw(): void {
            const c = this.ctx;
            if (!c || !this.canvas) return;
            const rect = this.canvas.getBoundingClientRect();
            c.clearRect(0, 0, rect.width, rect.height);
            for (const s of this.strokes) {
                for (let i = 1; i < s.length; i++) this.stroke(s[i - 1]!, s[i]!);
            }
            this.hasInk = this.strokes.length > 0;
        },

        undo(): void {
            this.strokes.pop();
            this.redraw();
            this.sync();
        },

        clear(): void {
            this.strokes = [];
            this.current = null;
            this.drawing = false;
            this.redraw();
            this.sync();
        },

        // Write the current canvas as a data URL into the hidden input (empty when blank).
        sync(): void {
            const field = (this as Live).$refs['field'] as HTMLInputElement | undefined;
            if (!field || !this.canvas) return;
            field.value = this.hasInk ? this.canvas.toDataURL('image/png') : '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
        },
    };
}

export default {
    name: 'signature-pad',
    register({ alpine }: HotContext): void {
        alpine.data('hotSignaturePad', createHotSignaturePad as never);
    },
} satisfies IslandPlugin;
