<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="sidebar-footer" data-sidebar="footer" <?= $attributes->twMerge('flex flex-col gap-2 p-2') ?>>
    <?= $slot ?>
</div>
