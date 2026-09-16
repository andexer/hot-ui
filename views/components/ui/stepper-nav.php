<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<ol
    data-slot="stepper-nav"
    :class="orientation === 'vertical' ? 'flex-col' : 'flex-row items-center'"
    <?= $attributes->twMerge('flex w-full gap-2 overflow-x-auto') ?>
>
    <?= $slot ?>
</ol>
