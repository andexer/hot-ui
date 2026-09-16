import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotMentionInput — textarea combobox with @trigger suggestions.
 *
 * Extracted verbatim from the template's inline x-data: the word immediately
 * before the caret is scanned for the trigger character; matching entries are
 * offered in a listbox popup while the textarea keeps focus (combobox pattern).
 */

/** One suggestion, normalised by the template layer. */
export interface MentionItem {
    value: string;
    label: string;
    avatar: string | null;
    sub: string | null;
}

export interface MentionInputConfig {
    /** Character(s) that start a mention query. */
    trigger: string;
    /** Candidate mentions (already normalised to MentionItem). */
    mentions: MentionItem[];
}

export interface MentionInputController {
    trigger: string;
    mentions: MentionItem[];
    open: boolean;
    query: string;
    activeIndex: number;
    /** Caret position where the current trigger word begins (index of the trigger char). */
    anchor: number;

    get matches(): MentionItem[];
    get activeId(): string | null;
    /** Inspect the word immediately before the caret; open when it starts with the trigger. */
    scan(): void;
    move(dir: number): void;
    scrollActive(): void;
    insertActive(): boolean;
    close(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface MentionInputScope {
    $id(name: string, key?: string | number | null): string;
    $nextTick(callback: () => void): void;
    $refs: Record<string, HTMLElement>;
}

type Live = MentionInputController & MentionInputScope;

export function createHotMentionInput(config: MentionInputConfig): MentionInputController {
    return {
        trigger: config.trigger,
        mentions: config.mentions,
        open: false,
        query: '',
        activeIndex: 0,
        anchor: -1,

        get matches(): MentionItem[] {
            const q = this.query.toLowerCase();

            return this.mentions.filter((m) => m.label.toLowerCase().includes(q) || m.value.toLowerCase().includes(q));
        },
        get activeId(): string | null {
            const m = this.matches[this.activeIndex];

            return m ? (this as Live).$id('hot-mention-opt', m.value) : null;
        },

        scan(): void {
            const el = (this as Live).$refs['field'] as HTMLTextAreaElement | undefined;
            if (!el) return;
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

        move(dir: number): void {
            const n = this.matches.length;
            if (!n) return;
            this.activeIndex = (this.activeIndex + dir + n) % n;
            (this as Live).$nextTick(() => this.scrollActive());
        },

        scrollActive(): void {
            const opt = (this as Live).$refs['list']?.querySelector('[data-active=\'true\']');
            opt?.scrollIntoView({ block: 'nearest' });
        },

        insertActive(): boolean {
            const m = this.matches[this.activeIndex];
            if (!m) return false;
            const el = (this as Live).$refs['field'] as HTMLTextAreaElement | undefined;
            if (!el) return false;
            const pos = el.selectionStart ?? 0;
            const head = el.value.slice(0, this.anchor);
            const tail = el.value.slice(pos);
            const inserted = this.trigger + m.value + ' ';
            el.value = head + inserted + tail;
            const caret = (head + inserted).length;
            el.setSelectionRange(caret, caret);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            this.close();
            (this as Live).$nextTick(() => el.focus());

            return true;
        },

        close(): void {
            this.open = false;
            this.query = '';
            this.anchor = -1;
            this.activeIndex = 0;
        },
    };
}

export default {
    name: 'mention-input',
    register({ alpine }: HotContext): void {
        alpine.data('hotMentionInput', createHotMentionInput as never);
    },
} satisfies IslandPlugin;
