<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    x-ref="trigger"
    x-hot-trigger="{ describedby: $id('hot-tooltip'), state: null, focusable: true }"
    data-slot="tooltip-trigger"
    <?= $attributes->twMerge('inline-block') ?>
>
    <?= $slot ?>
</span>
