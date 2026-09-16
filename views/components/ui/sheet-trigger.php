<?php

declare(strict_types=1);

extract(props($__ctx, [
    'for' => null,
]));
?>
<span
    <?php if ($for): ?>
    x-data
    @click="$dispatch('open-sheet-<?= e($for) ?>')"
    aria-haspopup="dialog"
    <?php else: ?>
    @click="open = true"
    x-hot-trigger="{ haspopup: 'dialog', controls: $id('hot-sheet') }"
    <?php endif; ?>
    data-slot="sheet-trigger"
    <?= $attributes->twMerge('inline-block') ?>
>
    <?= $slot ?>
</span>
