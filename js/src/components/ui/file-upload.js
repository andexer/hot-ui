const BYTE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];
export function createHotFileUpload(config) {
    return {
        files: [],
        dragging: false,
        seq: 0,
        disabled: Boolean(config.disabled),
        multiple: Boolean(config.multiple),
        formatBytes(bytes) {
            if (!bytes)
                return '0 B';
            const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), BYTE_UNITS.length - 1);
            const unit = BYTE_UNITS[i] ?? 'B';
            return parseFloat((bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0)) + ' ' + unit;
        },
        addFiles(fileList) {
            if (this.disabled || !fileList || !fileList.length)
                return;
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
            }
            else {
                this.clearAll();
                this.files = batch;
            }
        },
        remove(index) {
            const entry = this.files[index];
            if (!entry)
                return;
            if (entry.url)
                URL.revokeObjectURL(entry.url);
            this.files.splice(index, 1);
            this.syncInput();
        },
        clearAll() {
            this.files.forEach((entry) => {
                if (entry.url)
                    URL.revokeObjectURL(entry.url);
            });
            this.files = [];
        },
        // Keep the native input's FileList equal to what the list shows, so a <form> submits
        // exactly the files the user can still see.
        syncInput() {
            const input = this.$refs['input'];
            if (!input)
                return;
            const dt = new DataTransfer();
            this.files.forEach((entry) => entry.file && dt.items.add(entry.file));
            input.files = dt.files;
        },
        onChange(event) {
            this.addFiles(event.target.files);
        },
        onDrop(event) {
            this.dragging = false;
            if (this.disabled || !event.dataTransfer)
                return;
            // Hand the drop to the real input and let it announce itself. A dropped file that
            // never reaches the input is a file that never uploads and never submits — it only
            // looks selected.
            const dropped = Array.from(event.dataTransfer.files);
            if (!dropped.length)
                return;
            const dt = new DataTransfer();
            (this.multiple ? dropped : dropped.slice(0, 1)).forEach((file) => dt.items.add(file));
            const input = this.$refs['input'];
            if (!input)
                return;
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        open() {
            if (this.disabled)
                return;
            this.$refs['input']?.click();
        },
        destroy() {
            this.files.forEach((f) => f.url && URL.revokeObjectURL(f.url));
        },
    };
}
export default {
    name: 'file-upload',
    register({ alpine }) {
        alpine.data('hotFileUpload', createHotFileUpload);
    },
};
//# sourceMappingURL=file-upload.js.map