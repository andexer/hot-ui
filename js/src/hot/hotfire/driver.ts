import morphdom from 'morphdom';

/**
 * Reactive driver for server-side components (hot-* → data-hot-*).
 *
 * Responsibilities:
 *   - find every [data-hot-component] root;
 *   - events (click / change / poll) are read from data-hot-* attributes;
 *   - each interaction POSTs the component snapshot + action as JSON to
 *     data-hot-action;
 *   - on response, the root element's subtree is morphed into the fresh HTML
 *     and the new snapshot is re-embedded so the next interaction keeps
 *     working (stateless, Hotfire);
 *   - Alpine trees inside the fragment are re-initialized via initTree().
 *
 * No fetch-and-replace coupling to Alpine: if Alpine is absent the DOM still
 * updates; islands simply run once the page initializes them.
 */

interface Snapshot {
    payload: string;
    checksum: string;
}

interface ActionResult {
    html?: string;
    snapshot?: Snapshot;
    response?: {
        type: 'redirect' | 'flash' | 'download' | 'no-content';
        url?: string;
        message?: string;
        messageType?: string;
        content?: string;
        filename?: string;
        mimeType?: string;
    };
}

type Interaction =
    | { name: 'call' | 'poll' | 'init'; method?: string; params?: unknown[]; target?: string }
    | { name: 'model'; property: string; value: unknown; target?: string; isArray?: boolean; remove?: boolean };

type ModelControl = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
type HotfireHook = (payload: unknown) => void;
type HotfireGlobal = Record<string, HotfireHook[]> & {
    hook?: (name: string, callback: HotfireHook) => () => void;
    debug?: Array<{ name: string; payload: unknown; at: number }>;
};

const timers = new WeakMap<Element, number>();
const boundRoots = new WeakSet<HTMLElement>();
const initializedRoots = new WeakSet<HTMLElement>();
const inflight = new WeakMap<HTMLElement, AbortController>();
const requestIds = new WeakMap<HTMLElement, number>();
const draggedSortItems = new WeakMap<HTMLElement, string>();

export function installHotfire(): void {
    installGlobalApi();

    const handler = (): void => {
        for (const root of Array.from(document.querySelectorAll<HTMLElement>('[data-hot-component]'))) {
            attachRoot(root);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handler);
    } else {
        handler();
    }
}

