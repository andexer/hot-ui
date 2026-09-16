<?php

declare(strict_types=1);

extract(props($__ctx, ['for' => null]));
?>
<span
<?php if ($for): ?>
    x-data
    @click="$dispatch('open-dialog-<?= e($for) ?>')"
    aria-haspopup="dialog"
<?php else: ?>
    @click="open = true"
    x-hot-trigger="{ haspopup: 'dialog', controls: $id('hot-dialog') }"
<?php endif; ?>
    data-slot="dialog-trigger"
    <?= $attributes->twMerge('inline-block') ?>
>
    <?= $slot ?>
</span>
