<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'options' => [],
    'value' => null,
    'size' => 'default',
    'disabled' => false,
]));

$items = [];
foreach ($options as $option) {
    if (is_array($option)) {
        $val = $option['value'] ?? ($option['label'] ?? null);
        $items[] = [
            'value' => (string) $val,
            'label' => (string) ($option['label'] ?? $val),
            'icon' => $option['icon'] ?? null,
        ];
    } else {
        $items[] = ['value' => (string) $option, 'label' => (string) $option, 'icon' => null];
    }
}

$groupName = $name ?? ('segmented-control-'.bin2hex(random_bytes(3)));

$uid = 'segmented-control-'.bin2hex(random_bytes(4));

$tracks = [
    'sm' => 'h-8 p-0.5 text-xs',
    'default' => 'h-9 p-1 text-sm',
    'lg' => 'h-11 p-1 text-base',
];
$segments = [
    'sm' => 'gap-1 px-2 [&_svg]:size-3.5',
    'default' => 'gap-1.5 px-2.5 [&_svg]:size-4',
    'lg' => 'gap-2 px-4 [&_svg]:size-5',
];

$track = $tracks[$size] ?? $tracks['default'];
$segment = $segments[$size] ?? $segments['default'];
?>
<div
    data-slot="segmented-control"
    role="radiogroup"
    <?php if ($name): ?>aria-label="<?= e($name) ?>"<?php endif; ?>
    <?php if ($disabled): ?>aria-disabled="true" data-disabled<?php endif; ?>
    <?= $attributes->twMerge('inline-flex w-fit items-center justify-center rounded-lg bg-muted text-muted-foreground '.$track.($disabled ? ' opacity-50' : '')) ?>
>
    <?php foreach ($items as $i => $item):
        $checked = $value !== null && (string) $value === $item['value'];
        $inputId = $uid.'-'.$i; ?>
        <span data-slot="segmented-control-item" data-value="<?= e($item['value']) ?>" class="relative inline-flex min-w-0 flex-1 shrink-0">
            <input
                type="radio"
                id="<?= e($inputId) ?>"
                name="<?= e((string) $groupName) ?>"
                value="<?= e($item['value']) ?>"
                class="peer sr-only"
                <?php if ($checked) { echo 'checked'; } ?>
                <?php if ($disabled) { echo 'disabled'; } ?>
            >
            <label
                for="<?= e($inputId) ?>"
                class="<?= classes([
                    'inline-flex w-full min-w-0 items-center justify-center whitespace-nowrap rounded-md font-medium text-muted-foreground transition-[color,box-shadow] outline-none',
                    'peer-checked:bg-background peer-checked:text-foreground peer-checked:shadow-sm',
                    'peer-focus-visible:ring-[3px] peer-focus-visible:ring-ring/50',
                    '[&_svg]:pointer-events-none [&_svg]:shrink-0',
                    $segment,
                    'cursor-pointer hover:text-foreground' => ! $disabled,
                    'cursor-not-allowed' => $disabled,
                ]) ?>"
            >
                <?php if ($item['icon']): ?>
                    <i data-lucide="<?= e((string) $item['icon']) ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <span><?= e($item['label']) ?></span>
            </label>
        </span>
    <?php endforeach; ?>
</div>
