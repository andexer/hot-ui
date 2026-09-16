/**
 * Positioning engine for teleported popovers — a focused replacement for
 * floating-ui covering exactly what Hot-UI needs: offset → flip → shift →
 * size, with viewport-relative ('fixed') coordinates by default so popovers
 * stay correct inside native <dialog> top layers.
 *
 * autoUpdate() keeps the position honest while the page moves: window
 * resize/scroll, plus ResizeObserver on both reference and floating node.
 */
const VIEWPORT_MARGIN = 140;
function rectOf(el) {
    const box = el.getBoundingClientRect();
    return { x: box.left, y: box.top, width: box.width, height: box.height };
}
function opposite(side) {
    const map = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };
    return map[side];
}
/** Base coordinates for a placement, before flip/shift/size. */
function baseCoords(reference, floating, placement, offset) {
    const [rawSide, rawAlign] = placement.split('-');
    const align = rawAlign ?? 'center';
    const side = rawSide;
    let x = reference.x;
    let y = reference.y;
    if (side === 'top')
        y -= floating.height + offset;
    if (side === 'bottom')
        y += reference.height + offset;
    if (side === 'left')
        x -= floating.width + offset;
    if (side === 'right')
        x += reference.width + offset;
    const crossVertical = side === 'top' || side === 'bottom';
    if (crossVertical) {
        if (align === 'start')
            x = reference.x;
        else if (align === 'end')
            x = reference.x + reference.width - floating.width;
        else
            x = reference.x + (reference.width - floating.width) / 2;
    }
    else {
        if (align === 'start')
            y = reference.y;
        else if (align === 'end')
            y = reference.y + reference.height - floating.height;
        else
            y = reference.y + (reference.height - floating.height) / 2;
    }
    return { x, y };
}
function isOutsideViewport(viewport, floating, x, y, padding) {
    return (x < padding ||
        y < padding ||
        x + floating.width > viewport.width - padding ||
        y + floating.height > viewport.height - padding);
}
/**
 * Computes left/top and applies height-cap / width-match policies.
 * Returns nothing; writes styles onto the floating element directly.
 */
export function computePosition(referenceEl, floatingEl, config) {
    const viewport = {
        x: 0,
        y: 0,
        width: document.documentElement.clientWidth,
        height: document.documentElement.clientHeight,
    };
    const reference = rectOf(referenceEl);
    const floating = rectOf(floatingEl);
    // A hidden popover measures 0×0; park it offscreen rather than at 0,0.
    if (reference.width === 0 && reference.height === 0) {
        Object.assign(floatingEl.style, { position: config.strategy, left: '-9999px', top: '-9999px' });
        return;
    }
    const [rawSide] = config.placement.split('-');
    let side = rawSide;
    let { x, y } = baseCoords(reference, floating, config.placement, config.offset);
    // Flip to the opposite side when the preferred one does not fit.
    const alignment = config.placement.split('-')[1];
    if (config.allowFlip && isOutsideViewport(viewport, floating, x, y, config.padding)) {
        side = opposite(side);
        const flipped = alignment ? `${side}-${alignment}` : side;
        ({ x, y } = baseCoords(reference, floating, flipped, config.offset));
    }
    // Shift along the cross axis to keep the popover inside the viewport.
    if (side === 'top' || side === 'bottom') {
        const min = config.padding;
        const max = viewport.width - config.padding - floating.width;
        x = Math.min(Math.max(x, min), Math.max(min, max));
    }
    else {
        const min = config.padding;
        const max = viewport.height - config.padding - floating.height;
        y = Math.min(Math.max(y, min), Math.max(min, max));
    }
    // Height cap: fit available space, never below 140px, never above design cap.
    if (config.capHeight) {
        const spaceBelow = viewport.height - config.padding - (side === 'bottom' ? y : reference.y + reference.height);
        const h = Math.min(config.designMaxHeight, Math.max(VIEWPORT_MARGIN, Math.floor(Math.max(spaceBelow, VIEWPORT_MARGIN))));
        floatingEl.style.maxHeight = Number.isFinite(h) ? `${h}px` : '';
    }
    if (config.matchWidth && reference.width > 0) {
        const w = `${Math.round(reference.width)}px`;
        if (floatingEl.style.minWidth !== w)
            floatingEl.style.minWidth = w;
    }
    Object.assign(floatingEl.style, {
        position: config.strategy,
        left: `${Math.round(x)}px`,
        top: `${Math.round(y)}px`,
    });
}
/** Keep calling `update()` while the page scrolls/resizes or boxes change. */
export function autoUpdate(reference, floating, update) {
    const onScroll = () => update();
    const onResize = () => update();
    const ro = typeof ResizeObserver === 'function'
        ? new ResizeObserver(() => update())
        : null;
    ro?.observe(reference);
    ro?.observe(floating);
    window.addEventListener('resize', onResize, { passive: true });
    window.addEventListener('scroll', onScroll, { capture: true, passive: true });
    update();
    return {
        stop() {
            ro?.disconnect();
            window.removeEventListener('resize', onResize);
            window.removeEventListener('scroll', onScroll, { capture: true });
        },
    };
}
//# sourceMappingURL=position.js.map