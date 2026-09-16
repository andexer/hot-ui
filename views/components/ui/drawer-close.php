<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span @click="open = false" data-slot="drawer-close" <?= $attributes->twMerge('inline-block') ?>>
    <?= $slot ?>
</span>