function attachRoot(root: HTMLElement): void {
    if (boundRoots.has(root)) {
        return;
    }
    boundRoots.add(root);

    const actionUrl = root.dataset['hotAction'];

    root.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement | null)?.closest?.('[data-hot-click]') as HTMLElement | null;
        if (!target || !root.contains(target)) {
            return;
        }
        const method = target.dataset['hotClick'];
        if (method && confirmAction(target)) {
            event.preventDefault();
            void dispatch(root, actionUrl, { name: 'call', method, target: actionTarget(target, method) });
        }
    });

    root.addEventListener('change', (event) => {
        const target = event.target as ModelControl | null;
        if (!target || !root.contains(target)) {
            return;
        }
        markDirty(root, target);

        const method = target.dataset['hotChange'];
        if (method && confirmAction(target)) {
            void dispatch(root, actionUrl, { name: 'call', method, target: actionTarget(target, method) });
            return;
        }

        queueModelDispatch(root, actionUrl, target, 'change');
    });

    root.addEventListener('input', (event) => {
        const target = event.target as ModelControl | null;
        if (!target || !root.contains(target)) {
            return;
        }
        markDirty(root, target);
        queueModelDispatch(root, actionUrl, target, 'input');
    });

    root.addEventListener('blur', (event) => {
        const target = event.target as ModelControl | null;
        if (!target || !root.contains(target)) {
            return;
        }
        queueModelDispatch(root, actionUrl, target, 'blur');
    }, true);

    root.addEventListener('submit', (event) => {
        const form = event.target as HTMLFormElement | null;
        const method = form?.dataset['hotSubmit'];
        if (!form || !method || !root.contains(form) || !confirmAction(form)) {
            return;
        }
        event.preventDefault();
        void dispatch(root, actionUrl, { name: 'call', method, target: actionTarget(form, method) });
    });

    root.addEventListener('keydown', (event) => {
        const target = (event.target as HTMLElement | null)?.closest?.('[data-hot-key]') as HTMLElement | null;
        const method = target?.dataset['hotKey'];
        if (!target || !method || !root.contains(target) || !matchesKey(event, modifiers(target, 'key'))) {
            return;
        }
        if (modifiers(target, 'key').includes('prevent')) {
            event.preventDefault();
        }
        if (modifiers(target, 'key').includes('stop')) {
            event.stopPropagation();
        }
        if (confirmAction(target)) {
            void dispatch(root, actionUrl, { name: 'call', method, target: actionTarget(target, method) });
        }
    });

    root.addEventListener('dragstart', (event) => {
        const item = (event.target as HTMLElement | null)?.closest?.('[data-hot-sort-item]') as HTMLElement | null;
        const list = item?.closest?.('[data-hot-sort]') as HTMLElement | null;
        if (!item || !list || !root.contains(list)) {
            return;
        }
        const id = item.dataset['hotSortItem'];
        if (id) {
            draggedSortItems.set(list, id);
            event.dataTransfer?.setData('text/plain', id);
        }
    });

    root.addEventListener('dragover', (event) => {
        const item = (event.target as HTMLElement | null)?.closest?.('[data-hot-sort-item]');
        if (item) {
            event.preventDefault();
        }
    });

    root.addEventListener('drop', (event) => {
        const item = (event.target as HTMLElement | null)?.closest?.('[data-hot-sort-item]') as HTMLElement | null;
        const list = item?.closest?.('[data-hot-sort]') as HTMLElement | null;
        const method = list?.dataset['hotSort'];
        const from = list ? draggedSortItems.get(list) : null;
        const to = item?.dataset['hotSortItem'];
        if (!list || !method || !from || !to || from === to || !root.contains(list)) {
            return;
        }
        event.preventDefault();
        void dispatch(root, actionUrl, {
            name: 'call',
            method,
            params: [{ from, to }],
            target: actionTarget(list, method),
        });
    });

    const pollAttr = root.dataset['hotPoll'] ?? root.querySelector<HTMLElement>('[data-hot-poll]')?.dataset['hotPoll'];
    if (pollAttr) {
        const ms = Number.parseInt(pollAttr, 10);
        if (Number.isFinite(ms) && ms > 0) {
            const existing = timers.get(root);
            if (existing !== undefined) {
                window.clearInterval(existing);
            }
            const timerId = window.setInterval(() => {
                void dispatch(root, actionUrl, { name: 'poll', target: 'poll' });
            }, ms);
            timers.set(root, timerId);
        }
    }

    if (!initializedRoots.has(root)) {
        initializedRoots.add(root);
        for (const element of Array.from(root.querySelectorAll<HTMLElement>('[data-hot-init]'))) {
            const method = element.dataset['hotInit'];
            if (method) {
                void dispatch(root, actionUrl, { name: 'call', method, target: actionTarget(element, method) });
            }
        }
    }

    installLazy(root, actionUrl);
    syncDirty(root);
}

async function dispatch(root: HTMLElement, actionUrl: string | undefined, action: Interaction): Promise<void> {
    if (!actionUrl) {
        return;
    }

    const target = action.target ?? (action.name === 'model' ? action.property : action.method ?? action.name);
    beginRequest(root, target);
    inflight.get(root)?.abort();
    const controller = new AbortController();
    inflight.set(root, controller);
    const requestId = (requestIds.get(root) ?? 0) + 1;
    requestIds.set(root, requestId);

    try {
        const detail = { action, target, root };
        root.dispatchEvent(new CustomEvent('hotfire:request', { bubbles: true, detail }));
        callHook('beforeRequest', detail);
        const response = await fetch(actionUrl, {
            method: 'POST',
            headers: requestHeaders(root),
            signal: controller.signal,
            body: JSON.stringify({
                snapshot: {
                    payload: root.dataset['hotSnapshot'] ?? '',
                    checksum: root.dataset['hotChecksum'] ?? '',
                },
                action,
            }),
        });

        if (!response.ok) {
            throw new Error(`Hot-UI: HTTP ${response.status}`);
        }

        const result = (await response.json()) as ActionResult;
        if (requestIds.get(root) !== requestId) {
            return;
        }
        callHook('afterResponse', { action, target, root, result });
        handleResponse(root, result);
        root.dispatchEvent(new CustomEvent('hotfire:success', { bubbles: true, detail: { action, target, result } }));
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return;
        }
        console.error('Hot-UI: interaction failed', error);
        root.dataset['hotError'] = String(error);
        root.dispatchEvent(new CustomEvent('hotfire:error', { bubbles: true, detail: { action, target, error } }));
        callHook('error', { action, target, root, error });
    } finally {
        if (inflight.get(root) === controller) {
            inflight.delete(root);
        }
        endRequest(root, target);
        root.dispatchEvent(new CustomEvent('hotfire:finish', { bubbles: true, detail: { action, target } }));
    }
}

