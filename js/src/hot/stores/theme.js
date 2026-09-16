/**
 * Theme store — dark mode + every visual dimension, persisted to localStorage
 * and applied as data-attributes on <html> (css/hot-ui.css keys off them).
 */
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
};
const RANDOM_FONTS = [
    'sans', 'inter', 'geist', 'manrope', 'jakarta', 'space-grotesk',
    'dm-sans', 'outfit', 'sora', 'lora', 'source-serif', 'system', 'serif', 'mono',
];
function attr(root, name, value, fallback) {
    if (value && value !== fallback)
        root.setAttribute(name, value);
    else
        root.removeAttribute(name);
}
export function createThemeStore(darkMode = 'class') {
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
        get isDark() {
            if (this.darkMode === false)
                return false;
            return this.mode === 'dark' || (this.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        },
        init() {
            if (!localStorage.getItem(`${STORAGE_PREFIX}mode`)) {
                this.mode = this.darkMode === 'system' ? 'system' : 'light';
            }
            this.apply();
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.mode === 'system')
                    this.apply();
            });
            window.addEventListener('storage', (event) => {
                const key = event.key?.startsWith(STORAGE_PREFIX) ? event.key.slice(STORAGE_PREFIX.length) : null;
                if (!key || !(key in DEFAULTS) && key !== 'mode')
                    return;
                const fallback = key === 'mode'
                    ? (this.darkMode === 'system' ? 'system' : 'light')
                    : DEFAULTS[key];
                this[key] = event.newValue ?? fallback;
                this.apply();
            });
        },
        set(key, value) {
            this[key] = value;
            localStorage.setItem(`${STORAGE_PREFIX}${key}`, value);
            this.apply();
        },
        setMode(mode) {
            this.set('mode', mode);
        },
        toggle() {
            if (this.darkMode === false)
                return;
            this.setMode(this.isDark ? 'light' : 'dark');
        },
        randomize() {
            const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
            const body = pick(RANDOM_FONTS);
            const next = {
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
            for (const [key, value] of Object.entries(next))
                this.set(key, value);
        },
        reset() {
            for (const key of ['mode', ...Object.keys(DEFAULTS)])
                localStorage.removeItem(`${STORAGE_PREFIX}${key}`);
            this.mode = this.darkMode === 'system' ? 'system' : 'light';
            Object.assign(this, structuredClone(DEFAULTS));
            this.apply();
        },
        apply() {
            const root = document.documentElement;
            if (this.darkMode !== false)
                root.classList.toggle('dark', this.isDark);
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
//# sourceMappingURL=theme.js.map