/**
 * DOM-derived ARIA wiring utilities — the backbone of every x-hot-* directive.
 *
 * Several directives resolve a fact out of the rendered DOM (which node is the
 * title, where is the real control, is there an error slot) and write ARIA onto
 * it. Because those attributes exist only in the browser — server markup has
 * never heard of them — any external DOM sync strips them back off. So instead
 * of applying once and hoping, keepWired() re-derives the wiring whenever the
 * subtree changes.
 *
 * `sync` callbacks MUST be idempotent: write only through setAttr(), with only
 * values they would compute again next pass. The observer sees our own writes;
 * an idempotent sync makes pass two a no-op, an unconditional one loops forever.
 */
const FOCUSABLE_SELECTOR = 'button, [href], input, select, textarea, [tabindex]';
/** The genuine focusable control a trigger wrapper stands for. */
export function resolveControl(el) {
    if (el.matches(FOCUSABLE_SELECTOR) && el.getAttribute('tabindex') !== '-1')
        return el;
    const nested = el.querySelector('button:not([tabindex="-1"]), a[href]:not([tabindex="-1"]), input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])');
    return (nested ?? el.firstElementChild ?? el);
}
let hotIdCounter = 0;
export function mintId(prefix = 'hot') {
    hotIdCounter += 1;
    return `${prefix}-${String(hotIdCounter)}-${Math.random().toString(36).slice(2, 7)}`;
}
export function ensureId(node, prefix = 'hot') {
    node.id ||= mintId(prefix);
    return node.id;
}
/**
 * ensureId that remembers which id it handed out per role, memoised on the
 * owner element. External DOM syncs strip generated ids; without the memo a
 * re-wire would mint a brand-new idref on every re-render — an id that keeps
 * changing under a screen reader.
 */
export function stableIds(owner) {
    const el = owner;
    const minted = (el._hotIds ??= {});
    return (node, role, prefix) => {
        if (node.id) {
            minted[role] = node.id;
            return node.id;
        }
        node.id = minted[role] ?? mintId(prefix);
        minted[role] = node.id;
        return node.id;
    };
}
/** Write an attribute only when it would change; null/''/undefined removes. */
export function setAttr(node, name, value) {
    if (value === null || value === undefined || value === '') {
        if (node.hasAttribute(name))
            node.removeAttribute(name);
        return;
    }
    if (node.getAttribute(name) !== String(value))
        node.setAttribute(name, String(value));
}
/** Attributes Hot-UI derives from the DOM; narrowing keeps the observer quiet. */
const WIRED_ATTRS = [
    'id', 'for', 'aria-describedby', 'aria-labelledby', 'aria-invalid',
    'aria-controls', 'aria-haspopup', 'aria-expanded', 'data-invalid', 'data-state',
];
/** Re-run `sync` whenever the subtree or wired attributes change. */
export function keepWired(el, sync, cleanup) {
    sync();
    const observer = new MutationObserver(sync);
    observer.observe(el, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: [...WIRED_ATTRS],
    });
    cleanup?.(() => observer.disconnect());
}
/**
 * `closest()`, climbing THROUGH an Alpine teleport rather than stopping at it.
 *
 * Teleported content is physically a child of <body>; `_x_teleportBack` links
 * it back to its markup home, which is the truth for "who owns this element".
 */
export function hotClosest(el, selector) {
    let node = el;
    while (node) {
        if (node.matches(selector))
            return node;
        const back = node._x_teleportBack;
        node = back ?? node.parentElement;
    }
    return null;
}
//# sourceMappingURL=wiring.js.map