function applyResult(root: HTMLElement, result: ActionResult): void {
    if (!result.html) {
        return;
    }

    const next = document.createElement('div');
    next.innerHTML = result.html;
    const nextRoot = next.firstElementChild as HTMLElement | null;
    if (!nextRoot) {
        return;
    }

    const alpinize = (): void => {
        const alpine = (window as unknown as { Alpine?: { initTree?: (node: Node) => void } }).Alpine;
        alpine?.initTree?.(root);
    };

    callHook('beforeMorph', { root, nextRoot, result });
    morphdom(root, nextRoot, { onNodeAdded: alpinize });
    callHook('afterMorph', { root, result });
    attachRoot(root);
    syncDirty(root);
}

function handleResponse(root: HTMLElement, result: ActionResult): void {
    if (result.response) {
        if (result.response.type === 'redirect' && result.response.url) {
            window.location.assign(result.response.url);
            return;
        }
        if (result.response.type === 'flash') {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: result.response.messageType ?? 'info', title: result.response.message ?? '' },
            }));
        }
        if (result.response.type === 'download' && result.response.content && result.response.filename) {
            download(result.response.content, result.response.filename, result.response.mimeType ?? 'application/octet-stream');
        }
        if (result.response.type === 'no-content') {
            return;
        }
    }

    applyResult(root, result);
}

function queueModelDispatch(root: HTMLElement, actionUrl: string | undefined, target: ModelControl, source: 'input' | 'change' | 'blur'): void {
    const property = target.dataset['hotModel'];
    if (!property) {
        return;
    }

    const mods = modifiers(target, 'model');
    if (mods.includes('defer')) {
        return;
    }
    if (mods.includes('lazy') && source !== 'change') {
        return;
    }
    if (mods.includes('blur') && source !== 'blur') {
        return;
    }
    if (!mods.includes('live') && !mods.includes('debounce') && source === 'input' && !isTextInput(target)) {
        return;
    }

    const run = (): void => {
        const isArray = target.hasAttribute('data-hot-model-array');
        const isCheckbox = target instanceof HTMLInputElement && target.type === 'checkbox';
        const val = isArray && isCheckbox ? target.value : valueOf(target);
        void dispatch(root, actionUrl, {
            name: 'model',
            property,
            value: val,
            target: actionTarget(target, property),
            ...(isArray ? { isArray: true, remove: isCheckbox ? !target.checked : false } : {}),
        });
    };

    const delay = debounceDelay(mods);
    if (delay > 0) {
        window.clearTimeout(timers.get(target));
        timers.set(target, window.setTimeout(run, delay));
        return;
    }

    run();
}

function requestHeaders(root: HTMLElement): Record<string, string> {
    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    const csrf = root.dataset['hotCsrf'] ?? document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
    if (csrf) {
        const headerName = document.querySelector<HTMLMetaElement>('meta[name="csrf-header"]')?.content ?? 'X-CSRF-TOKEN';
        headers[headerName] = csrf;
    }
    return headers;
}

function installLazy(root: HTMLElement, actionUrl: string | undefined): void {
    const elements = Array.from(root.querySelectorAll<HTMLElement>('[data-hot-lazy]'));
    if (elements.length === 0) {
        return;
    }

    const run = (element: HTMLElement): void => {
        if (element.dataset['hotLazyLoaded'] === '1') {
            return;
        }
        element.dataset['hotLazyLoaded'] = '1';
        const method = element.dataset['hotLazy'];
        if (method) {
            void dispatch(root, actionUrl, { name: 'call', method, target: actionTarget(element, method) });
        }
    };

    if (!('IntersectionObserver' in window)) {
        elements.forEach(run);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (! entry.isIntersecting) {
                continue;
            }
            observer.unobserve(entry.target);
            run(entry.target as HTMLElement);
        }
    });

    elements.forEach((element) => observer.observe(element));
}

