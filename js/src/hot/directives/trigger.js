import { keepWired, resolveControl, setAttr, stableIds } from '../dom/wiring.js';
/**
 * x-hot-trigger="{ haspopup: 'menu', controls: $id('hot-content'), state: 'open' }"
 *
 * Mirrors disclosure/popup ARIA onto the REAL control inside a trigger
 * wrapper (usually a display:contents span around a <button>), so screen
 * readers land on the focusable element, not the wrapper. `state: null` opts
 * out of expanded tracking (tooltip/hover-card targets).
 */
export const triggerDirective = (el, { expression }, { evaluate, effect, cleanup }) => {
    const config = expression ? evaluate(expression) : {};
    const tracksState = config.state !== null; // explicit null = opt out
    const stateExpression = config.state || 'open';
    let open = false;
    // The control is re-resolved every pass: an external sync can replace the
    // wrapper's button with a fresh node, and wiring the stale one would leave
    // the live trigger bare.
    const sync = () => {
        const control = resolveControl(el);
        if (!control)
            return;
        if ((config.focusable ?? false) && !control.matches('button, a[href], input, select, textarea, [tabindex]')) {
            control.tabIndex = 0;
        }
        if (config.id && !control.id)
            control.id = config.id;
        if (config.haspopup)
            setAttr(control, 'aria-haspopup', config.haspopup === true ? 'true' : config.haspopup);
        if (config.controls)
            setAttr(control, 'aria-controls', config.controls);
        if (config.labelledby)
            setAttr(control, 'aria-labelledby', config.labelledby);
        if (config.describedby)
            setAttr(control, 'aria-describedby', config.describedby);
        if (!tracksState)
            return;
        setAttr(control, 'aria-expanded', open ? 'true' : 'false');
        setAttr(control, 'data-state', open ? 'open' : 'closed');
    };
    if (tracksState) {
        effect(() => {
            try {
                open = Boolean(evaluate(stateExpression));
            }
            catch {
                // State expression not yet in scope; the next pass retries.
            }
            sync();
        });
    }
    keepWired(el, sync, cleanup);
};
// stableIds is re-exported here so field.ts and labelledby share one mint path.
export { stableIds };
//# sourceMappingURL=trigger.js.map