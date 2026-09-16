<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="sidebar-header" data-sidebar="header" <?= $attributes->twMerge('flex flex-col gap-2 p-2') ?>>
    <?= $slot ?>
</div>
