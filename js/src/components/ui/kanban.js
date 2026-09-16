export function createHotKanban(config) {
    return {
        columns: config.columns,
        dragId: null,
        fromCol: null,
        overCol: null,
        // Native HTML5 drag is a progressive enhancement only. Begin a drag.
        start(ev, colId, cardId) {
            this.dragId = cardId;
            this.fromCol = colId;
            if (ev.dataTransfer) {
                ev.dataTransfer.effectAllowed = 'move';
                ev.dataTransfer.setData('text/plain', cardId);
            }
        },
        // Move the dragged card into the target column (appended at the end).
        drop(toCol) {
            this.overCol = null;
            const dragId = this.dragId;
            if (!dragId || this.fromCol === null)
                return;
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
        reset() {
            this.dragId = null;
            this.fromCol = null;
            this.overCol = null;
        },
    };
}
export default {
    name: 'kanban',
    register({ alpine }) {
        alpine.data('hotKanban', createHotKanban);
    },
};
//# sourceMappingURL=kanban.js.map