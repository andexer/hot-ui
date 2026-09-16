export function createHotDataTable(config = {}) {
    return {
        q: '',
        sortKey: null,
        sortDir: 'asc',
        page: 1,
        pageSize: config.pageSize ?? 5,
        rows: config.rows ?? [],
        searchKeys: config.searchKeys ?? [],
        selected: [],
        get filtered() {
            const rows = this.rows.map((r, i) => ({ r, i }));
            if (!this.q)
                return rows;
            const q = this.q.toLowerCase();
            return rows.filter(({ r }) => this.searchKeys.some((k) => String(r[k] ?? '').toLowerCase().includes(q)));
        },
        get sorted() {
            const arr = [...this.filtered];
            const key = this.sortKey;
            if (key !== null && key !== '') {
                const dir = this.sortDir;
                arr.sort((a, b) => {
                    const x = a.r[key] ?? '';
                    const y = b.r[key] ?? '';
                    const num = x !== '' && y !== ''
                        && !isNaN(parseFloat(String(x))) && !isNaN(parseFloat(String(y)));
                    const c = num ? parseFloat(String(x)) - parseFloat(String(y)) : String(x).localeCompare(String(y));
                    return dir === 'asc' ? c : -c;
                });
            }
            return arr;
        },
        get pageCount() {
            return Math.max(1, Math.ceil(this.sorted.length / this.pageSize));
        },
        get paged() {
            const s = (this.page - 1) * this.pageSize;
            return this.sorted.slice(s, s + this.pageSize);
        },
        get pageIndices() {
            return this.paged.map((p) => p.i);
        },
        get allPageSelected() {
            const idx = this.pageIndices;
            return idx.length > 0 && idx.every((i) => this.selected.includes(i));
        },
        toggleSort(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            }
            else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
            this.page = 1;
        },
        toggleRow(i) {
            if (this.selected.includes(i)) {
                this.selected = this.selected.filter((x) => x !== i);
            }
            else {
                this.selected.push(i);
            }
        },
        toggleAll() {
            const idx = this.pageIndices;
            if (this.allPageSelected) {
                this.selected = this.selected.filter((i) => !idx.includes(i));
            }
            else {
                idx.forEach((i) => {
                    if (!this.selected.includes(i))
                        this.selected.push(i);
                });
            }
        },
        next() {
            if (this.page < this.pageCount)
                this.page++;
        },
        prev() {
            if (this.page > 1)
                this.page--;
        },
        init() {
            // Reset to the first page whenever the query changes.
            this.$watch('q', () => {
                this.page = 1;
            });
        },
    };
}
export default {
    name: 'data-table',
    register({ alpine }) {
        alpine.data('hotDataTable', createHotDataTable);
    },
};
//# sourceMappingURL=data-table.js.map