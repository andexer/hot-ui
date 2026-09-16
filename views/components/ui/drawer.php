<?php

declare(strict_types=1);

extract(props($__ctx, ['direction' => 'bottom']));
?>
<div
    data-slot="drawer"
    data-vaul-drawer-direction="<?= e($direction) ?>"
    x-data="hotDrawer({ direction: <?= js($direction) ?> })"
    x-id="['hot-drawer']"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
