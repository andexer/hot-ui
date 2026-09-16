export function createHotMentionInput(config) {
    return {
        trigger: config.trigger,
        mentions: config.mentions,
        open: false,
        query: '',
        activeIndex: 0,
        anchor: -1,
        get matches() {
            const q = this.query.toLowerCase();
            return this.mentions.filter((m) => m.label.toLowerCase().includes(q) || m.value.toLowerCase().includes(q));
        },
        get activeId() {
            const m = this.matches[this.activeIndex];
            return m ? this.$id('hot-mention-opt', m.value) : null;
        },
        scan() {
            const el = this.$refs['field'];
            if (!el)
                return;
            const pos = el.selectionStart ?? 0;
            const text = el.value.slice(0, pos);
            const idx = text.lastIndexOf(this.trigger);
            if (idx === -1) {
                this.close();
                return;
            }
            // The char before the trigger must be whitespace or start-of-text (so emails aren't caught).
            const before = idx === 0 ? '' : text[idx - 1] ?? '';
            if (before && !/\s/.test(before)) {
                this.close();
                return;
            }
            const word = text.slice(idx + this.trigger.length);
            // No whitespace allowed inside the active query.
            if (/\s/.test(word)) {
                this.close();
                return;
            }
            this.anchor = idx;
            this.query = word;
            this.activeIndex = 0;
            this.open = this.matches.length > 0;
        },
        move(dir) {
            const n = this.matches.length;
            if (!n)
                return;
            this.activeIndex = (this.activeIndex + dir + n) % n;
            this.$nextTick(() => this.scrollActive());
        },
        scrollActive() {
            const opt = this.$refs['list']?.querySelector('[data-active=\'true\']');
            opt?.scrollIntoView({ block: 'nearest' });
        },
        insertActive() {
            const m = this.matches[this.activeIndex];
            if (!m)
                return false;
            const el = this.$refs['field'];
            if (!el)
                return false;
            const pos = el.selectionStart ?? 0;
            const head = el.value.slice(0, this.anchor);
            const tail = el.value.slice(pos);
            const inserted = this.trigger + m.value + ' ';
            el.value = head + inserted + tail;
            const caret = (head + inserted).length;
            el.setSelectionRange(caret, caret);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            this.close();
            this.$nextTick(() => el.focus());
            return true;
        },
        close() {
            this.open = false;
            this.query = '';
            this.anchor = -1;
            this.activeIndex = 0;
        },
    };
}
export default {
    name: 'mention-input',
    register({ alpine }) {
        alpine.data('hotMentionInput', createHotMentionInput);
    },
};
//# sourceMappingURL=mention-input.js.map