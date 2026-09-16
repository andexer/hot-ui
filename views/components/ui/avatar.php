<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    data-slot="avatar"
    x-data="{ loaded: false, error: false }"
    <?= $attributes->twMerge('relative flex size-8 shrink-0 overflow-hidden rounded-full') ?>
>
    <?= $slot ?>
</span>
