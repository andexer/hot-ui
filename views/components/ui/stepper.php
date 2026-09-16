<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => 1,
    'orientation' => 'horizontal',
]));
?>
<div
    data-slot="stepper"
    x-data="{ step: <?= js((int) $value) ?>, orientation: <?= js($orientation) ?> }"
    :data-orientation="orientation"
    <?= $attributes->twMerge('flex flex-col gap-8') ?>
>
    <?= $slot ?>
</div>
