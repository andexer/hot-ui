<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="field-separator"
    <?= $attributes->twMerge('relative -my-2 h-5 text-sm') ?>
>
    <div class="bg-border absolute inset-0 top-1/2 h-px"></div>
    <?php if (trim((string) $slot) !== ''): ?>
    <span class="bg-background text-muted-foreground relative mx-auto block w-fit px-2"><?= $slot ?></span>
    <?php endif; ?>
</div>
