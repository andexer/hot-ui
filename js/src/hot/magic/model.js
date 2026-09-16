import { hotClosest } from '../dom/wiring.js';
function resolveScope(el) {
    const host = hotClosest(el, '[x-data]');
    if (!host)
        return null;
    const stack = host._x_dataStack;
    return stack && stack.length > 0 ? stack[stack.length - 1] : null;
}
function readPath(scope, segments) {
    let current = scope;
    for (const key of segments) {
        if (current === null || current === undefined)
            return undefined;
        current = current[key];
    }
    return current;
}
function writePath(scope, segments, next) {
    const last = segments.pop();
    if (!last)
        return;
    let target = scope;
    for (const key of segments) {
        const nextTarget = target[key];
        if (nextTarget === null || nextTarget === undefined) {
            target[key] = {};
        }
        target = target[key];
    }
    target[last] = next;
}
export function createModelMagic(_alpine) {
    return (el) => (fallback = null) => ({
        local: fallback,
        get path() {
            return el.dataset['hotModel'] ?? null;
        },
        get value() {
            const path = this.path;
            if (!path)
                return this.local;
            const scope = resolveScope(el);
            if (!scope)
                return this.local;
            const found = readPath(scope, path.split('.'));
            const local = this.local;
            return found === undefined ? local : found;
        },
        set value(next) {
            this.write(next);
        },
        write(next) {
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
        commit() {
            // Reactivity propagates writes immediately; nothing to flush.
        },
    });
}
//# sourceMappingURL=model.js.map