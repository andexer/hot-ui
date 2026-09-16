<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="empty-content" <?= $attributes->twMerge('flex w-full max-w-sm min-w-0 flex-col items-center gap-4 text-sm text-balance') ?>>
    <?= $slot ?>
</div>
