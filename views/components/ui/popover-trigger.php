<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    x-ref="trigger"
    @click="open = !open"
    x-hot-trigger="{ haspopup: 'dialog', controls: $id('hot-popover') }"
    data-slot="popover-trigger"
    <?= $attributes->twMerge('inline-block') ?>
>
    <?= $slot ?>
</span>
