<?php

declare(strict_types=1);

extract(props($__ctx, ['open' => false, 'id' => null]));
?>
<div
    data-slot="dialog"
    x-data="hotDialog({ open: <?= js((bool) $open) ?> })"
<?php if ($id): ?>
    @open-dialog-<?= e($id) ?>.window="open = true"
    @close-dialog-<?= e($id) ?>.window="open = false"
<?php endif; ?>
    x-id="['hot-dialog']"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
