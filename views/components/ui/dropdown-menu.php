<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="dropdown-menu" x-data="hotMenu()" x-id="['hot-menu', 'hot-menu-trigger']" <?= $attributes->twMerge('contents text-start') ?>>
    <?= $slot ?>
</div>