function download(content: string, filename: string, mimeType: string): void {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.hidden = true;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function callHook(name: string, payload: unknown): void {
    const registry = (window as unknown as { Hotfire?: HotfireGlobal }).Hotfire;
    registry?.debug?.push({ name, payload, at: Date.now() });
    for (const hook of registry?.[name] ?? []) {
        hook(payload);
    }
}

function installGlobalApi(): void {
    const target = window as unknown as { Hotfire?: HotfireGlobal };
    target.Hotfire ??= { debug: [] } as HotfireGlobal;
    target.Hotfire.debug ??= [];
    target.Hotfire.hook ??= (name: string, callback: HotfireHook): (() => void) => {
        target.Hotfire ??= { debug: [] } as HotfireGlobal;
        target.Hotfire[name] ??= [];
        target.Hotfire[name].push(callback);

        return () => {
            const hooks = target.Hotfire?.[name] ?? [];
            const index = hooks.indexOf(callback);
            if (index >= 0) {
                hooks.splice(index, 1);
            }
        };
    };
}

function beginRequest(root: HTMLElement, target: string): void {
    root.dataset['hotLoading'] = 'true';
    root.dataset['hotTarget'] = target;
    updateTargeted(root, 'loading', target, true);
}

function endRequest(root: HTMLElement, target: string): void {
    root.removeAttribute('data-hot-loading');
    root.removeAttribute('data-hot-target');
    updateTargeted(root, 'loading', target, false);
}

function syncDirty(root: HTMLElement): void {
    for (const control of Array.from(root.querySelectorAll<ModelControl>('[data-hot-model]'))) {
        control.dataset['hotOriginalValue'] = serialiseValue(valueOf(control));
        markDirty(root, control);
    }
}

function markDirty(root: HTMLElement, control: ModelControl): void {
    const property = control.dataset['hotModel'];
    if (!property) {
        return;
    }
    const dirty = control.dataset['hotOriginalValue'] !== serialiseValue(valueOf(control));
    control.toggleAttribute('data-hot-dirty-active', dirty);
    updateTargeted(root, 'dirty', property, dirty);
}

function updateTargeted(root: HTMLElement, kind: 'loading' | 'dirty', target: string, active: boolean): void {
    for (const element of Array.from(root.querySelectorAll<HTMLElement>(`[data-hot-${kind}]`))) {
        const expected = element.dataset[`hot${capitalize(kind)}`] ?? '';
        if (expected !== '' && expected !== target) {
            continue;
        }
        element.toggleAttribute(`data-hot-${kind}-active`, active);
        if (kind === 'loading') {
            element.setAttribute('aria-busy', active ? 'true' : 'false');
        }
    }
}

function actionTarget(element: HTMLElement, fallback: string): string {
    return element.dataset['hotTarget'] || fallback;
}

function confirmAction(element: HTMLElement): boolean {
    const message = element.dataset['hotConfirm'];
    return !message || window.confirm(message);
}

function modifiers(element: HTMLElement, name: string): string[] {
    return (element.dataset[`hot${capitalize(name)}Modifiers`] ?? '').split(/\s+/).filter(Boolean);
}

function matchesKey(event: KeyboardEvent, mods: string[]): boolean {
    const keys = mods.filter((mod) => !['prevent', 'stop', 'once', 'debounce', 'live'].includes(mod));
    if (keys.length === 0) {
        return true;
    }
    const key = event.key.toLowerCase();
    return keys.some((candidate) => {
        if (candidate === 'enter') return key === 'enter';
        if (candidate === 'escape' || candidate === 'esc') return key === 'escape';
        if (candidate === 'space') return key === ' ';
        return key === candidate.toLowerCase();
    });
}

function debounceDelay(mods: string[]): number {
    const explicit = mods.find((mod) => /^\d+ms$/.test(mod));
    if (explicit) {
        return Number.parseInt(explicit, 10);
    }
    if (mods.includes('debounce')) {
        return 150;
    }
    if (mods.includes('live')) {
        return 150;
    }
    return 0;
}

function valueOf(control: ModelControl): unknown {
    if (control instanceof HTMLInputElement && control.type === 'checkbox') {
        return control.checked;
    }
    if (control instanceof HTMLSelectElement && control.multiple) {
        return Array.from(control.selectedOptions).map((option) => option.value);
    }
    return control.value;
}

function serialiseValue(value: unknown): string {
    return JSON.stringify(value);
}

function isTextInput(control: ModelControl): boolean {
    return control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement;
}

function capitalize(value: string): string {
    return value.charAt(0).toUpperCase() + value.slice(1);
}
