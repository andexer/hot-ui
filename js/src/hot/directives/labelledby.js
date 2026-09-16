import { keepWired, setAttr, stableIds } from '../dom/wiring.js';
/**
 * x-hot-labelledby="{ label: '[data-slot=dialog-title]', description: '[data-slot=dialog-description]' }"
 *
 * Wires aria-labelledby / aria-describedby on dialog/popover containers from
 * whichever slots actually rendered. Resolved from the DOM on every change —
 * a title can arrive or leave long after its container was created. Only
 * idrefs WE contributed are ever withdrawn; author-written ones survive.
 */
export const labelledByDirective = (el, { expression }, { evaluate, cleanup }) => {
    const config = expression ? evaluate(expression) : {};
    const ours = {};
    const idFor = stableIds(el);
    const wire = (selector, attribute) => {
        if (!selector)
            return;
        const node = el.querySelector(selector);
        if (node) {
            ours[attribute] = idFor(node, attribute, 'hot-label');
            setAttr(el, attribute, ours[attribute]);
        }
        else if (ours[attribute] && el.getAttribute(attribute) === ours[attribute]) {
            setAttr(el, attribute, null);
            delete ours[attribute];
        }
    };
    keepWired(el, () => {
        wire(config.label, 'aria-labelledby');
        wire(config.description, 'aria-describedby');
    }, cleanup);
};
//# sourceMappingURL=labelledby.js.map