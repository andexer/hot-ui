import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

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
] as const;

export type RichTextStateKey = (typeof STATE_KEYS)[number];

export interface RichTextEditorConfig {}

export interface RichTextEditorController {
    /** Command-state map driving `:aria-pressed` on toggle buttons. */
    active: Record<string, boolean>;
    /** document selectionchange listener installed by init(). */
    _onSel: (() => void) | null;

    /** Push the editor HTML into the mirror textarea (when present) and notify it. */
    sync(): void;
    run(cmd: string): void;
    block(tag: string): void;
    link(): void;
    clear(): void;
    refresh(): void;
    init(): void;
    destroy(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface RichTextEditorScope {
    $refs: Record<string, HTMLElement>;
}

type Live = RichTextEditorController & RichTextEditorScope;

export function createRichTextEditor(_config?: RichTextEditorConfig): RichTextEditorController {
    return {
        active: {},
        _onSel: null,

        sync(): void {
            const scope = this as Live;
            const input = scope.$refs['input'] as HTMLTextAreaElement | undefined;
            const editor = scope.$refs['editor'];
            if (!input || !editor) return;
            input.value = editor.innerHTML;
            // Notify any listener bound to the mirror that the value changed.
            input.dispatchEvent(new Event('input', { bubbles: true }));
        },

        run(cmd: string): void {
            const editor = (this as Live).$refs['editor'];
            if (!editor) return;
            editor.focus();
            document.execCommand(cmd, false);
            this.refresh();
            this.sync();
        },

        block(tag: string): void {
            const editor = (this as Live).$refs['editor'];
            if (!editor) return;
            editor.focus();
            document.execCommand('formatBlock', false, tag);
            this.refresh();
            this.sync();
        },

        link(): void {
            const editor = (this as Live).$refs['editor'];
            if (!editor) return;
            editor.focus();
            const url = window.prompt('Link URL');
            if (url) document.execCommand('createLink', false, url);
            this.refresh();
            this.sync();
        },

        clear(): void {
            const editor = (this as Live).$refs['editor'];
            if (!editor) return;
            editor.focus();
            document.execCommand('removeFormat', false);
            document.execCommand('unlink', false);
            this.refresh();
            this.sync();
        },

        refresh(): void {
            // Only reflect state when the selection is inside this editor.
            const editor = (this as Live).$refs['editor'];
            const sel = window.getSelection();
            if (!editor || !sel || sel.rangeCount === 0 || !editor.contains(sel.anchorNode)) return;
            const next: Record<string, boolean> = {};
            for (const k of STATE_KEYS) {
                try {
                    next[k] = document.queryCommandState(k);
                } catch {
                    next[k] = false;
                }
            }
            this.active = next;
        },

        init(): void {
            this.sync();
            this._onSel = () => this.refresh();
            document.addEventListener('selectionchange', this._onSel);
        },

        destroy(): void {
            if (this._onSel) document.removeEventListener('selectionchange', this._onSel);
        },
    };
}

export default {
    name: 'rich-text-editor',
    register({ alpine }: HotContext): void {
        alpine.data('hotRichTextEditor', createRichTextEditor as never);
    },
} satisfies IslandPlugin;
