<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'default',
]));

$variants = [
    'default' => 'bg-transparent',
    'icon' => "bg-muted text-foreground flex size-10 shrink-0 items-center justify-center rounded-lg [&_svg:not([class*='size-'])]:size-6",
];
?>
<div
    data-slot="empty-media"
    data-variant="<?= e($variant) ?>"
    <?= $attributes->twMerge('mb-2 flex shrink-0 items-center justify-center '.($variants[$variant] ?? $variants['default'])) ?>
>
    <?= $slot ?>
</div>
