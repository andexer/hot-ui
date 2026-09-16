import type { DirectiveHandler } from '../types.js';
import { autoUpdate, computePosition } from '../dom/position.js';

const PLACEMENTS = [
    'top', 'top-start', 'top-end',
    'right', 'right-start', 'right-end',
    'bottom', 'bottom-start', 'bottom-end',
    'left', 'left-start', 'left-end',
] as const;

/**
 * x-hot-anchor — positions a teleported popover at its trigger.
 *
 *   placement modifier      bottom-start etc. (default bottom)
 *   .offset-N               gap between trigger and popover
 *   .no-flip                never flip to the opposite side
 *   .match-width            popover is never narrower than its trigger
 *   .no-size                do not cap height (non-scrolling popovers)
 *   .pad-N                  viewport gutter (default 8)
 *   .absolute               document coords instead of fixed (viewport-pinned
 *                           popovers that must scroll away with their trigger)
 *
 * The anchored reference is re-evaluated whenever it leaves the document:
 * an external DOM sync may swap the trigger node for a fresh one, and the
 * observers would otherwise watch a detached element forever.
 */
export const anchorDirective: DirectiveHandler = (el, { modifiers, expression }, { evaluateLater, cleanup }) => {
    const placement = PLACEMENTS.find((candidate) => modifiers.includes(candidate)) ?? 'bottom';
    let offsetValue = 0;
    const offsetIndex = modifiers.indexOf('offset');
    if (offsetIndex !== -1) offsetValue = Number(modifiers[offsetIndex + 1]) || 0;

    const allowFlip = !modifiers.includes('no-flip');
    const matchWidth = modifiers.includes('match-width');
    const capHeight = !modifiers.includes('no-size');
    const strategy = modifiers.includes('absolute') ? ('absolute' as const) : ('fixed' as const);
    const padIndex = modifiers.indexOf('pad');
    const padding = padIndex !== -1 ? Number(modifiers[padIndex + 1]) || 8 : 8;
    const designMax = Number.parseFloat(getComputedStyle(el).maxHeight) || Number.POSITIVE_INFINITY;
    const getReference = evaluateLater(expression);

    let stopAuto: (() => void) | null = null;
    let reference: Element | null = null;

    const rebind = (next: Element): void => {
        stopAuto?.();
        reference = next;
        stopAuto = () => {}; // replaced below with real updater
        const updater = autoUpdate(reference, el, update);
        stopAuto = (): void => updater.stop();
    };

    function update(): void {
        if (reference?.isConnected === false) {
            getReference((fresh) => {
                if (fresh instanceof Element && fresh !== reference) rebind(fresh);
                else position();
            });

            return;
        }
        position();
    }

    function position(): void {
        if (!reference) return;
        computePosition(reference, el, {
            strategy,
            placement,
            offset: offsetValue,
            padding,
            allowFlip,
            capHeight,
            matchWidth,
            designMaxHeight: designMax,
        });
    }

    getReference((initial) => {
        if (initial instanceof Element) rebind(initial);
    });

    cleanup(() => stopAuto?.());
};
