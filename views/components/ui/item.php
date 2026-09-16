<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
]));

$base = "group/item flex items-center border border-transparent text-sm rounded-md transition-colors [a]:hover:bg-accent/50 [a]:transition-colors duration-100 flex-wrap outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]";
$variants = [
    'default' => 'bg-transparent',
    'outline' => 'border-border',
    'muted' => 'bg-muted/50',
];
$sizes = [
    'default' => 'gap-4 p-4',
    'sm' => 'gap-2.5 px-4 py-3',
];
$classes = $base.' '.($variants[$variant] ?? $variants['default']).' '.($sizes[$size] ?? $sizes['default']);

$linkTarget = ! empty($href) ? safe_url(is_string($href) ? $href : null) : null;
?>
<?php if ($linkTarget !== null): ?>
<a href="<?= e($linkTarget) ?>" data-slot="item" data-variant="<?= e($variant) ?>" data-size="<?= e($size) ?>" <?= $attributes->twMerge($classes) ?>><?= $slot ?></a>
<?php else: ?>
<div data-slot="item" data-variant="<?= e($variant) ?>" data-size="<?= e($size) ?>" <?= $attributes->twMerge($classes) ?>><?= $slot ?></div>
<?php endif; ?>
