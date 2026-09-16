<?php

declare(strict_types=1);

extract(props($__ctx, [
    'for' => null,
]));
?>
<span
<?php if ($for): ?>
    x-data
    @click="$dispatch('open-alert-dialog-<?= e($for) ?>')"
    aria-haspopup="dialog"
<?php else: ?>
    @click="open = true"
    x-hot-trigger="{ haspopup: 'dialog', controls: $id('hot-alert-dialog') }"
<?php endif; ?>
    data-slot="alert-dialog-trigger"
    <?= $attributes->twMerge('inline-block') ?>
>
    <?= $slot ?>
</span>
