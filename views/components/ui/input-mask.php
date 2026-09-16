<?php

declare(strict_types=1);

extract(props($__ctx, [
    'mask' => '',
    'value' => '',
    'id' => null,
    'name' => null,
    'placeholder' => null,
    'inputmode' => null,
]));

$classes = "file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive md:text-sm";
?>
<input
    type="text"
    data-slot="input"
    data-mask="<?= e($mask) ?>"
    value="<?= e($value) ?>"
    <?php if ($id): ?>id="<?= e($id) ?>"
    <?php endif; ?>
    <?php if ($name): ?>name="<?= e($name) ?>"
    <?php endif; ?>
    <?php if ($placeholder): ?>placeholder="<?= e($placeholder) ?>"
    <?php endif; ?>
    <?php if ($inputmode): ?>inputmode="<?= e($inputmode) ?>"
    <?php endif; ?>
    x-data="hotInputMask()"
    x-init="apply()"
    x-on:input="apply()"
    <?= $attributes->twMerge($classes) ?>
/>
