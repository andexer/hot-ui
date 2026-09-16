<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="sidebar-group" data-sidebar="group" <?= $attributes->twMerge('relative flex w-full min-w-0 flex-col p-2') ?>>
    <?= $slot ?>
</div>
