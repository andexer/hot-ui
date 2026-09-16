/**
 * window.toast + window.exportTheme — the two page-level globals Hot-UI ships.
 * toast() dispatches DOM events the sonner toaster listens for; exportTheme()
 * resolves the live CSS tokens into a self-contained stylesheet.
 */
function normaliseDetail(input) {
    if (typeof input === 'string')
        return { title: input };
    if (!input)
        return {};
    return { ...input };
}
export function installToast() {
    let promiseCounter = 0;
    const toast = ((input) => {
        window.dispatchEvent(new CustomEvent('toast', { detail: normaliseDetail(input) }));
    });
    for (const kind of ['success', 'error', 'warning', 'info']) {
        toast[kind] = (input) => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { ...normaliseDetail(input), type: kind },
            }));
        };
    }
    // A persistent loading toast (no auto-dismiss).
    toast.loading = (input) => {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { duration: Number.POSITIVE_INFINITY, ...normaliseDetail(input), type: 'loading' },
        }));
    };
    // Promise toast: shows `loading`, then swaps to success/error on settle.
    toast.promise = (promise, messages = {}) => {
        const id = `hot-tp-${String(++promiseCounter)}`;
        const text = (message, value) => typeof message === 'function' ? String(message(value)) : String(message ?? '');
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { id, type: 'loading', title: text(messages.loading) || 'Loading…', duration: Number.POSITIVE_INFINITY },
        }));
        return Promise.resolve(typeof promise === 'function' ? promise() : promise)
            .then((data) => {
            window.dispatchEvent(new CustomEvent('toast-update', {
                detail: { id, type: 'success', title: text(messages.success, data) || 'Success', duration: 4000 },
            }));
            return data;
        })
            .catch((err) => {
            window.dispatchEvent(new CustomEvent('toast-update', {
                detail: { id, type: 'error', title: text(messages.error, err) || 'Error', duration: 4000 },
            }));
            throw err;
        });
    };
    window.toast = toast;
}
// ---------------------------------------------------------------------------
// Theme export — resolves the CURRENT customization into a complete
// css/hot-ui.css the developer can paste as-is. Mirrors the token list below;
// if you add a token to the stylesheet, mirror it in THEME_TOKENS.
// ---------------------------------------------------------------------------
const THEME_SCAFFOLD = `@import 'tailwindcss';

@source '../views';

@custom-variant dark (&:is(.dark *));

@theme inline {
    /* Fonts */
    --font-sans: var(--font-sans);
    --font-serif: var(--font-serif);
    --font-mono: var(--font-mono);
    --font-heading: var(--font-heading);

    /* Radius scale (derived from --radius) */
    --radius-sm: calc(var(--radius) - 4px);
    --radius-md: calc(var(--radius) - 2px);
    --radius-lg: var(--radius);
    --radius-xl: calc(var(--radius) + 4px);

    /* Spacing base */
    --spacing: var(--spacing);

    /* Letter-spacing scale (derived from --tracking-normal) */
    --tracking-tighter: calc(var(--tracking-normal) - 0.05em);
    --tracking-tight: calc(var(--tracking-normal) - 0.025em);
    --tracking-normal: var(--tracking-normal);
    --tracking-wide: calc(var(--tracking-normal) + 0.025em);
    --tracking-wider: calc(var(--tracking-normal) + 0.05em);
    --tracking-widest: calc(var(--tracking-normal) + 0.1em);

    /* Box-shadow scale */
    --shadow-2xs: var(--shadow-2xs);
    --shadow-xs: var(--shadow-xs);
    --shadow-sm: var(--shadow-sm);
    --shadow: var(--shadow);
    --shadow-md: var(--shadow-md);
    --shadow-lg: var(--shadow-lg);
    --shadow-xl: var(--shadow-xl);
    --shadow-2xl: var(--shadow-2xl);

    /* Colors */
    --color-background: var(--background);
    --color-foreground: var(--foreground);
    --color-card: var(--card);
    --color-card-foreground: var(--card-foreground);
    --color-popover: var(--popover);
    --color-popover-foreground: var(--popover-foreground);
    --color-primary: var(--primary);
    --color-primary-foreground: var(--primary-foreground);
    --color-secondary: var(--secondary);
    --color-secondary-foreground: var(--secondary-foreground);
    --color-muted: var(--muted);
    --color-muted-foreground: var(--muted-foreground);
    --color-accent: var(--accent);
    --color-accent-foreground: var(--accent-foreground);
    --color-destructive: var(--destructive);
    --color-destructive-foreground: var(--destructive-foreground);
    --color-success: var(--success);
    --color-success-foreground: var(--success-foreground);
    --color-warning: var(--warning);
    --color-warning-foreground: var(--warning-foreground);
    --color-info: var(--info);
    --color-info-foreground: var(--info-foreground);
    --color-border: var(--border);
    --color-input: var(--input);
    --color-ring: var(--ring);
    --color-chart-1: var(--chart-1);
    --color-chart-2: var(--chart-2);
    --color-chart-3: var(--chart-3);
    --color-chart-4: var(--chart-4);
    --color-chart-5: var(--chart-5);
    --color-sidebar: var(--sidebar);
    --color-sidebar-foreground: var(--sidebar-foreground);
    --color-sidebar-primary: var(--sidebar-primary);
    --color-sidebar-primary-foreground: var(--sidebar-primary-foreground);
    --color-sidebar-accent: var(--sidebar-accent);
    --color-sidebar-accent-foreground: var(--sidebar-accent-foreground);
    --color-sidebar-border: var(--sidebar-border);
    --color-sidebar-ring: var(--sidebar-ring);

    --animate-caret-blink: caret-blink 1.25s ease-out infinite;

    @keyframes caret-blink {
        0%, 70%, 100% { opacity: 1; }
        20%, 50% { opacity: 0; }
    }

    --animate-progress-indeterminate: progress-indeterminate 1.4s ease-in-out infinite;

    @keyframes progress-indeterminate {
        0% { left: -40%; }
        100% { left: 100%; }
    }
}`;
const THEME_TOKENS = [
    '--radius', '--spacing', '--tracking-normal',
    '--font-sans', '--font-serif', '--font-mono', '--font-heading',
    '--shadow-2xs', '--shadow-xs', '--shadow-sm', '--shadow',
    '--shadow-md', '--shadow-lg', '--shadow-xl', '--shadow-2xl',
    '--background', '--foreground',
    '--card', '--card-foreground',
    '--popover', '--popover-foreground',
    '--primary', '--primary-foreground',
    '--secondary', '--secondary-foreground',
    '--muted', '--muted-foreground',
    '--accent', '--accent-foreground',
    '--destructive', '--destructive-foreground',
    '--success', '--success-foreground',
    '--warning', '--warning-foreground',
    '--info', '--info-foreground',
    '--border', '--input', '--ring',
    '--chart-1', '--chart-2', '--chart-3', '--chart-4', '--chart-5',
    '--sidebar', '--sidebar-foreground', '--sidebar-primary', '--sidebar-primary-foreground',
    '--sidebar-accent', '--sidebar-accent-foreground', '--sidebar-border', '--sidebar-ring',
];
function readTokens() {
    const style = getComputedStyle(document.documentElement);
    const out = {};
    for (const token of THEME_TOKENS) {
        const value = style.getPropertyValue(token).trim();
        if (value !== '')
            out[token] = value;
    }
    // computed styles resolve var() — re-tie heading to body so editing
    // --font-sans keeps moving headings after an export/import cycle.
    if (out['--font-heading'] === out['--font-sans'])
        out['--font-heading'] = 'var(--font-sans)';
    return out;
}
export function installThemeExporter() {
    window.exportTheme = () => {
        const root = document.documentElement;
        const wasDark = root.classList.contains('dark');
        root.classList.remove('dark');
        const light = readTokens();
        root.classList.add('dark');
        const dark = readTokens();
        root.classList.toggle('dark', wasDark);
        const fmt = (tokens, keys) => keys.filter((token) => tokens[token]).map((token) => `  ${token}: ${tokens[token]};`).join('\n');
        const darkKeys = THEME_TOKENS.filter((token) => dark[token] && dark[token] !== light[token]);
        return `${THEME_SCAFFOLD}\n\n:root {\n${fmt(light, [...THEME_TOKENS])}\n}\n\n.dark {\n${fmt(dark, darkKeys)}\n}\n`;
    };
}
//# sourceMappingURL=globals.js.map