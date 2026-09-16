import type { HotModel } from '../../hot/magic/model.js';
import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotMarkdownEditor — textarea + live HTML preview controller.
 *
 * Extracted verbatim from the template's one-shot <script> (which existed only
 * because attribute decoding corrupted entity strings in an inline x-data).
 * Markdown is rendered entirely client-side by a small safe-subset renderer:
 * input is ESCAPED first, then a fixed set of patterns is applied, so user
 * content can never inject markup.
 */

export interface MarkdownEditorConfig {
    /** A $hot.model instance handed over by the template (`$hot.model(default)`). */
    model: HotModel;
}

export type MarkdownView = 'write' | 'preview';

export interface MarkdownEditorController {
    _model: HotModel;
    view: MarkdownView;

    /** The bound value IS the source — no second copy to sync. */
    get source(): string;
    set source(v: string);
    get html(): string;
    get textarea(): HTMLTextAreaElement;

    // ---- Selection-aware insertion helpers (toolbar) ----
    wrap(before: string, after?: string): void;
    prefixLines(make: (line: string, index: number) => string): void;
    insertLink(): void;
    bold(): void;
    italic(): void;
    code(): void;
    ul(): void;
    ol(): void;
    quote(): void;

    // ---- Inline markdown→HTML renderer (safe subset) ----
    escape(str: string): string;
    safeUrl(url: string): string;
    inline(text: string): string;
    render(src: string): string;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface MarkdownEditorScope {
    $nextTick(callback: () => void): void;
    $refs: Record<string, HTMLElement>;
}

type Live = MarkdownEditorController & MarkdownEditorScope;

export function createHotMarkdownEditor(config: MarkdownEditorConfig): MarkdownEditorController {
    return {
        _model: config.model,
        view: 'write',

        get source(): string {
            const v = this._model.value;

            return typeof v === 'string' ? v : '';
        },
        set source(v: string) {
            this._model.value = v;
        },
        get html(): string {
            return this.render(this.source);
        },

        // ---- Selection-aware insertion helpers (toolbar) ----
        get textarea(): HTMLTextAreaElement {
            return (this as Live).$refs['textarea'] as HTMLTextAreaElement;
        },

        wrap(before: string, after: string = before): void {
            const ta = this.textarea;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const sel = this.source.slice(start, end);
            this.source = this.source.slice(0, start) + before + sel + after + this.source.slice(end);
            (this as Live).$nextTick(() => {
                ta.focus();
                ta.setSelectionRange(start + before.length, start + before.length + sel.length);
            });
        },

        prefixLines(make: (line: string, index: number) => string): void {
            const ta = this.textarea;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const lineStart = this.source.lastIndexOf('\n', start - 1) + 1;
            let lineEnd = this.source.indexOf('\n', end);
            if (lineEnd === -1) lineEnd = this.source.length;
            const block = this.source.slice(lineStart, lineEnd) || '';
            const out = block.split('\n').map((l, i) => make(l, i)).join('\n');
            this.source = this.source.slice(0, lineStart) + out + this.source.slice(lineEnd);
            (this as Live).$nextTick(() => {
                ta.focus();
                ta.setSelectionRange(lineStart, lineStart + out.length);
            });
        },

        insertLink(): void {
            const ta = this.textarea;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const sel = this.source.slice(start, end) || 'text';
            const snippet = '[' + sel + '](url)';
            this.source = this.source.slice(0, start) + snippet + this.source.slice(end);
            (this as Live).$nextTick(() => {
                ta.focus();
                const urlAt = start + sel.length + 3;
                ta.setSelectionRange(urlAt, urlAt + 3);
            });
        },

        bold(): void {
            this.wrap('**');
        },
        italic(): void {
            this.wrap('*');
        },
        code(): void {
            this.wrap('`');
        },
        ul(): void {
            this.prefixLines((l) => (l.startsWith('- ') ? l : '- ' + l));
        },
        ol(): void {
            let n = 0;
            this.prefixLines((l) => String(++n) + '. ' + l.replace(/^\d+\.\s+/, ''));
        },
        quote(): void {
            this.prefixLines((l) => (l.startsWith('> ') ? l : '> ' + l));
        },

        // ---- Inline markdown→HTML renderer (safe subset) ----
        escape(str: string): string {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        },

        safeUrl(url: string): string {
            const u = (url || '').trim();
            if (/^(https?:\/\/|mailto:|\/|#|\.\/|\.\.\/)/i.test(u)) return u;
            if (/^[a-z][a-z0-9+.-]*:/i.test(u)) return '#';

            return u;
        },

        inline(text: string): string {
            let out = text;
            out = out.replace(/`([^`]+)`/g, (_m, c: string) =>
                '<code class="bg-muted relative rounded px-[0.3rem] py-[0.2rem] font-mono text-[0.85em] font-medium">' + c + '</code>');
            out = out.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (_m, t: string, u: string) => {
                const href = this.safeUrl(u.replace(/&amp;/g, '&')).replace(/&/g, '&amp;');

                return '<a href="' + href + '" class="text-primary font-medium underline underline-offset-4" rel="noopener noreferrer nofollow">' + t + '</a>';
            });
            out = out.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold">$1</strong>');
            out = out.replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em class="italic">$2</em>');

            return out;
        },

        render(src: string): string {
            const raw = (src ?? '').replace(/\r\n?/g, '\n');
            const lines = this.escape(raw).split('\n');
            const html: string[] = [];
            let i = 0;
            let listType: 'ul' | 'ol' | null = null;
            const closeList = (): void => {
                if (listType) {
                    html.push('</' + listType + '>');
                    listType = null;
                }
            };

            while (i < lines.length) {
                const line: string = lines[i] ?? '';

                if (/^```/.test(line.trim())) {
                    closeList();
                    const buf: string[] = [];
                    i++;
                    while (i < lines.length && !/^```/.test((lines[i] ?? '').trim())) {
                        buf.push(lines[i] ?? '');
                        i++;
                    }
                    i++;
                    html.push('<pre class="bg-muted my-3 overflow-x-auto rounded-md p-3 text-sm"><code class="font-mono">' + buf.join('\n') + '</code></pre>');
                    continue;
                }

                let m: RegExpMatchArray | null;
                if ((m = line.match(/^###\s+(.*)$/))) { closeList(); html.push('<h3 class="mt-4 mb-2 text-lg font-semibold tracking-tight">' + this.inline(m[1] ?? '') + '</h3>'); i++; continue; }
                if ((m = line.match(/^##\s+(.*)$/)))  { closeList(); html.push('<h2 class="mt-5 mb-2 text-xl font-semibold tracking-tight">' + this.inline(m[1] ?? '') + '</h2>'); i++; continue; }
                if ((m = line.match(/^#\s+(.*)$/)))   { closeList(); html.push('<h1 class="mt-6 mb-3 text-2xl font-bold tracking-tight first:mt-0">' + this.inline(m[1] ?? '') + '</h1>'); i++; continue; }

                if (/^&gt;\s?/.test(line)) {
                    closeList();
                    const buf: string[] = [];
                    while (i < lines.length && /^&gt;\s?/.test(lines[i] ?? '')) {
                        buf.push(this.inline((lines[i] ?? '').replace(/^&gt;\s?/, '')));
                        i++;
                    }
                    html.push('<blockquote class="text-muted-foreground my-3 border-s-2 ps-4 italic">' + buf.join('<br>') + '</blockquote>');
                    continue;
                }

                if ((m = line.match(/^[-*]\s+(.*)$/))) {
                    if (listType !== 'ul') { closeList(); html.push('<ul class="my-3 ms-6 list-disc space-y-1">'); listType = 'ul'; }
                    html.push('<li>' + this.inline(m[1] ?? '') + '</li>'); i++; continue;
                }

                if ((m = line.match(/^\d+\.\s+(.*)$/))) {
                    if (listType !== 'ol') { closeList(); html.push('<ol class="my-3 ms-6 list-decimal space-y-1">'); listType = 'ol'; }
                    html.push('<li>' + this.inline(m[1] ?? '') + '</li>'); i++; continue;
                }

                if (line.trim() === '') { closeList(); i++; continue; }

                closeList();
                const buf: string[] = [];
                while (
                    i < lines.length &&
                    (lines[i] ?? '').trim() !== '' &&
                    !/^```/.test((lines[i] ?? '').trim()) &&
                    !/^#{1,3}\s+/.test(lines[i] ?? '') &&
                    !/^&gt;\s?/.test(lines[i] ?? '') &&
                    !/^[-*]\s+/.test(lines[i] ?? '') &&
                    !/^\d+\.\s+/.test(lines[i] ?? '')
                ) {
                    buf.push(this.inline(lines[i] ?? ''));
                    i++;
                }
                html.push('<p class="my-2 leading-7">' + buf.join('<br>') + '</p>');
            }

            closeList();

            return html.join('') || '<p class="text-muted-foreground italic">Nothing to preview yet.</p>';
        },
    };
}

export default {
    name: 'markdown-editor',
    register({ alpine }: HotContext): void {
        alpine.data('hotMarkdownEditor', createHotMarkdownEditor as never);
    },
} satisfies IslandPlugin;
