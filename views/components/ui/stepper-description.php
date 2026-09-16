<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    data-slot="stepper-description"
    <?= $attributes->twMerge('text-muted-foreground text-xs') ?>
>
    <?= $slot ?>
</span>
