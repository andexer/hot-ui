<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<li data-slot="sidebar-menu-item" data-sidebar="menu-item" <?= $attributes->twMerge('group/menu-item relative') ?>>
    <?= $slot ?>
</li>
