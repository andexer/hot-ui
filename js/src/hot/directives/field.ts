import type { DirectiveHandler } from '../types.js';
import { keepWired, setAttr, stableIds } from '../dom/wiring.js';

const FIELD_CONTROL =
    'input:not([type=hidden]), textarea, select, [role="checkbox"], [role="switch"], [role="radiogroup"], [role="combobox"], [role="slider"], [role="spinbutton"]';

/**
 * x-hot-field — wires a form field's control to its label/description/error:
 * aria-describedby ← description + error ids, aria-invalid + data-invalid when
 * an error slot exists, label[for] ← control id when the author did not set it.
 *
 * Re-derived on every relevant DOM change; only attributes WE contributed are
 * ever withdrawn, so author-written wiring always survives.
 */
export const fieldDirective: DirectiveHandler = (el, _directive, { cleanup }) => {
    let ourIds: string[] = [];
    let ourInvalid = false;
    const idFor = stableIds(el);

    const sync = (): void => {
        const control = el.querySelector<HTMLElement>(FIELD_CONTROL);
        if (!control) return;

        const description = el.querySelector<HTMLElement>('[data-slot="field-description"]');
        const error = el.querySelector<HTMLElement>('[data-slot="field-error"]');

        const ids: string[] = [];
        if (description) ids.push(idFor(description, 'desc', 'field-desc'));
        if (error) ids.push(idFor(error, 'err', 'field-err'));

        // Rebuild instead of append: anything that is neither ours-from-last-pass
        // nor ours-this-pass belongs to the author and keeps its position.
        const present = (control.getAttribute('aria-describedby') ?? '').split(/\s+/).filter(Boolean);
        const theirs = present.filter((id) => !ourIds.includes(id) && !ids.includes(id));
        setAttr(control, 'aria-describedby', [...theirs, ...ids].join(' '));
        ourIds = ids;

        if (error) {
            setAttr(control, 'aria-invalid', 'true');
            ourInvalid = true;
        } else if (ourInvalid) {
            setAttr(control, 'aria-invalid', null);
            ourInvalid = false;
        }

        setAttr(el, 'data-invalid', error ? 'true' : null);

        const label = el.querySelector<HTMLElement>('[data-slot="field-label"]');
        if (label && !label.getAttribute('for')) {
            setAttr(label, 'for', idFor(control, 'control', 'field-control'));
        }
    };

    keepWired(el, sync, cleanup);
};
