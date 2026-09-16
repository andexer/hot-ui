<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => null,
    'orientation' => 'horizontal',
]));
?>
<div
    data-slot="tabs"
    x-data="{ tab: <?= js($value) ?>, orientation: <?= js($orientation) ?> }"
    x-id="['hot-tab', 'hot-tabpanel']"
    :data-orientation="orientation"
    <?= $attributes->twMerge('flex flex-col gap-2') ?>
>
    <?= $slot ?>
</div>
