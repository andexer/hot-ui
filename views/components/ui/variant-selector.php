<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'options' => [],
    'value' => null,
    'type' => 'pill',
    'label' => 'Variant',
    'disabled' => false,
]));

$normalized = [];
foreach ($options as $option) {
    if (is_array($option)) {
        $optValue = $option['value'] ?? ($option['label'] ?? null);
        $optLabel = $option['label'] ?? ($option['value'] ?? '');
        $optColor = $option['color'] ?? null;
        $optDisabled = (bool) ($option['disabled'] ?? false);
    } else {
        $optValue = $option;
        $optLabel = $option;
        $optColor = null;
        $optDisabled = false;
    }

    $normalized[] = [
        'value' => (string) $optValue,
        'label' => (string) $optLabel,
        'color' => $optColor,
        'disabled' => $optDisabled,
    ];
}

$groupId = 'variant-selector-'.bin2hex(random_bytes(4));
$radioName = $name ?? $groupId;
$labelId = $groupId.'-label';
?>
<div
    data-slot="variant-selector"
    <?= $attributes->twMerge('flex flex-col gap-2') ?>
>
    <?php if ($label): ?>
        <span id="<?= e($labelId) ?>" data-slot="variant-selector-label" class="text-sm font-medium text-foreground">
            <?= e($label) ?>
        </span>
    <?php endif; ?>

    <div
        role="radiogroup"
        <?php if ($label): ?>aria-labelledby="<?= e($labelId) ?>"<?php endif; ?>
        data-slot="variant-selector-options"
        class="<?= classes([
            'flex flex-wrap gap-2' => $type !== 'color',
            'flex flex-wrap items-center gap-3' => $type === 'color',
        ]) ?>"
    >
        <?php foreach ($normalized as $optionIndex => $option):
            $optionDisabled = $disabled || $option['disabled'];
            $isChecked = $value !== null && (string) $value === $option['value'];
            $inputId = $groupId.'-'.$optionIndex; ?>
            <?php if ($type === 'color'): ?>
                <span data-slot="variant-selector-item" class="relative inline-flex">
                    <input
                        type="radio"
                        id="<?= e($inputId) ?>"
                        name="<?= e($radioName) ?>"
                        value="<?= e($option['value']) ?>"
                        <?php if ($isChecked): ?>checked<?php endif; ?>
                        <?php if ($optionDisabled): ?>disabled<?php endif; ?>
                        class="peer sr-only"
                    >
                    <label
                        for="<?= e($inputId) ?>"
                        title="<?= e($option['label']) ?>"
                        class="<?= classes([
                            'relative grid size-9 place-items-center rounded-full border border-border bg-clip-padding shadow-xs transition-[box-shadow,transform] outline-none',
                            'ring-offset-2 ring-offset-background peer-checked:ring-2 peer-checked:ring-ring' => true,
                            'peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background' => true,
                            'cursor-pointer hover:scale-105' => ! $optionDisabled,
                            'cursor-not-allowed opacity-50' => $optionDisabled,
                        ]) ?>"
                        style="background-color: <?= e($option['color'] ?? 'transparent') ?>;"
                    >
                        <i
                            data-lucide="check"
                            aria-hidden="true"
                            class="size-4 text-white opacity-0 drop-shadow-[0_1px_1px_rgba(0,0,0,0.55)] transition-opacity peer-checked:opacity-100"
                        ></i>
                        <?php if ($optionDisabled): ?>
                            <span aria-hidden="true" class="pointer-events-none absolute inset-0 grid place-items-center">
                                <span class="block h-px w-[140%] rotate-45 bg-foreground/60"></span>
                            </span>
                        <?php endif; ?>
                        <span class="sr-only"><?= e($option['label']) ?></span>
                    </label>
                </span>
            <?php else: ?>
                <span data-slot="variant-selector-item" class="relative inline-flex">
                    <input
                        type="radio"
                        id="<?= e($inputId) ?>"
                        name="<?= e($radioName) ?>"
                        value="<?= e($option['value']) ?>"
                        <?php if ($isChecked): ?>checked<?php endif; ?>
                        <?php if ($optionDisabled): ?>disabled<?php endif; ?>
                        class="peer sr-only"
                    >
                    <label
                        for="<?= e($inputId) ?>"
                        class="<?= classes([
                            'inline-flex min-w-9 items-center justify-center rounded-md border border-border px-3 py-1.5 text-sm font-medium text-foreground transition-[color,box-shadow,background-color] outline-none select-none',
                            'peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary peer-checked:ring-2 peer-checked:ring-primary' => true,
                            'peer-focus-visible:ring-2 peer-focus-visible:ring-ring' => true,
                            'cursor-pointer hover:bg-accent hover:text-accent-foreground' => ! $optionDisabled,
                            'cursor-not-allowed text-muted-foreground/50 line-through opacity-60' => $optionDisabled,
                        ]) ?>"
                    >
                        <?= e($option['label']) ?>
                    </label>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
