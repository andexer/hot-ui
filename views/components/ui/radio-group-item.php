<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => null,
    'id' => null,
    'disabled' => false,
]));

$valueJs = js($value);
?>
<button
    type="button"
    role="radio"
    data-value="<?= e($value) ?>"
<?php if ($id): ?>
    id="<?= e($id) ?>"
<?php endif; ?>
    @click="value = <?= $valueJs ?>; rovingValue = <?= $valueJs ?>"
    @focus="rovingValue = <?= $valueJs ?>"
    :data-state="value === <?= $valueJs ?> ? 'checked' : 'unchecked'"
    :aria-checked="(value === <?= $valueJs ?>).toString()"
    :tabindex="rovingValue === <?= $valueJs ?> ? 0 : -1"
<?php if ($disabled): ?>
    disabled
<?php endif; ?>
    data-slot="radio-group-item"
    <?= $attributes->twMerge('border-input text-primary dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive aspect-square size-4 shrink-0 rounded-full border shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50') ?>
>
    <span data-slot="radio-group-indicator" class="relative flex items-center justify-center" x-show="value === <?= $valueJs ?>" x-cloak>
        <i data-lucide="circle" aria-hidden="true" class="fill-current absolute top-1/2 left-1/2 size-2 -translate-x-1/2 -translate-y-1/2"></i>
    </span>
</button>
