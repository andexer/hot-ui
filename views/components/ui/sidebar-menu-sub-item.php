<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<li data-slot="sidebar-menu-sub-item" data-sidebar="menu-sub-item" <?= $attributes->twMerge('group/menu-sub-item relative') ?>>
    <?= $slot ?>
</li>
