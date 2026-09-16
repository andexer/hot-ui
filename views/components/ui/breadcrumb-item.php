<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<li data-slot="breadcrumb-item" <?= $attributes->twMerge('inline-flex items-center gap-1.5') ?>>
    <?= $slot ?>
</li>
