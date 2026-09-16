export function hotAccordion(config) {
    const type = config?.type === 'multiple' ? 'multiple' : 'single';
    const seed = config?.value ?? null;
    return {
        type,
        collapsible: config?.collapsible ?? false,
        open: type === 'multiple' ? (Array.isArray(seed) ? seed : []) : (Array.isArray(seed) ? null : seed),
        toggle(v) {
            if (this.type === 'multiple') {
                const list = Array.isArray(this.open) ? this.open : [];
                this.open = list.includes(v) ? list.filter((x) => x !== v) : [...list, v];
                return;
            }
            this.open = this.open === v ? (this.collapsible ? null : this.open) : v;
        },
        isOpen(v) {
            if (this.type === 'multiple') {
                return (Array.isArray(this.open) ? this.open : []).includes(v);
            }
            return !Array.isArray(this.open) && this.open === v;
        },
    };
}
export function hotCollapsible(config) {
    return {
        open: config?.open ?? false,
    };
}
export default {
    name: 'accordion',
    register({ alpine }) {
        alpine.data('hotAccordion', hotAccordion);
        alpine.data('hotCollapsible', hotCollapsible);
    },
};
//# sourceMappingURL=accordion.js.map