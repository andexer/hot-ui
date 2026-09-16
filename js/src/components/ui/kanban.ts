import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotKanban — board of columns with HTML5 drag & drop card moves.
 *
 * Extracted verbatim from the template's inline x-data. Native drag is a
 * progressive enhancement: dragging moves the card object between column
 * arrays; everything else (highlighting, counts) recomputes from state.
 */

/** One card, normalised by the template layer. */
export interface KanbanCard {
    id: string;
    title: string;
    tags: string[];
    meta: string | null;
}

export interface KanbanColumn {
    id: string;
    title: string;
    cards: KanbanCard[];
}

export interface KanbanConfig {
    columns: KanbanColumn[];
}

export interface HotKanbanController {
    columns: KanbanColumn[];
    /** id of the card being dragged (null when idle). */
    dragId: string | null;
    fromCol: string | null;
    overCol: string | null;

    start(ev: DragEvent, colId: string, cardId: string): void;
    drop(toCol: string): void;
    reset(): void;
}

export function createHotKanban(config: KanbanConfig): HotKanbanController {
    return {
        columns: config.columns,
        dragId: null,
        fromCol: null,
        overCol: null,

        // Native HTML5 drag is a progressive enhancement only. Begin a drag.
        start(ev: DragEvent, colId: string, cardId: string): void {
            this.dragId = cardId;
            this.fromCol = colId;
            if (ev.dataTransfer) {
                ev.dataTransfer.effectAllowed = 'move';
                ev.dataTransfer.setData('text/plain', cardId);
            }
        },

        // Move the dragged card into the target column (appended at the end).
        drop(toCol: string): void {
            this.overCol = null;
            const dragId = this.dragId;
            if (!dragId || this.fromCol === null) return;
            if (this.fromCol === toCol) {
                this.reset();
                return;
            }
            const src = this.columns.find((c) => c.id === this.fromCol);
            const dst = this.columns.find((c) => c.id === toCol);
            if (!src || !dst) {
                this.reset();
                return;
            }
            const idx = src.cards.findIndex((c) => c.id === dragId);
            if (idx === -1) {
                this.reset();
                return;
            }
            const card = src.cards[idx];
            if (!card) {
                this.reset();
                return;
            }
            src.cards.splice(idx, 1);
            dst.cards.push(card);
            this.reset();
        },

        reset(): void {
            this.dragId = null;
            this.fromCol = null;
            this.overCol = null;
        },
    };
}

export default {
    name: 'kanban',
    register({ alpine }: HotContext): void {
        alpine.data('hotKanban', createHotKanban as never);
    },
} satisfies IslandPlugin;
