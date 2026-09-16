/**
 * APG roving focus ($hot.nav) and typeahead ($hot.type) for composite widgets.
 * They move DOM focus only — selection stays the widget's business.
 */
function visibleItems(el, selector) {
    return Array.from(el.querySelectorAll(selector)).filter((item) => item.offsetParent !== null &&
        item.getAttribute('aria-disabled') !== 'true' &&
        !item.hasAttribute('disabled'));
}
/** Arrow/Home/End roving focus with wrapping. */
export function createNavMagic() {
    return (el) => (event, options = {}) => {
        if (!event?.key)
            return;
        const selector = options.selector ?? '[role^="menuitem"], [role="option"]';
        const orientation = options.orientation ?? 'vertical';
        const loop = options.loop !== false;
        const horiz = orientation !== 'vertical';
        const vert = orientation !== 'horizontal';
        const items = visibleItems(el, selector);
        if (items.length === 0)
            return;
        const currentIndex = items.indexOf(document.activeElement);
        if ((options.requireMatch ?? false) && currentIndex < 0)
            return;
        let index = null;
        if ((vert && event.key === 'ArrowDown') || (horiz && event.key === 'ArrowRight')) {
            index = currentIndex < 0 ? 0 : currentIndex + 1;
        }
        else if ((vert && event.key === 'ArrowUp') || (horiz && event.key === 'ArrowLeft')) {
            index = currentIndex < 0 ? items.length - 1 : currentIndex - 1;
        }
        else if (event.key === 'Home' || event.key === 'PageUp') {
            index = 0;
        }
        else if (event.key === 'End' || event.key === 'PageDown') {
            index = items.length - 1;
        }
        else {
            return;
        }
        const bounded = loop ? ((index % items.length) + items.length) % items.length : Math.max(0, Math.min(items.length - 1, index));
        const target = items[bounded];
        if (target) {
            target.focus();
            event.preventDefault();
            event.stopPropagation();
        }
    };
}
const TYPEAHEAD_DEFAULT = '[role^="menuitem"], [role="option"], [role="menuitemradio"], [role="menuitemcheckbox"]';
const TYPEAHEAD_WINDOW_MS = 500;
/** 500 ms typeahead: move focus to the next item starting with the typed text. */
export function createTypeaheadMagic() {
    return (el) => (event, selector = TYPEAHEAD_DEFAULT) => {
        if (!event || event.key == null || event.key.length !== 1 || event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }
        const items = visibleItems(el, selector);
        if (items.length === 0)
            return;
        const state = el;
        state._hotBuf = (state._hotBuf ?? '') + event.key.toLowerCase();
        clearTimeout(state._hotBufT);
        state._hotBufT = setTimeout(() => {
            state._hotBuf = '';
        }, TYPEAHEAD_WINDOW_MS);
        const start = items.indexOf(document.activeElement);
        const ordered = [...items.slice(start + 1), ...items.slice(0, start + 1)];
        const needle = state._hotBuf ?? '';
        const match = ordered.find((item) => (item.textContent ?? '').trim().toLowerCase().startsWith(needle));
        if (match) {
            match.focus();
            event.preventDefault();
        }
    };
}
//# sourceMappingURL=navigation.js.map