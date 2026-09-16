/**
 * x-hot-dialog-layer — puts a teleported popover in the right stacking layer.
 *
 * When the popover's teleport home sits inside a native <dialog> opened with
 * showModal(), move it INTO that dialog so both share the browser's top layer;
 * otherwise stay in <body>. Pure DOM, one-shot by design: reparenting a native
 * <dialog>'s subtree closes the modal outright, so no morph can invalidate the
 * structural fact this resolves.
 */
export const dialogLayerDirective = (el) => {
    queueMicrotask(() => {
        const home = el._x_teleportBack;
        if (!home)
            return;
        const target = home.closest('dialog') ?? document.body;
        if (el.parentElement !== target) {
            target.appendChild(el);
            // Reparenting changes the offsetParent; nudge any anchor observers
            // to recompute against the new containing block.
            requestAnimationFrame(() => window.dispatchEvent(new Event('resize')));
        }
    });
};
//# sourceMappingURL=dialog-layer.js.map