<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => '#',
    'variant' => 'default',
    'external' => false,
]));

$base = 'inline underline-offset-4 rounded-xs transition-colors outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]';

$variants = [
    'default' => 'text-primary underline decoration-primary/40 hover:decoration-primary',
    'muted' => 'text-muted-foreground underline decoration-muted-foreground/30 hover:text-foreground',
    'subtle' => 'text-current no-underline hover:underline',
];

$classes = $base.' '.($variants[$variant] ?? $variants['default']);

$linkTarget = safe_url(is_string($href) ? $href : null) ?? '#';
?>
<a
    href="<?= e($linkTarget) ?>"
    data-slot="link"
<?php if ($external): ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>
    <?= $attributes->twMerge($classes) ?>
><?= $slot ?><?php if ($external): ?><span class="sr-only"> (opens in new tab)</span><?php endif; ?></a>
