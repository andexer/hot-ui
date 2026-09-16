import type { DirectiveHandler } from '../types.js';
import { keepWired, setAttr, stableIds } from '../dom/wiring.js';

interface LabelledByConfig { label?: string; description?: string }

/**
 * x-hot-labelledby="{ label: '[data-slot=dialog-title]', description: '[data-slot=dialog-description]' }"
 *
 * Wires aria-labelledby / aria-describedby on dialog/popover containers from
 * whichever slots actually rendered. Resolved from the DOM on every change —
 * a title can arrive or leave long after its container was created. Only
 * idrefs WE contributed are ever withdrawn; author-written ones survive.
 */
export const labelledByDirective: DirectiveHandler = (el, { expression }, { evaluate, cleanup }) => {
    const config = expression ? (evaluate(expression) as LabelledByConfig) : {};
    const ours: Record<string, string | undefined> = {};
    const idFor = stableIds(el);

    const wire = (selector: string | undefined, attribute: string): void => {
        if (!selector) return;
        const node = el.querySelector<HTMLElement>(selector);
        if (node) {
            ours[attribute] = idFor(node, attribute, 'hot-label');
            setAttr(el, attribute, ours[attribute]);
        } else if (ours[attribute] && el.getAttribute(attribute) === ours[attribute]) {
            setAttr(el, attribute, null);
            delete ours[attribute];
        }
    };

    keepWired(
        el,
        () => {
            wire(config.label, 'aria-labelledby');
            wire(config.description, 'aria-describedby');
        },
        cleanup,
    );
};
