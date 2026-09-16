<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    x-ref="trigger"
    x-init="_trigger = $el.querySelector('button, [href], [role=button], [tabindex]:not([tabindex=\'-1\'])') || $el"
    @click="toggleMenu()"
    @keydown.enter.prevent.stop="openMenu('first')"
    @keydown.space.prevent.stop="openMenu('first')"
    @keydown.down.prevent.stop="openMenu('first')"
    @keydown.up.prevent.stop="openMenu('last')"
    x-hot-trigger="{ haspopup: 'menu', controls: $id('hot-menu'), id: $id('hot-menu-trigger') }"
    data-slot="dropdown-menu-trigger"
    <?= $attributes->twMerge('contents') ?>
>
    <?= $slot ?>
</span>
