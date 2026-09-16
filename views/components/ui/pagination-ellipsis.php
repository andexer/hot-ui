<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    aria-hidden="true"
    data-slot="pagination-ellipsis"
    <?= $attributes->twMerge('flex size-9 items-center justify-center') ?>
>
    <i data-lucide="more-horizontal" class="size-4"></i>
    <span class="sr-only">More pages</span>
</span>
