<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<ul data-slot="navigation-menu-list" <?= $attributes->twMerge('group flex flex-1 list-none items-center justify-center gap-1') ?>>
    <?= $slot ?>
</ul>
