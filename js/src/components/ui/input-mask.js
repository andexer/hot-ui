export function createHotInputMask() {
    return {
        apply() {
            const el = this.$el;
            const mask = el.dataset['mask'];
            if (!mask)
                return;
            const v = el.value;
            let out = '';
            let vi = 0;
            for (let mi = 0; mi < mask.length; mi++) {
                if (vi >= v.length)
                    break;
                const m = mask.charAt(mi);
                if (m === '9' || m === 'a' || m === '*') {
                    let ok = false;
                    while (vi < v.length) {
                        const c = v.charAt(vi);
                        vi += 1;
                        const good = m === '9' ? /[0-9]/.test(c) : m === 'a' ? /[a-z]/i.test(c) : /[a-z0-9]/i.test(c);
                        if (good) {
                            out += c;
                            ok = true;
                            break;
                        }
                    }
                    if (!ok)
                        break;
                }
                else {
                    out += m;
                    if (vi < v.length && v.charAt(vi) === m)
                        vi += 1;
                }
            }
            el.value = out;
        },
    };
}
export default {
    name: 'input-mask',
    register({ alpine }) {
        alpine.data('hotInputMask', createHotInputMask);
    },
};
//# sourceMappingURL=input-mask.js.map