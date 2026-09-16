/**
 * Theme store — dark mode + every visual dimension, persisted to localStorage
 * and applied as data-attributes on <html> (css/hot-ui.css keys off them).
 */

export type DarkModePolicy = 'class' | 'system' | false;

export interface ThemeStore {
    darkMode: DarkModePolicy;
    mode: string;
    base: string;
    preset: string;
    radius: string;
    font: string;
    shadow: string;
    spacing: string;
    tracking: string;
    inputStyle: string;
    fontHeading: string;
    readonly isDark: boolean;
    init(): void;
    set(key: string, value: string): void;
    setMode(mode: string): void;
    toggle(): void;
    randomize(): void;
    reset(): void;
    apply(): void;
}

const STORAGE_PREFIX = 'theme:';
const DEFAULTS = {
    base: 'neutral',
    preset: 'default',
    radius: '0.625',
    font: 'sans',
    shadow: 'default',
    spacing: 'default',
    tracking: 'normal',
    inputStyle: 'outline',
    fontHeading: 'sans',
} as const;

const RANDOM_FONTS = [
    'sans', 'inter', 'geist', 'manrope', 'jakarta', 'space-grotesk',
    'dm-sans', 'outfit', 'sora', 'lora', 'source-serif', 'system', 'serif', 'mono',
];

function attr(root: HTMLElement, name: string, value: string, fallback: string): void {
    if (value && value !== fallback) root.setAttribute(name, value);
    else root.removeAttribute(name);
}

export function createThemeStore(darkMode: DarkModePolicy = 'class'): ThemeStore {
    return {
        darkMode,
        mode: localStorage.getItem(`${STORAGE_PREFIX}mode`) ?? 'light',
        ...structuredClone(DEFAULTS),
        // Order note: spread above seeds defaults; explicit reads below keep
        // any persisted user choice.
        base: localStorage.getItem(`${STORAGE_PREFIX}base`) ?? DEFAULTS.base,
        preset: localStorage.getItem(`${STORAGE_PREFIX}preset`) ?? DEFAULTS.preset,
        radius: localStorage.getItem(`${STORAGE_PREFIX}radius`) ?? DEFAULTS.radius,
        font: localStorage.getItem(`${STORAGE_PREFIX}font`) ?? DEFAULTS.font,
        shadow: localStorage.getItem(`${STORAGE_PREFIX}shadow`) ?? DEFAULTS.shadow,
        spacing: localStorage.getItem(`${STORAGE_PREFIX}spacing`) ?? DEFAULTS.spacing,
        tracking: localStorage.getItem(`${STORAGE_PREFIX}tracking`) ?? DEFAULTS.tracking,
        inputStyle: localStorage.getItem(`${STORAGE_PREFIX}inputStyle`) ?? DEFAULTS.inputStyle,
        fontHeading: localStorage.getItem(`${STORAGE_PREFIX}fontHeading`) ?? DEFAULTS.fontHeading,

        get isDark(): boolean {
            if (this.darkMode === false) return false;

            return this.mode === 'dark' || (this.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        },

        init(): void {
            if (!localStorage.getItem(`${STORAGE_PREFIX}mode`)) {
                this.mode = this.darkMode === 'system' ? 'system' : 'light';
            }
            this.apply();
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.mode === 'system') this.apply();
            });
            window.addEventListener('storage', (event) => {
                const key = event.key?.startsWith(STORAGE_PREFIX) ? event.key.slice(STORAGE_PREFIX.length) : null;
                if (!key || !(key in DEFAULTS) && key !== 'mode') return;
                const fallback = key === 'mode'
                    ? (this.darkMode === 'system' ? 'system' : 'light')
                    : (DEFAULTS as Record<string, string>)[key];
                type Writable = keyof ThemeStore;
                (this as unknown as Record<Writable, unknown>)[key as Writable] = event.newValue ?? fallback;
                this.apply();
            });
        },

        set(key: string, value: string): void {
            (this as unknown as Record<string, string>)[key] = value;
            localStorage.setItem(`${STORAGE_PREFIX}${key}`, value);
            this.apply();
        },

        setMode(mode: string): void {
            this.set('mode', mode);
        },

        toggle(): void {
            if (this.darkMode === false) return;
            this.setMode(this.isDark ? 'light' : 'dark');
        },

        randomize(): void {
            const pick = <T>(arr: T[]): T => arr[Math.floor(Math.random() * arr.length)]!;
            const body = pick(RANDOM_FONTS);
            const next: Record<string, string> = {
                base: pick(['neutral', 'stone', 'zinc', 'slate', 'gray', 'mauve', 'olive', 'mist', 'taupe']),
                preset: pick(['default', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose', 'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky']),
                radius: pick(['0', '0.3', '0.5', '0.625', '0.75', '1']),
                inputStyle: pick(['outline', 'fill', 'inset']),
                font: body,
                fontHeading: Math.random() < 0.6 ? 'sans' : pick(RANDOM_FONTS),
                shadow: pick(['none', 'sm', 'default', 'lg', 'xl']),
                spacing: pick(['compact', 'default', 'comfortable']),
                tracking: pick(['tight', 'normal', 'wide']),
            };
            for (const [key, value] of Object.entries(next)) this.set(key, value);
        },

        reset(): void {
            for (const key of ['mode', ...Object.keys(DEFAULTS)]) localStorage.removeItem(`${STORAGE_PREFIX}${key}`);
            this.mode = this.darkMode === 'system' ? 'system' : 'light';
            Object.assign(this, structuredClone(DEFAULTS));
            this.apply();
        },

        apply(): void {
            const root = document.documentElement;
            if (this.darkMode !== false) root.classList.toggle('dark', this.isDark);
            attr(root, 'data-base', this.base, 'neutral');
            attr(root, 'data-theme', this.preset, 'default');
            attr(root, 'data-font', this.font, 'sans');
            attr(root, 'data-shadow', this.shadow, 'default');
            attr(root, 'data-spacing', this.spacing, 'default');
            attr(root, 'data-tracking', this.tracking, 'normal');
            attr(root, 'data-input-style', this.inputStyle, 'outline');
            attr(root, 'data-font-heading', this.fontHeading, 'sans');
            root.setAttribute('data-radius', this.radius);
        },
    };
}
