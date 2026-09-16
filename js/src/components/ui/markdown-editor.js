export function createHotMarkdownEditor(config) {
    return {
        _model: config.model,
        view: 'write',
        get source() {
            const v = this._model.value;
            return typeof v === 'string' ? v : '';
        },
        set source(v) {
            this._model.value = v;
        },
        get html() {
            return this.render(this.source);
        },
        // ---- Selection-aware insertion helpers (toolbar) ----
        get textarea() {
            return this.$refs['textarea'];
        },
        wrap(before, after = before) {
            const ta = this.textarea;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const sel = this.source.slice(start, end);
            this.source = this.source.slice(0, start) + before + sel + after + this.source.slice(end);
            this.$nextTick(() => {
                ta.focus();
                ta.setSelectionRange(start + before.length, start + before.length + sel.length);
            });
        },
        prefixLines(make) {
            const ta = this.textarea;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const lineStart = this.source.lastIndexOf('\n', start - 1) + 1;
            let lineEnd = this.source.indexOf('\n', end);
            if (lineEnd === -1)
                lineEnd = this.source.length;
            const block = this.source.slice(lineStart, lineEnd) || '';
            const out = block.split('\n').map((l, i) => make(l, i)).join('\n');
            this.source = this.source.slice(0, lineStart) + out + this.source.slice(lineEnd);
            this.$nextTick(() => {
                ta.focus();
                ta.setSelectionRange(lineStart, lineStart + out.length);
            });
        },
        insertLink() {
            const ta = this.textarea;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const sel = this.source.slice(start, end) || 'text';
            const snippet = '[' + sel + '](url)';
            this.source = this.source.slice(0, start) + snippet + this.source.slice(end);
            this.$nextTick(() => {
                ta.focus();
                const urlAt = start + sel.length + 3;
                ta.setSelectionRange(urlAt, urlAt + 3);
            });
        },
        bold() {
            this.wrap('**');
        },
        italic() {
            this.wrap('*');
        },
        code() {
            this.wrap('`');
        },
        ul() {
            this.prefixLines((l) => (l.startsWith('- ') ? l : '- ' + l));
        },
        ol() {
            let n = 0;
            this.prefixLines((l) => String(++n) + '. ' + l.replace(/^\d+\.\s+/, ''));
        },
        quote() {
            this.prefixLines((l) => (l.startsWith('> ') ? l : '> ' + l));
        },
        // ---- Inline markdown→HTML renderer (safe subset) ----
        escape(str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        },
        safeUrl(url) {
            const u = (url || '').trim();
            if (/^(https?:\/\/|mailto:|\/|#|\.\/|\.\.\/)/i.test(u))
                return u;
            if (/^[a-z][a-z0-9+.-]*:/i.test(u))
                return '#';
            return u;
        },
        inline(text) {
            let out = text;
            out = out.replace(/`([^`]+)`/g, (_m, c) => '<code class="bg-muted relative rounded px-[0.3rem] py-[0.2rem] font-mono text-[0.85em] font-medium">' + c + '</code>');
            out = out.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (_m, t, u) => {
                const href = this.safeUrl(u.replace(/&amp;/g, '&')).replace(/&/g, '&amp;');
                return '<a href="' + href + '" class="text-primary font-medium underline underline-offset-4" rel="noopener noreferrer nofollow">' + t + '</a>';
            });
            out = out.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold">$1</strong>');
            out = out.replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em class="italic">$2</em>');
            return out;
        },
        render(src) {
            const raw = (src ?? '').replace(/\r\n?/g, '\n');
            const lines = this.escape(raw).split('\n');
            const html = [];
            let i = 0;
            let listType = null;
            const closeList = () => {
                if (listType) {
                    html.push('</' + listType + '>');
                    listType = null;
                }
            };
            while (i < lines.length) {
                const line = lines[i] ?? '';
                if (/^```/.test(line.trim())) {
                    closeList();
                    const buf = [];
                    i++;
                    while (i < lines.length && !/^```/.test((lines[i] ?? '').trim())) {
                        buf.push(lines[i] ?? '');
                        i++;
                    }
                    i++;
                    html.push('<pre class="bg-muted my-3 overflow-x-auto rounded-md p-3 text-sm"><code class="font-mono">' + buf.join('\n') + '</code></pre>');
                    continue;
                }
                let m;
                if ((m = line.match(/^###\s+(.*)$/))) {
                    closeList();
                    html.push('<h3 class="mt-4 mb-2 text-lg font-semibold tracking-tight">' + this.inline(m[1] ?? '') + '</h3>');
                    i++;
                    continue;
                }
                if ((m = line.match(/^##\s+(.*)$/))) {
                    closeList();
                    html.push('<h2 class="mt-5 mb-2 text-xl font-semibold tracking-tight">' + this.inline(m[1] ?? '') + '</h2>');
                    i++;
                    continue;
                }
                if ((m = line.match(/^#\s+(.*)$/))) {
                    closeList();
                    html.push('<h1 class="mt-6 mb-3 text-2xl font-bold tracking-tight first:mt-0">' + this.inline(m[1] ?? '') + '</h1>');
                    i++;
                    continue;
                }
                if (/^&gt;\s?/.test(line)) {
                    closeList();
                    const buf = [];
                    while (i < lines.length && /^&gt;\s?/.test(lines[i] ?? '')) {
                        buf.push(this.inline((lines[i] ?? '').replace(/^&gt;\s?/, '')));
                        i++;
                    }
                    html.push('<blockquote class="text-muted-foreground my-3 border-s-2 ps-4 italic">' + buf.join('<br>') + '</blockquote>');
                    continue;
                }
                if ((m = line.match(/^[-*]\s+(.*)$/))) {
                    if (listType !== 'ul') {
                        closeList();
                        html.push('<ul class="my-3 ms-6 list-disc space-y-1">');
                        listType = 'ul';
                    }
                    html.push('<li>' + this.inline(m[1] ?? '') + '</li>');
                    i++;
                    continue;
                }
                if ((m = line.match(/^\d+\.\s+(.*)$/))) {
                    if (listType !== 'ol') {
                        closeList();
                        html.push('<ol class="my-3 ms-6 list-decimal space-y-1">');
                        listType = 'ol';
                    }
                    html.push('<li>' + this.inline(m[1] ?? '') + '</li>');
                    i++;
                    continue;
                }
                if (line.trim() === '') {
                    closeList();
                    i++;
                    continue;
                }
                closeList();
                const buf = [];
                while (i < lines.length &&
                    (lines[i] ?? '').trim() !== '' &&
                    !/^```/.test((lines[i] ?? '').trim()) &&
                    !/^#{1,3}\s+/.test(lines[i] ?? '') &&
                    !/^&gt;\s?/.test(lines[i] ?? '') &&
                    !/^[-*]\s+/.test(lines[i] ?? '') &&
                    !/^\d+\.\s+/.test(lines[i] ?? '')) {
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
    register({ alpine }) {
        alpine.data('hotMarkdownEditor', createHotMarkdownEditor);
    },
};
//# sourceMappingURL=markdown-editor.js.map