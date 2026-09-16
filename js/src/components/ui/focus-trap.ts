/**
 * Local modal-focus machinery shared by the overlay islands
 * (dialog, alert-dialog, sheet, drawer, popover, command palette).
 *
 * Those templates historically requested Alpine's focus plugin through
 * `x-trap(.noscroll)(.inert)`, but the kernel ships zero plugins, so the
 * modifiers were silent no-ops. This module owns what they promised:
 *
 *   - Tab loop between the panel's focusables, wrapping both directions;
 *   - scroll lock while a modal layer is open (`noscroll`);
 *   - `inert` on everything outside the layer (`inert`);
 *   - initial focus on open ([autofocus] wins) and restore on close.
 *
 * Escape-to-close stays in the templates (`@keydown.escape.window`) so the
 * emitted markup keeps its exact event surface.
 *
 * Bookkeeping lives in MODULE scope, deliberately outside Alpine's reactive
 * proxies: the trap toggles from `x-effect="trap($el, open)"` expressions,
 * and writing reactive state inside an effect would re-trigger that effect.
 */

const FOCUSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
    '[contenteditable]:not([contenteditable="false"])',
].join(', ');

/** Behaviour knobs — one preset per overlay flavour. */
export interface TrapOptions {
    /** Lock document scrolling while engaged (the `.noscroll` flavour). */
    readonly lockScroll: boolean;
    /** Make everything outside the layer inert (the `.inert` flavour). */
    readonly inert: boolean;
}

interface Engagement {
    release(): void;
}

const ENGAGED = new WeakMap<HTMLElement, Engagement>();

// ---------------------------------------------------------------------------
// Scroll lock — refcounted so stacked overlays unwind cleanly.
// ---------------------------------------------------------------------------

let scrollDepth = 0;
let savedOverflow: { documentElement: string; body: string } | null = null;

function acquireScrollLock(): void {
    scrollDepth += 1;
    if (savedOverflow) return;
    savedOverflow = {
        documentElement: document.documentElement.style.overflow,
        body: document.body.style.overflow,
    };
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
}

function releaseScrollLock(): void {
    if (scrollDepth > 0) scrollDepth -= 1;
    if (scrollDepth > 0 || !savedOverflow) return;
    document.documentElement.style.overflow = savedOverflow.documentElement;
    document.body.style.overflow = savedOverflow.body;
    savedOverflow = null;
}

// ---------------------------------------------------------------------------
// Inert — applied to <body> children other than this layer, refcounted.
// ---------------------------------------------------------------------------

let inertDepth = 0;
const INERTED = new Set<HTMLElement>();

/**
 * The teleported layer this panel belongs to: the nearest ancestor sitting
 * directly under <body> (or the panel itself when nothing wraps it).
 */
function layerRoot(panel: HTMLElement): HTMLElement {
    let node = panel;
    let parent = panel.parentElement;
    while (parent && parent !== document.body) {
        node = parent;
        parent = parent.parentElement;
    }
    return node;
}

function acquireInert(root: HTMLElement): void {
    inertDepth += 1;
    for (const child of Array.from(document.body.children)) {
        if (child === root || root.contains(child)) continue;
        const sibling = child as HTMLElement;
        if (sibling.inert) continue;
        sibling.inert = true;
        INERTED.add(sibling);
    }
}

function releaseInert(): void {
    if (inertDepth > 0) inertDepth -= 1;
    if (inertDepth > 0 || INERTED.size === 0) return;
    for (const el of INERTED) el.inert = false;
    INERTED.clear();
}

// ---------------------------------------------------------------------------
// Focusables
// ---------------------------------------------------------------------------

function collectFocusables(panel: HTMLElement): HTMLElement[] {
    const nodes = panel.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR);
    return Array.from(nodes).filter((el) => {
        if (el.hasAttribute('disabled') || el.getAttribute('aria-disabled') === 'true') return false;
        // offsetParent is null inside display:none subtrees — hides x-shown
        // sections without dragging layout metrics into the hot path.
        return el.offsetParent !== null || el === document.activeElement;
    });
}

// ---------------------------------------------------------------------------
// Engagement lifecycle
// ---------------------------------------------------------------------------

function engage(panel: HTMLElement, options: TrapOptions): Engagement {
    const previous =
        document.activeElement instanceof HTMLElement ? document.activeElement : null;

    if (options.lockScroll) acquireScrollLock();
    if (options.inert) acquireInert(layerRoot(panel));

    // Focus once x-show has actually cleared display:none (two frames beat
    // any transition/style flush ordering between effects).
    let cancelled = false;
    let frame = requestAnimationFrame(() => {
        frame = requestAnimationFrame(() => {
            if (cancelled || !panel.isConnected) return;
            const initial =
                panel.querySelector<HTMLElement>('[autofocus]') ??
                collectFocusables(panel)[0] ??
                panel;
            initial.focus({ preventScroll: true });
        });
    });

    const onKeydown = (event: KeyboardEvent): void => {
        if (cancelled || event.key !== 'Tab' || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }
        const focusables = collectFocusables(panel);
        if (focusables.length === 0) {
            // Nothing tabbable: swallow Tab so focus cannot escape the layer.
            event.preventDefault();
            return;
        }
        const current = document.activeElement;
        const index =
            current instanceof HTMLElement && current !== panel
                ? focusables.indexOf(current)
                : -1;
        const next = event.shiftKey
            ? (index <= 0 ? focusables.length - 1 : index - 1)
            : ((index < 0 ? -1 : index) + 1) % focusables.length;
        const target = focusables[next];
        if (!target) return;
        event.preventDefault();
        target.focus({ preventScroll: true });
    };

    document.addEventListener('keydown', onKeydown, true);

    return {
        release(): void {
            cancelled = true;
            cancelAnimationFrame(frame);
            document.removeEventListener('keydown', onKeydown, true);
            if (options.lockScroll) releaseScrollLock();
            if (options.inert) releaseInert();
            if (previous?.isConnected === true && previous !== document.activeElement) {
                previous.focus({ preventScroll: true });
            }
        },
    };
}

/**
 * Toggle the trap for one panel. Called from island controllers, which are
 * called from `x-effect="trap($el, open)"` — idempotent per element: engaging
 * twice is a no-op, releasing when idle is a no-op.
 */
export function trapFocus(panel: HTMLElement, active: boolean, options: TrapOptions): void {
    const current = ENGAGED.get(panel);
    if (active) {
        if (!current) ENGAGED.set(panel, engage(panel, options));
        return;
    }
    if (current) {
        current.release();
        ENGAGED.delete(panel);
    }
}
