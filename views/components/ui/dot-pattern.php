<?php

declare(strict_types=1);

extract(props($__ctx, [
    'size' => 1,
    'gap' => 16,
    'mask' => false,
]));

$dot = (float) $size;
$cell = (float) $gap;

$maskCss = $mask
    ? 'mask-image: radial-gradient(ellipse at center, #000 40%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at center, #000 40%, transparent 75%);'
    : '';
?>
<div
    data-slot="dot-pattern"
    aria-hidden="true"
    <?= $attributes->twMerge('pointer-events-none absolute inset-0 text-foreground/15') ?>
    style="
        background-image: radial-gradient(currentColor <?= $dot ?>px, transparent <?= $dot ?>px);
        background-size: <?= $cell ?>px <?= $cell ?>px;
        background-position: 0 0;
        <?= $maskCss ?>
    "
></div>
