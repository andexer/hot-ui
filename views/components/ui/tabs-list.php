<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'segmented',
]));

$variants = [
    'segmented' => 'bg-muted h-9 w-fit justify-center rounded-lg p-[3px]',
    'underline' => 'w-full justify-start gap-4 border-b',
    'pills' => 'w-fit justify-center gap-1',
];
?>
<div
    data-slot="tabs-list"
    data-variant="<?= e($variant) ?>"
    role="tablist"
    :aria-orientation="orientation"
    :data-orientation="orientation"
    @keydown="$hot.nav($event, { orientation, selector: '[role=tab]', loop: true })"
    <?= $attributes->twMerge('group/tabs-list text-muted-foreground inline-flex items-center '.($variants[$variant] ?? $variants['segmented'])) ?>
>
    <?= $slot ?>
</div>
