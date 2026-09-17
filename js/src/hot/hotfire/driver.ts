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
    html: string;
    snapshot: Snapshot;
}

type Interaction =
    | { name: 'click' | 'poll'; method?: string }
    | { name: 'model'; property: string; value: string };

export function installHotfire(): void {
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
    if (root.dataset['hotBound'] === '1') {
        return;
    }
    root.dataset['hotBound'] = '1';

    const actionUrl = root.dataset['hotAction'];

    root.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement | null)?.closest?.('[data-hot-click]') as HTMLElement | null;
        if (!target || !root.contains(target)) {
            return;
        }
        const method = target.dataset['hotClick'];
        if (method) {
            event.preventDefault();
            void dispatch(root, actionUrl, { name: 'click', method });
        }
    });

    root.addEventListener('change', (event) => {
        const target = event.target as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement | null;
        const property = target?.dataset['hotModel'];
        if (!target || !property || !root.contains(target)) {
            return;
        }
        void dispatch(root, actionUrl, { name: 'model', property, value: target.value });
    });

    root.addEventListener('input', (event) => {
        const target = event.target as HTMLInputElement | HTMLTextAreaElement | null;
        const property = target?.dataset['hotModel'];
        if (!target || !property || !root.contains(target)) {
            return;
        }
        void dispatch(root, actionUrl, { name: 'model', property, value: target.value });
    });

    const pollAttr = root.querySelector<HTMLElement>('[data-hot-poll]')?.dataset['hotPoll'];
    if (pollAttr) {
        const ms = Number.parseInt(pollAttr, 10);
        if (Number.isFinite(ms) && ms > 0) {
            window.setInterval(() => {
                void dispatch(root, actionUrl, { name: 'poll' });
            }, ms);
        }
    }
}

async function dispatch(root: HTMLElement, actionUrl: string | undefined, action: Interaction): Promise<void> {
    if (!actionUrl) {
        return;
    }

    if (action.name === 'model' && action.property) {
        root.setAttribute('data-hot-model-pending', action.property);
    }

    try {
        const response = await fetch(actionUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
        applyResult(root, result);
    } catch (error) {
        console.error('Hot-UI: interaction failed', error);
        root.dataset['hotError'] = String(error);
    } finally {
        root.removeAttribute('data-hot-model-pending');
    }
}

function applyResult(root: HTMLElement, result: ActionResult): void {
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

    morphdom(root, nextRoot, { onNodeAdded: alpinize });
}