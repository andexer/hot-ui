<?php

declare(strict_types=1);

extract(props($__ctx, [
    'open' => false,
    'id' => null,
]));
?>
<div
    data-slot="alert-dialog"
    x-data="hotAlertDialog({ open: <?= js((bool) $open) ?> })"
<?php if ($id): ?>
    @open-alert-dialog-<?= e($id) ?>.window="open = true"
    @close-alert-dialog-<?= e($id) ?>.window="open = false"
<?php endif; ?>
    x-id="['hot-alert-dialog']"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
