<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'multiple' => false,
    'accept' => null,
    'maxSizeLabel' => null,
    'id' => null,
    'disabled' => false,
]));

$fieldId = $id ?: ('file-upload-'.bin2hex(random_bytes(3)));

$hintBits = array_filter([
    $accept ? trim($accept) : null,
    $maxSizeLabel,
]);
$hint = $hintBits ? implode(' · ', $hintBits) : null;

$dropzoneLabel = $multiple
    ? 'Upload files — drag and drop, or activate to browse'
    : 'Upload a file — drag and drop, or activate to browse';
?>
<div
    data-slot="file-upload"
    x-data="hotFileUpload({
        disabled: <?= js((bool) $disabled) ?>,
        multiple: <?= js((bool) $multiple) ?>,
    })"
    <?= $attributes->twMerge('flex w-full flex-col gap-3') ?>
>
    <input
        x-ref="input"
        type="file"
        id="<?= e($fieldId) ?>"
        <?php if ($name): ?>name="<?= e($name).($multiple ? '[]' : '') ?>"
        <?php endif; ?>
        <?php if ($accept): ?>accept="<?= e($accept) ?>"
        <?php endif; ?>
        <?php if ($multiple): ?>multiple
        <?php endif; ?>
        <?php if ($disabled): ?>disabled
        <?php endif; ?>
        @change="onChange($event)"
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
    />

    <div
        data-slot="file-upload-dropzone"
        role="button"
        :tabindex="disabled ? -1 : 0"
        :aria-disabled="disabled"
        aria-label="<?= e($dropzoneLabel) ?>"
        @click="open()"
        @keydown.enter.prevent="open()"
        @keydown.space.prevent="open()"
        @dragover.prevent="!disabled && (dragging = true)"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop($event)"
        :class="dragging ? 'bg-muted ring-ring/50 ring-[3px] border-ring' : ''"
        class="border-input flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-transparent px-6 py-8 text-center transition-[color,background-color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-disabled:pointer-events-none aria-disabled:opacity-50"
    >
        <span class="bg-muted text-muted-foreground flex size-10 items-center justify-center rounded-full">
            <i data-lucide="cloud-upload" class="size-5" aria-hidden="true"></i>
        </span>
        <p class="text-foreground text-sm font-medium">
            Drag &amp; drop files here, or click to browse
        </p>
        <?php if ($hint): ?>
            <p class="text-muted-foreground text-xs"><?= e($hint) ?></p>
        <?php endif; ?>
    </div>

    <ul x-show="files.length" x-cloak class="flex flex-col gap-2" role="list">
        <template x-for="(file, index) in files" :key="file.id">
            <li
                data-slot="file-upload-item"
                class="bg-card flex items-center gap-3 rounded-lg border p-2.5 shadow-xs"
            >
                <template x-if="file.url">
                    <img :src="file.url" :alt="file.name" class="size-10 shrink-0 rounded-md object-cover" />
                </template>
                <template x-if="!file.url">
                    <span class="bg-muted text-muted-foreground flex size-10 shrink-0 items-center justify-center rounded-md">
                        <i data-lucide="file" class="size-5" aria-hidden="true"></i>
                    </span>
                </template>

                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-foreground truncate text-sm font-medium" x-text="file.name"></span>
                        <span class="text-muted-foreground shrink-0 text-xs tabular-nums" x-text="formatBytes(file.size)"></span>
                    </div>
                </div>

                <button
                    type="button"
                    @click="remove(index)"
                    :aria-label="`Remove ${file.name}`"
                    class="text-muted-foreground hover:text-foreground hover:bg-muted focus-visible:ring-ring/50 flex size-8 shrink-0 items-center justify-center rounded-md outline-none transition-colors focus-visible:ring-[3px]"
                >
                    <i data-lucide="x" class="size-4" aria-hidden="true"></i>
                </button>
            </li>
        </template>
    </ul>
</div>
