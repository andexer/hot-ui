<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="menubar"
    role="menubar"
    aria-orientation="horizontal"
    x-data="hotMenubar()"
    <?= $attributes->twMerge('bg-background flex h-9 items-center gap-1 rounded-md border p-1 shadow-xs') ?>
>
    <?= $slot ?>
</div>
