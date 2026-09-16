<?php

declare(strict_types=1);

extract(props($__ctx, [
    'dataSlot' => 'menu-shortcut',
]));
?>
<span data-slot="<?= e($dataSlot) ?>" <?= $attributes->twMerge('text-muted-foreground ms-auto text-xs tracking-widest') ?>><?= $slot ?></span>
