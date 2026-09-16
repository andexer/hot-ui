<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<ol
    data-slot="timeline"
    <?= $attributes->twMerge('relative flex flex-col [&>li:last-child_[data-slot=timeline-line]]:hidden') ?>
>
    <?= $slot ?>
</ol>
