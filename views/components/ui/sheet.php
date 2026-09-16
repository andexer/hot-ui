<?php

declare(strict_types=1);

extract(props($__ctx, [
    'open' => false,
    'id' => null,
]));
?>
<div
    data-slot="sheet"
    x-data="hotSheet({ open: <?= js((bool) $open) ?> })"
    <?php if ($id): ?>
    @open-sheet-<?= e($id) ?>.window="open = true"
    @close-sheet-<?= e($id) ?>.window="open = false"
    <?php endif; ?>
    x-id="['hot-sheet']"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
