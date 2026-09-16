<?php

declare(strict_types=1);

extract(props($__ctx, [
    'orientation' => 'horizontal',
    'swipe' => true,
]));
?>
<div
    data-slot="carousel"
    role="region"
    aria-roledescription="carousel"
    x-data="hotCarousel({
        orientation: <?= js($orientation) ?>,
        swipe: <?= js((bool) $swipe) ?>,
    })"
    @keydown.left.prevent="prev()"
    @keydown.right.prevent="next()"
    tabindex="0"
    <?= $attributes->twMerge('relative') ?>
>
    <?= $slot ?>
</div>
