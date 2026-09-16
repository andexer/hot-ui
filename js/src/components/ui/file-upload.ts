import type { HotContext, IslandPlugin } from '../../hot/plugin.js';

/**
 * hotFileUpload — dropzone + selected-file list over a real hidden <input>.
 *
 * Extracted verbatim from the template's inline x-data. The native input stays
 * the source of truth for forms: drops are handed to it and its FileList is
 * re-synced whenever the visual list changes, so a <form> submits exactly the
 * files the user still sees.
 */

export interface FileUploadConfig {
    disabled?: boolean;
    multiple?: boolean;
}

/** One row in the visual list mirroring the native input's FileList. */
export interface FileUploadEntry {
    id: number;
    file: File;
    name: string;
    size: number;
    type: string;
    /** Object URL for image previews; null otherwise (revoked on removal/destroy). */
    url: string | null;
}

export interface FileUploadController {
    files: FileUploadEntry[];
    dragging: boolean;
    seq: number;
    disabled: boolean;
    multiple: boolean;

    formatBytes(bytes: number): string;
    addFiles(fileList: FileList | null | undefined): void;
    remove(index: number): void;
    clearAll(): void;
    syncInput(): void;
    onChange(event: Event): void;
    onDrop(event: DragEvent): void;
    open(): void;
    destroy(): void;
}

/** Alpine-injected magics this controller touches on the live proxy. */
interface FileUploadScope {
    $refs: Record<string, HTMLElement>;
}

type Live = FileUploadController & FileUploadScope;

const BYTE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'] as const;

export function createHotFileUpload(config: FileUploadConfig): FileUploadController {
    return {
        files: [],
        dragging: false,
        seq: 0,
        disabled: Boolean(config.disabled),
        multiple: Boolean(config.multiple),

        formatBytes(bytes: number): string {
            if (!bytes) return '0 B';
            const i = Math.min(
                Math.floor(Math.log(bytes) / Math.log(1024)),
                BYTE_UNITS.length - 1,
            );
            const unit = BYTE_UNITS[i] ?? 'B';

            return parseFloat((bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0)) + ' ' + unit;
        },

        addFiles(fileList: FileList | null | undefined): void {
            if (this.disabled || !fileList || !fileList.length) return;
            const batch = Array.from(fileList).map((file) => ({
                id: ++this.seq,
                file,
                name: file.name,
                size: file.size,
                type: file.type,
                url: file.type && file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
            }));
            // A single-file field holds one selection: the new pick replaces the old one, the
            // way the native input it wraps does.
            if (this.multiple) {
                this.files.push(...batch);
            } else {
                this.clearAll();
                this.files = batch;
            }
        },

        remove(index: number): void {
            const entry = this.files[index];
            if (!entry) return;
            if (entry.url) URL.revokeObjectURL(entry.url);
            this.files.splice(index, 1);
            this.syncInput();
        },

        clearAll(): void {
            this.files.forEach((entry) => {
                if (entry.url) URL.revokeObjectURL(entry.url);
            });
            this.files = [];
        },

        // Keep the native input's FileList equal to what the list shows, so a <form> submits
        // exactly the files the user can still see.
        syncInput(): void {
            const input = (this as Live).$refs['input'] as HTMLInputElement | undefined;
            if (!input) return;
            const dt = new DataTransfer();
            this.files.forEach((entry) => entry.file && dt.items.add(entry.file));
            input.files = dt.files;
        },

        onChange(event: Event): void {
            this.addFiles((event.target as HTMLInputElement).files);
        },

        onDrop(event: DragEvent): void {
            this.dragging = false;
            if (this.disabled || !event.dataTransfer) return;
            // Hand the drop to the real input and let it announce itself. A dropped file that
            // never reaches the input is a file that never uploads and never submits — it only
            // looks selected.
            const dropped = Array.from(event.dataTransfer.files);
            if (!dropped.length) return;
            const dt = new DataTransfer();
            (this.multiple ? dropped : dropped.slice(0, 1)).forEach((file) => dt.items.add(file));
            const input = (this as Live).$refs['input'] as HTMLInputElement | undefined;
            if (!input) return;
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        open(): void {
            if (this.disabled) return;
            ((this as Live).$refs['input'] as HTMLInputElement | undefined)?.click();
        },

        destroy(): void {
            this.files.forEach((f) => f.url && URL.revokeObjectURL(f.url));
        },
    };
}

export default {
    name: 'file-upload',
    register({ alpine }: HotContext): void {
        alpine.data('hotFileUpload', createHotFileUpload as never);
    },
} satisfies IslandPlugin;
