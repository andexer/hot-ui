<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="chat"
    role="log"
    aria-label="Conversation"
    aria-live="polite"
    tabindex="0"
    <?= $attributes->twMerge('flex flex-col gap-4 overflow-y-auto p-4 outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]') ?>
>
    <?= $slot ?>
</div>
