<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => [],
    'placeholder' => 'Add tag…',
    'max' => null,
    'disabled' => false,
    'id' => null,
]));

?>
<div
    data-slot="tags-input"
x-data="hotTagsInput({
        model: $hot.model(<?= js(array_values((array) $value)) ?>),
        max: <?= js($max !== null ? (int) $max : null) ?>,
        disabled: <?= js((bool) $disabled) ?>,
    })"
    @click="!disabled && $refs.field && $refs.field.focus()"
    <?php if ($disabled): ?>aria-disabled="true"<?php endif; ?>
    <?= $attributes->twMerge('border-input dark:bg-input/30 flex min-h-9 w-full flex-wrap items-center gap-1.5 rounded-md border bg-transparent p-1.5 text-sm shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px] has-[input:disabled]:pointer-events-none has-[input:disabled]:cursor-not-allowed has-[input:disabled]:opacity-50') ?>
>
    <?php if ($name): ?>
        <template x-for="tag in tags" :key="tag">
            <input type="hidden" name="<?= e($name) ?>[]" :value="tag">
        </template>
    <?php endif; ?>

    <template x-for="(tag, i) in tags" :key="tag">
        <span data-slot="tags-input-item" class="bg-secondary text-secondary-foreground inline-flex items-center gap-1 rounded px-2 py-0.5 text-sm">
            <span x-text="tag"></span>
            <button
                type="button"
                x-show="!disabled"
                @click.stop="remove(i)"
                :aria-label="'Remove ' + tag"
                class="hover:text-secondary-foreground/70 -me-0.5 inline-flex cursor-pointer items-center rounded-sm outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]"
            >
                <i data-lucide="x" class="size-3.5" aria-hidden="true"></i>
            </button>
        </span>
    </template>

    <input
        type="text"
        x-ref="field"
        x-model="draft"
        <?php if ($id): ?>id="<?= e($id) ?>"
        <?php endif; ?>
        placeholder="<?= e($placeholder) ?>"
        :disabled="inputDisabled"
        @keydown.enter.prevent="add()"
        @keydown="if ($event.key === ',') { $event.preventDefault(); add(); }"
        @keydown.backspace="backspace()"
        @blur="add()"
        class="text-foreground placeholder:text-muted-foreground flex-1 bg-transparent px-1 py-0.5 text-sm outline-none disabled:cursor-not-allowed"
    />
</div>
