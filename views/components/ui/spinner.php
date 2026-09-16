<?php

declare(strict_types=1);

extract(props($__ctx, [
    'icon' => 'loader-circle',
]));
?>
<i data-lucide="<?= e($icon) ?>" data-slot="spinner" role="status" aria-label="Loading" <?= $attributes->twMerge('size-4 animate-spin') ?>></i>
