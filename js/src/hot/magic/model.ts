import { hotClosest } from '../dom/wiring.js';
import type { AlpineLike } from '../types.js';

/**
 * `$hot.model` — the two-way binding behind every value-carrying component.
 *
 * Pure Alpine, no server framework involved. When the caller passes
 * `data-hot-model="property.path"`, that path is resolved against the NEAREST
 * x-data scope (climbing through teleports), read fresh on every access so a
 * swapped scope is followed, and written through Alpine's reactivity on every
 * assignment. Without the attribute the same object simply holds the value
 * locally, so components behave identically unbound.
 *
 * Usage inside a component's x-data:
 *     _model: $hot.model(@js(defaultValue)),
 *     get value() { return this._model.value },
 *     set value(v) { this._model.value = v },
 */

export interface HotModel<T = unknown> {
    /** Local fallback storage when nothing is bound (kept in-scope => reactive). */
    local: T;
    /** The bound property path from data-hot-model, or null. */
    readonly path: string | null;
    get value(): T;
    set value(next: T);
    /** Assign without any side effects beyond reactivity (drag-friendly). */
    write(next: T): void;
    /** Legacy no-op kept so templates calling commit() keep working. */
    commit(): void;
}

interface ScopeOwner extends HTMLElement {
    _x_dataStack?: Array<Record<string, unknown>>;
}

function resolveScope(el: HTMLElement): Record<string, unknown> | null {
    const host = hotClosest(el, '[x-data]') as ScopeOwner | null;
    if (!host) return null;
    const stack = host._x_dataStack;

    return stack && stack.length > 0 ? stack[stack.length - 1]! : null;
}

function readPath(scope: Record<string, unknown>, segments: string[]): unknown {
    let current: unknown = scope;
    for (const key of segments) {
        if (current === null || current === undefined) return undefined;
        current = (current as Record<string, unknown>)[key];
    }

    return current;
}

function writePath(scope: Record<string, unknown>, segments: string[], next: unknown): void {
    const last = segments.pop();
    if (!last) return;
    let target: unknown = scope;
    for (const key of segments) {
        const nextTarget = (target as Record<string, unknown>)[key];
        if (nextTarget === null || nextTarget === undefined) {
            (target as Record<string, unknown>)[key] = {};
        }
        target = (target as Record<string, unknown>)[key];
    }
    (target as Record<string, unknown>)[last] = next;
}

export function createModelMagic(_alpine: AlpineLike): (el: HTMLElement) => (fallback?: unknown) => HotModel {
    return (el: HTMLElement) =>
        (fallback: unknown = null): HotModel => ({
            local: fallback,

            get path(): string | null {
                return el.dataset['hotModel'] ?? null;
            },

            get value(): unknown {
                const path = this.path;
                if (!path) return this.local;
                const scope = resolveScope(el);
                if (!scope) return this.local;
                const found = readPath(scope, path.split('.'));
                const local = this.local;

                return found === undefined ? local : found;
            },

            set value(next: unknown) {
                this.write(next);
            },

            write(next: unknown): void {
                const path = this.path;
                if (!path) {
                    this.local = next;

                    return;
                }
                const scope = resolveScope(el);
                if (!scope) {
                    this.local = next;

                    return;
                }
                writePath(scope, path.split('.'), next);
            },

            commit(): void {
                // Reactivity propagates writes immediately; nothing to flush.
            },
        });
}
