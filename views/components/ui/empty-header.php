<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="empty-header" <?= $attributes->twMerge('flex max-w-sm flex-col items-center gap-2 text-center') ?>>
    <?= $slot ?>
</div>
