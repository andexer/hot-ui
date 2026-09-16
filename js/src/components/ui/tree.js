export function createHotTree(_config) {
    return {
        focusables() {
            return [...(this.$el.querySelectorAll('[role=treeitem]'))].filter((el) => el.offsetParent !== null);
        },
        move(current, dir) {
            const items = this.focusables();
            const i = items.indexOf(current);
            if (i === -1)
                return;
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
export function createHotTreeTable(config = {}) {
    return {
        expanded: config.expanded ?? {},
        markdown: config.markdown ?? '',
        copied: false,
        toggle(id) {
            this.expanded[id] = !this.isOpen(id);
        },
        isOpen(id) {
            return Boolean(this.expanded[id]);
        },
        isVisible(path) {
            const parts = String(path).split('.');
            // Walk every strict ancestor; the row shows only if all are open.
            for (let i = 1; i < parts.length; i++) {
                const ancestor = parts.slice(0, i).join('.');
                if (!this.isOpen(ancestor))
                    return false;
            }
            return true;
        },
        copyTree() {
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
    register({ alpine }) {
        alpine.data('hotTree', createHotTree);
        alpine.data('hotTreeTable', createHotTreeTable);
    },
};
//# sourceMappingURL=tree.js.map