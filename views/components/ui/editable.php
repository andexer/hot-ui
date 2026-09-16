<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => '',
    'as' => 'input',
    'placeholder' => null,
    'label' => 'value',
    'size' => 'default',
    'id' => null,
]));

$isTextarea = $as === 'textarea';

$fieldSizes = [
    'sm' => 'min-h-8 px-2.5 py-1 text-sm',
    'default' => 'min-h-9 px-3 py-1 text-base md:text-sm',
    'lg' => 'min-h-10 px-4 py-2 text-base',
];
$fieldSize = $fieldSizes[$size] ?? $fieldSizes['default'];

$displaySizes = [
    'sm' => 'px-2.5 py-1 text-sm',
    'default' => 'px-3 py-1 text-base md:text-sm',
    'lg' => 'px-4 py-2 text-base',
];
$displaySize = $displaySizes[$size] ?? $displaySizes['default'];

$fieldBase = 'placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex w-full min-w-0 rounded-md border bg-transparent shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]';

?>
<div
    data-slot="editable"
    x-data="{
        editing: false,
        _model: $hot.model(<?= js((string) $value) ?>),
        get value() { return this._model.value; },
        set value(v) { this._model.value = v; },
        draft: '',
        edit() {
            this.draft = this.value;
            this.editing = true;
            this.$nextTick(() => {
                const el = this.$refs.field;
                if (el) { el.focus(); el.select(); }
            });
        },
        save() {
            this.value = this.draft;
            this.editing = false;
        },
        cancel() {
            this.draft = this.value;
            this.editing = false;
        },
    }"
    <?= $attributes->twMerge('inline-flex w-full max-w-full flex-col items-stretch gap-2') ?>
>
    <?php if ($name): ?>
        <input type="hidden" name="<?= e($name) ?>" :value="value" <?php if ($id): ?>id="<?= e($id) ?>"<?php endif; ?>>
    <?php endif; ?>

    <button
        type="button"
        x-show="!editing"
        @click="edit()"
        aria-label="<?= e('Edit '.$label) ?>"
        class="<?= classes([
            'group hover:bg-muted text-foreground focus-visible:ring-ring/50 inline-flex w-full items-center justify-between gap-2 rounded-md border border-transparent text-start outline-none transition-colors focus-visible:ring-[3px]',
            $displaySize,
        ]) ?>"
    >
        <span class="truncate" :class="value ? '' : 'text-muted-foreground'" x-text="value || <?= js($placeholder ?? '') ?>"></span>
        <i data-lucide="pencil" class="text-muted-foreground size-4 shrink-0 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true"></i>
    </button>

    <?php if ($isTextarea): ?>
        <div x-show="editing" x-cloak class="flex flex-col items-stretch gap-2">
            <textarea
                x-ref="field"
                x-model="draft"
                @keydown.escape.prevent.stop="cancel()"
                aria-label="<?= e($label) ?>"
                <?php if ($placeholder): ?>placeholder="<?= e($placeholder) ?>"<?php endif; ?>
                rows="3"
                class="<?= classes([$fieldBase, 'resize-y field-sizing-content', $fieldSize]) ?>"
            ></textarea>
            <div class="flex items-center justify-end gap-2">
                <button
                    type="button"
                    @click="cancel()"
                    aria-label="Cancel"
                    class="border-input bg-background hover:bg-muted text-foreground focus-visible:ring-ring/50 inline-flex h-8 items-center justify-center gap-1.5 rounded-md border px-3 text-sm font-medium shadow-xs outline-none transition-colors focus-visible:ring-[3px]"
                >
                    <i data-lucide="x" class="size-4" aria-hidden="true"></i>
                    <span>Cancel</span>
                </button>
                <button
                    type="button"
                    @click="save()"
                    aria-label="Save"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-ring/50 inline-flex h-8 items-center justify-center gap-1.5 rounded-md px-3 text-sm font-medium shadow-xs outline-none transition-colors focus-visible:ring-[3px]"
                >
                    <i data-lucide="check" class="size-4" aria-hidden="true"></i>
                    <span>Save</span>
                </button>
            </div>
        </div>
    <?php else: ?>
        <input
            x-show="editing"
            x-cloak
            x-ref="field"
            x-model="draft"
            type="text"
            @keydown.enter.prevent="save()"
            @keydown.escape.prevent.stop="cancel()"
            @blur="editing && save()"
            aria-label="<?= e($label) ?>"
            <?php if ($placeholder): ?>placeholder="<?= e($placeholder) ?>"<?php endif; ?>
            class="<?= classes([$fieldBase, 'h-9', $fieldSize]) ?>"
        />
    <?php endif; ?>
</div>
