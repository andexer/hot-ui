import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotAccordion — controller for the accordion family, plus the single-property
 * hotCollapsible that drives the collapsible family (same expand/collapse
 * contract, one item deep).
 *
 * The root owns `open` — a scalar in 'single' mode, an array in 'multiple'
 * mode exactly as the templates always expressed it — and the toggle machine.
 * Items carry their own identity (`_v`) inline; triggers/panels only read
 * `isOpen(_v)` through scope inheritance. Animated panels pair their x-show
 * with the kernel's `x-hot-collapse` directive.
 */

export type AccordionType = 'single' | 'multiple';

/** Seed value: array for multiple, scalar|null for single (template's js() shape). */
export type AccordionSeed = string | string[] | null;

export interface AccordionConfig {
    /** 'single' (default) keeps at most one panel open; 'multiple' toggles freely. */
    type?: AccordionType;
    /** In single mode, allow closing the open item again. */
    collapsible?: boolean;
    /** Initially open item(s). */
    value?: AccordionSeed;
}

export interface AccordionController {
    type: AccordionType;
    collapsible: boolean;
    open: AccordionSeed;
    toggle(v: string): void;
    isOpen(v: string): boolean;
}

export function hotAccordion(config?: AccordionConfig): AccordionController {
    const type: AccordionType = config?.type === 'multiple' ? 'multiple' : 'single';
    const seed = config?.value ?? null;

    return {
        type,
        collapsible: config?.collapsible ?? false,
        open: type === 'multiple' ? (Array.isArray(seed) ? seed : []) : (Array.isArray(seed) ? null : seed),

        toggle(v: string): void {
            if (this.type === 'multiple') {
                const list = Array.isArray(this.open) ? this.open : [];
                this.open = list.includes(v) ? list.filter((x) => x !== v) : [...list, v];

                return;
            }
            this.open = this.open === v ? (this.collapsible ? null : this.open) : v;
        },

        isOpen(v: string): boolean {
            if (this.type === 'multiple') {
                return (Array.isArray(this.open) ? this.open : []).includes(v);
            }

            return !Array.isArray(this.open) && this.open === v;
        },
    };
}

// ---------------------------------------------------------------------------
// Collapsible — the one-panel cousin. Its trigger flips `open`; its content
// pairs x-show="open" with x-hot-collapse="open" for the height animation.
// ---------------------------------------------------------------------------

export interface CollapsibleConfig {
    /** Initial state; templates pass `open: <?= js((bool) $open) ?>`. */
    open?: boolean;
}

export interface CollapsibleController {
    open: boolean;
}

export function hotCollapsible(config?: CollapsibleConfig): CollapsibleController {
    return {
        open: config?.open ?? false,
    };
}

export default {
    name: 'accordion',
    register({ alpine }: HotContext): void {
        alpine.data('hotAccordion', hotAccordion as never);
        alpine.data('hotCollapsible', hotCollapsible as never);
    },
} satisfies IslandPlugin;
