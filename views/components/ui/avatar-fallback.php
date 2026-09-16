<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    data-slot="avatar-fallback"
    x-show="error || !loaded"
    <?= $attributes->twMerge('bg-muted flex size-full items-center justify-center rounded-full text-sm') ?>
>
    <?= $slot ?>
</span>
