<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    data-slot="stepper-title"
    <?= $attributes->twMerge('text-sm font-medium leading-none') ?>
>
    <?= $slot ?>
</span>
