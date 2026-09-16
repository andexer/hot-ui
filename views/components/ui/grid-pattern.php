<?php

declare(strict_types=1);

extract(props($__ctx, [
    'gap' => 24,
    'lineWidth' => 1,
    'mask' => false,
]));

$g = (float) $gap;
$lw = (float) $lineWidth;

$bgImage = "linear-gradient(to right, currentColor {$lw}px, transparent {$lw}px),"
    ." linear-gradient(to bottom, currentColor {$lw}px, transparent {$lw}px)";
$bgSize = "{$g}px {$g}px";

$style = "background-image: {$bgImage}; background-size: {$bgSize};";

if ($mask) {

    $maskImage = 'radial-gradient(ellipse at center, #000 0%, transparent 75%)';
    $style .= " -webkit-mask-image: {$maskImage}; mask-image: {$maskImage};";
}

$userStyle = (string) $attributes->get('style', '');
$style = trim($style.($userStyle ? ' '.$userStyle : ''));
$attributes = $attributes->except('style');
?>
<div
    data-slot="grid-pattern"
    aria-hidden="true"
    style="<?= e($style) ?>"
    <?= $attributes->twMerge('text-foreground/10 pointer-events-none absolute inset-0 -z-10') ?>
></div>
