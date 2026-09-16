<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<nav
    data-slot="navigation-menu"
    x-data="{ active: null }"
    @mouseleave="active = null"
    <?= $attributes->twMerge('group/navigation-menu relative flex max-w-max flex-1 items-center justify-center') ?>
>
    <?= $slot ?>
</nav>
