/**
 * Structural typings for the slice of Alpine's public API Hot-UI consumes.
 * Declared locally (instead of importing internals) so the kernel stays
 * decoupled from Alpine release-to-release churn.
 */

export interface DirectiveScope {
    /** Evaluate an expression string in the element's scope, once. */
    evaluate: (expression: string) => unknown;
    /** Evaluate later / repeatedly; the callback receives the current value. */
    evaluateLater: (expression: string) => (callback: (value: unknown) => void) => void;
    /** Reactive effect bound to the element's scope. */
    effect: (callback: () => void) => void;
    /** Register cleanup for when the element leaves the DOM. */
    cleanup: (callback: () => void) => void;
}

export interface DirectivePayload {
    expression: string;
    modifiers: string[];
}

export type DirectiveHandler = (
    el: HTMLElement,
    directive: DirectivePayload,
    scope: DirectiveScope,
) => void;

export type MagicHandler<T = unknown> = (el: HTMLElement) => T;

export interface DataRegistry {
    (name: string, factory: (config?: unknown) => unknown): void;
}

export interface AlpineLike {
    data: DataRegistry;
    directive(name: string, handler: DirectiveHandler): void;
    magic<T>(name: string, handler: MagicHandler<T>): void;
    store<T extends object>(name: string, value: T): T;
    plugin(plugin: object): void;
}

declare global {
    interface Window {
        Alpine?: AlpineLike & { start?: () => void };
        toast?: ToastFn;
        exportTheme?: () => string;
    }
}

// ---------------------------------------------------------------------------
// window.toast — typed surface for the toast helper installed by hot/globals.
// ---------------------------------------------------------------------------

export interface ToastDetail {
    id?: string;
    title?: string;
    description?: string;
    type?: 'default' | 'success' | 'error' | 'warning' | 'info' | 'loading';
    duration?: number;
}

type ToastInput = string | ToastDetail;

export interface ToastFn {
    (input?: ToastInput): void;
    success(input?: ToastInput): void;
    error(input?: ToastInput): void;
    warning(input?: ToastInput): void;
    info(input?: ToastInput): void;
    loading(input?: ToastInput): void;
    promise(promise: Promise<unknown> | (() => Promise<unknown>), messages?: {
        loading?: string | ((v: unknown) => string);
        success?: string | ((v: unknown) => string);
        error?: string | ((err: unknown) => string);
    }): Promise<unknown>;
}
