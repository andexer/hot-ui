<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span @click="open = true" x-hot-trigger="{ haspopup: 'dialog', controls: $id('hot-drawer') }" data-slot="drawer-trigger" <?= $attributes->twMerge('inline-block') ?>>
    <?= $slot ?>
</span>
