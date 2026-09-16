import type { DirectiveHandler } from '../types.js';

const DURATION_MS = 200;
const EASING = 'cubic-bezier(0.4, 0, 0.2, 1)';

interface CollapseState { originalTransition?: string }

/**
 * x-hot-collapse — animated expand/collapse for x-show'd panels. Apply it on
 * the SAME element as an x-show driven by the same boolean; it animates
 * height between 0 and auto on each toggle.
 */
export const collapseDirective: DirectiveHandler = (el, { expression }, { evaluate, effect }) => {
    const state = el as HTMLElement & CollapseState;

    effect(() => {
        let open = false;
        try {
            open = Boolean(evaluate(expression));
        } catch {
            return;
        }

        if (open) {
            state.style.removeProperty('display');
            const target = `${el.scrollHeight}px`;
            state.originalTransition ??= el.style.transition;
            el.style.transition = `height ${DURATION_MS}ms ${EASING}`;
            el.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                el.style.height = target;
            });
            window.setTimeout(() => {
                el.style.removeProperty('height');
                el.style.removeProperty('overflow');
                el.style.transition = state.originalTransition ?? '';
            }, DURATION_MS);
        } else if (!el.hasAttribute('hidden') && el.style.display !== 'none') {
            state.originalTransition ??= el.style.transition;
            el.style.transition = `height ${DURATION_MS}ms ${EASING}`;
            el.style.height = `${el.scrollHeight}px`;
            el.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                el.style.height = '0px';
            });
            window.setTimeout(() => {
                el.style.display = 'none';
                el.style.removeProperty('height');
                el.style.removeProperty('overflow');
                el.style.transition = state.originalTransition ?? '';
            }, DURATION_MS);
        }
    });
};
