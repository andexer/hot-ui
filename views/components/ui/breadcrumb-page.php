<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    data-slot="breadcrumb-page"
    role="link"
    aria-disabled="true"
    aria-current="page"
    <?= $attributes->twMerge('text-foreground font-normal') ?>
><?= $slot ?></span>
