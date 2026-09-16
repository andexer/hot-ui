import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotScrollspy — controller for the table-of-contents scrollspy.
 *
 * Resolves each item's `href` to its in-page target, watches them with one
 * IntersectionObserver (bottom-weighted rootMargin so a section becomes
 * active as it enters the upper part of the viewport), and exposes `active`
 * for the link list. Clicking a link sets `active` immediately — the observer
 * confirms once scrolling settles.
 */

/** One TOC entry; templates pass the server-normalised items array. */
export interface ScrollSpyItem {
    href: string;
    label?: string;
    level?: number;
}

export interface ScrollSpyConfig {
    items?: ScrollSpyItem[];
}

export interface ScrollSpyController {
    active: string | null;
    items: ScrollSpyItem[];
    observer: IntersectionObserver | null;

    init(): void;
    destroy(): void;
    idFor(href: string | null | undefined): string;
}

export function hotScrollspy(config?: ScrollSpyConfig): ScrollSpyController {
    return {
        active: null,
        items: config?.items ?? [],
        observer: null,

        init() {
            const targets = this.items
                .map((item) => {
                    try {
                        return document.querySelector(item.href);
                    } catch {
                        return null;
                    }
                })
                .filter((el): el is Element => el !== null);

            if (!targets.length) return;

            // Default the active section to the first available target.
            this.active = targets[0]?.id ?? null;

            this.observer = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting && entry.target.id) {
                            this.active = entry.target.id;
                        }
                    }
                },
                { rootMargin: '0px 0px -70% 0px', threshold: 0 },
            );

            for (const el of targets) this.observer.observe(el);
        },

        destroy() {
            this.observer?.disconnect();
            this.observer = null;
        },

        idFor(href: string | null | undefined): string {
            return (href ?? '').replace(/^#/, '');
        },
    };
}

export default {
    name: 'scrollspy',
    register({ alpine }: HotContext): void {
        alpine.data('hotScrollspy', hotScrollspy as never);
    },
} satisfies IslandPlugin;
