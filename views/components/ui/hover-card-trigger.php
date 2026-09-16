<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    x-ref="trigger"
    x-hot-trigger="{ focusable: true, state: null }"
    data-slot="hover-card-trigger"
    <?= $attributes->twMerge('inline-block') ?>
>
    <?= $slot ?>
</span>
