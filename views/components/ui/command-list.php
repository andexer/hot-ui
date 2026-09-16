<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="command-list"
    role="listbox"
    tabindex="-1"
    :id="$id('hot-command-list')"
    <?= $attributes->twMerge('max-h-[300px] scroll-py-1 overflow-x-hidden overflow-y-auto') ?>
>
    <?= $slot ?>
</div>
