/**
 * hotRichTextEditor — controller for the WYSIWYG contenteditable editor.
 *
 * All behaviour extracted verbatim from the template's inline x-data: toolbar
 * commands through document.execCommand (deprecated but universally supported),
 * a mirror <textarea> kept in sync for forms/bindings, and queryCommandState
 * reflection into `active` so buttons can show aria-pressed.
 */
/** Keys reflected from document.queryCommandState (mirrors the toolbar toggles). */
const STATE_KEYS = [
    'bold',
    'italic',
    'underline',
    'strikeThrough',
    'insertUnorderedList',
    'insertOrderedList',
];
export function createRichTextEditor(_config) {
    return {
        active: {},
        _onSel: null,
        sync() {
            const scope = this;
            const input = scope.$refs['input'];
            const editor = scope.$refs['editor'];
            if (!input || !editor)
                return;
            input.value = editor.innerHTML;
            // Notify any listener bound to the mirror that the value changed.
            input.dispatchEvent(new Event('input', { bubbles: true }));
        },
        run(cmd) {
            const editor = this.$refs['editor'];
            if (!editor)
                return;
            editor.focus();
            document.execCommand(cmd, false);
            this.refresh();
            this.sync();
        },
        block(tag) {
            const editor = this.$refs['editor'];
            if (!editor)
                return;
            editor.focus();
            document.execCommand('formatBlock', false, tag);
            this.refresh();
            this.sync();
        },
        link() {
            const editor = this.$refs['editor'];
            if (!editor)
                return;
            editor.focus();
            const url = window.prompt('Link URL');
            if (url)
                document.execCommand('createLink', false, url);
            this.refresh();
            this.sync();
        },
        clear() {
            const editor = this.$refs['editor'];
            if (!editor)
                return;
            editor.focus();
            document.execCommand('removeFormat', false);
            document.execCommand('unlink', false);
            this.refresh();
            this.sync();
        },
        refresh() {
            // Only reflect state when the selection is inside this editor.
            const editor = this.$refs['editor'];
            const sel = window.getSelection();
            if (!editor || !sel || sel.rangeCount === 0 || !editor.contains(sel.anchorNode))
                return;
            const next = {};
            for (const k of STATE_KEYS) {
                try {
                    next[k] = document.queryCommandState(k);
                }
                catch {
                    next[k] = false;
                }
            }
            this.active = next;
        },
        init() {
            this.sync();
            this._onSel = () => this.refresh();
            document.addEventListener('selectionchange', this._onSel);
        },
        destroy() {
            if (this._onSel)
                document.removeEventListener('selectionchange', this._onSel);
        },
    };
}
export default {
    name: 'rich-text-editor',
    register({ alpine }) {
        alpine.data('hotRichTextEditor', createRichTextEditor);
    },
};
//# sourceMappingURL=rich-text-editor.js.map