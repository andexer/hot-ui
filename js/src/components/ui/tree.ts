import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * Tree family — two controllers, one island:
 *
 *   hotTree        roving-tabindex + arrow-key navigation for the recursive
 *                  <ul role="tree"> (tree.php). Per-node expand state stays
 *                  inline in tree-node.php (a single `open` boolean).
 *
 *   hotTreeTable   table-level `expanded` map keyed by dotted path-id
 *                  ("0", "0.1", "0.1.2") shared by every uiTreeTableRow(),
 *                  plus the copy-as-markdown button (tree-table.php).
 */

// ---------------------------------------------------------------------------
// hotTree — keyboard navigation across visible rows (APG tree pattern).
// ---------------------------------------------------------------------------

export interface TreeConfig {}

export interface HotTreeController {
    /** Visible treeitem rows (offsetParent filters out collapsed branches). */
    focusables(): HTMLElement[];
    move(current: HTMLElement, dir: number): void;
}

interface TreeScope {
    $el: HTMLElement;
}

type LiveTree = HotTreeController & TreeScope;

export function createHotTree(_config?: TreeConfig): HotTreeController {
    return {
        focusables(): HTMLElement[] {
            return [...((this as LiveTree).$el.querySelectorAll<HTMLElement>('[role=treeitem]'))].filter(
                (el) => el.offsetParent !== null,
            );
        },
        move(current: HTMLElement, dir: number): void {
            const items = this.focusables();
            const i = items.indexOf(current);
            if (i === -1) return;
            const next = items[Math.min(items.length - 1, Math.max(0, i + dir))];
            if (next) {
                items.forEach((el) => {
                    el.tabIndex = -1;
                });
                next.tabIndex = 0;
                next.focus();
            }
        },
    };
}

// ---------------------------------------------------------------------------
// hotTreeTable — expanded map + visibility + markdown copy.
// ---------------------------------------------------------------------------

export interface TreeTableConfig {
    /** Path-ids that start expanded (seeded from each row's 'expanded' flag). */
    expanded?: Record<string, boolean>;
    /** Pre-rendered ├──/└──/│ markdown of the whole tree ('' when not copyable). */
    markdown?: string;
}

export interface HotTreeTableController {
    expanded: Record<string, boolean>;
    markdown: string;
    copied: boolean;

    toggle(id: string): void;
    isOpen(id: string): boolean;
    isVisible(path: string): boolean;
    copyTree(): void;
}

export function createHotTreeTable(config: TreeTableConfig = {}): HotTreeTableController {
    return {
        expanded: config.expanded ?? {},
        markdown: config.markdown ?? '',
        copied: false,

        toggle(id: string): void {
            this.expanded[id] = !this.isOpen(id);
        },
        isOpen(id: string): boolean {
            return Boolean(this.expanded[id]);
        },
        isVisible(path: string): boolean {
            const parts = String(path).split('.');
            // Walk every strict ancestor; the row shows only if all are open.
            for (let i = 1; i < parts.length; i++) {
                const ancestor = parts.slice(0, i).join('.');
                if (!this.isOpen(ancestor)) return false;
            }

            return true;
        },
        copyTree(): void {
            navigator.clipboard.writeText(this.markdown);
            this.copied = true;
            setTimeout(() => {
                this.copied = false;
            }, 1500);
        },
    };
}

export default {
    name: 'tree',
    register({ alpine }: HotContext): void {
        alpine.data('hotTree', createHotTree as never);
        alpine.data('hotTreeTable', createHotTreeTable as never);
    },
} satisfies IslandPlugin;
