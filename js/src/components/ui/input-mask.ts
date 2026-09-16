import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotInputMask — controller for the input-mask.
 *
 * The mask application logic extracted verbatim from the template's inline
 * x-data: '9' = digit, 'a' = letter, '*' = alphanumeric, anything else is a
 * literal. The mask pattern travels on the element itself (data-mask), so the
 * controller needs no configuration.
 */

export interface HotInputMaskController {
    /** Re-apply the mask to the element's current value. */
    apply(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface InputMaskScope {
    $el: HTMLElement;
}

type Live = HotInputMaskController & InputMaskScope;

export function createHotInputMask(): HotInputMaskController {
    return {
        apply(): void {
            const el = (this as Live).$el;
            const mask = el.dataset['mask'];
            if (!mask) return;
            const v = (el as HTMLInputElement).value;
            let out = '';
            let vi = 0;
            for (let mi = 0; mi < mask.length; mi++) {
                if (vi >= v.length) break;
                const m = mask.charAt(mi);
                if (m === '9' || m === 'a' || m === '*') {
                    let ok = false;
                    while (vi < v.length) {
                        const c = v.charAt(vi);
                        vi += 1;
                        const good = m === '9' ? /[0-9]/.test(c) : m === 'a' ? /[a-z]/i.test(c) : /[a-z0-9]/i.test(c);
                        if (good) {
                            out += c;
                            ok = true;
                            break;
                        }
                    }
                    if (!ok) break;
                } else {
                    out += m;
                    if (vi < v.length && v.charAt(vi) === m) vi += 1;
                }
            }
            (el as HTMLInputElement).value = out;
        },
    };
}

export default {
    name: 'input-mask',
    register({ alpine }: HotContext): void {
        alpine.data('hotInputMask', createHotInputMask as never);
    },
} satisfies IslandPlugin;
