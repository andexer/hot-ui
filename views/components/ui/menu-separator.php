<?php

declare(strict_types=1);

extract(props($__ctx, [
    'dataSlot' => 'menu-separator',
]));
?>
<div data-slot="<?= e($dataSlot) ?>" role="separator" aria-orientation="horizontal" <?= $attributes->twMerge('bg-border -mx-1 my-1 h-px') ?>></div>
