<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="command"
    x-data="hotCommand()"
    x-id="['hot-command-list']"
    x-init="$nextTick(() => ensureActive()); $watch('query', () => $nextTick(() => ensureActive()))"
    <?= $attributes->twMerge('bg-popover text-popover-foreground flex h-full w-full flex-col overflow-hidden rounded-md') ?>
>
    <?= $slot ?>
</div>
