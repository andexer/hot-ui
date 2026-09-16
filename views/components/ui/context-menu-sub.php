<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="context-menu-sub"
    x-data="hotMenu()"
    x-id="['hot-ctx-submenu', 'hot-ctx-submenu-trigger']"
    @mouseenter="open = true; cancelClose()"
    @mouseleave="closeSoon()"
    <?= $attributes->twMerge('relative') ?>
>
    <?= $slot ?>
</div>
