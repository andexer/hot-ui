import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotDataTable — client-side search / sort / pagination / selection table.
 *
 * Extracted verbatim from the template's inline x-data (plus its x-init
 * watcher, moved into init()). Rows arrive server-rendered as plain objects;
 * every derived list (filtered → sorted → paged) is a getter so Alpine's
 * reactivity recomputes it on demand.
 */

/** One row: an assoc array from PHP, keyed by column key. */
export type DataTableRow = Record<string, unknown>;

export interface DataTableConfig {
    pageSize?: number;
    rows?: DataTableRow[];
    /** Column keys the search box scans; empty/omitted falls back to all keys. */
    searchKeys?: string[];
}

export type SortDirection = 'asc' | 'desc';

/** A row paired with its index in the original `rows` array (stable identity for selection). */
export interface IndexedRow {
    r: DataTableRow;
    i: number;
}

export interface HotDataTableController {
    q: string;
    sortKey: string | null;
    sortDir: SortDirection;
    page: number;
    pageSize: number;
    rows: DataTableRow[];
    searchKeys: string[];
    selected: number[];

    get filtered(): IndexedRow[];
    get sorted(): IndexedRow[];
    get pageCount(): number;
    get paged(): IndexedRow[];
    get pageIndices(): number[];
    get allPageSelected(): boolean;
    toggleSort(key: string): void;
    toggleRow(i: number): void;
    toggleAll(): void;
    next(): void;
    prev(): void;
    init(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface DataTableScope {
    $watch(source: string, callback: (value: unknown) => void): void;
}

type Live = HotDataTableController & DataTableScope;

export function createHotDataTable(config: DataTableConfig = {}): HotDataTableController {
    return {
        q: '',
        sortKey: null,
        sortDir: 'asc',
        page: 1,
        pageSize: config.pageSize ?? 5,
        rows: config.rows ?? [],
        searchKeys: config.searchKeys ?? [],
        selected: [],

        get filtered(): IndexedRow[] {
            const rows = this.rows.map((r, i) => ({ r, i }));
            if (!this.q) return rows;
            const q = this.q.toLowerCase();

            return rows.filter(({ r }) => this.searchKeys.some((k) => String(r[k] ?? '').toLowerCase().includes(q)));
        },
        get sorted(): IndexedRow[] {
            const arr = [...this.filtered];
            const key = this.sortKey;
            if (key !== null && key !== '') {
                const dir = this.sortDir;
                arr.sort((a, b) => {
                    const x = a.r[key] ?? '';
                    const y = b.r[key] ?? '';
                    const num =
                        x !== '' && y !== ''
                        && !isNaN(parseFloat(String(x))) && !isNaN(parseFloat(String(y)));
                    const c = num ? parseFloat(String(x)) - parseFloat(String(y)) : String(x).localeCompare(String(y));

                    return dir === 'asc' ? c : -c;
                });
            }

            return arr;
        },
        get pageCount(): number {
            return Math.max(1, Math.ceil(this.sorted.length / this.pageSize));
        },
        get paged(): IndexedRow[] {
            const s = (this.page - 1) * this.pageSize;

            return this.sorted.slice(s, s + this.pageSize);
        },
        get pageIndices(): number[] {
            return this.paged.map((p) => p.i);
        },
        get allPageSelected(): boolean {
            const idx = this.pageIndices;

            return idx.length > 0 && idx.every((i) => this.selected.includes(i));
        },
        toggleSort(key: string): void {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
            this.page = 1;
        },
        toggleRow(i: number): void {
            if (this.selected.includes(i)) {
                this.selected = this.selected.filter((x) => x !== i);
            } else {
                this.selected.push(i);
            }
        },
        toggleAll(): void {
            const idx = this.pageIndices;
            if (this.allPageSelected) {
                this.selected = this.selected.filter((i) => !idx.includes(i));
            } else {
                idx.forEach((i) => {
                    if (!this.selected.includes(i)) this.selected.push(i);
                });
            }
        },
        next(): void {
            if (this.page < this.pageCount) this.page++;
        },
        prev(): void {
            if (this.page > 1) this.page--;
        },

        init(): void {
            // Reset to the first page whenever the query changes.
            (this as Live).$watch('q', () => {
                this.page = 1;
            });
        },
    };
}

export default {
    name: 'data-table',
    register({ alpine }: HotContext): void {
        alpine.data('hotDataTable', createHotDataTable as never);
    },
} satisfies IslandPlugin;
