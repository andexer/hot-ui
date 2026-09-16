<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="menubar-sub"
    x-data="hotMenu()"
    x-id="['hot-submenu', 'hot-submenu-trigger']"
    @mouseenter="open = true; cancelClose()"
    @mouseleave="closeSoon()"
    <?= $attributes->twMerge('relative') ?>
>
    <?= $slot ?>
</div>
