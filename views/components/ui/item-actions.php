<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="item-actions" <?= $attributes->twMerge('flex items-center gap-2') ?>>
    <?= $slot ?>
</div>
