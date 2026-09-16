<?php

declare(strict_types=1);

extract(props($__ctx, [
    'ratio' => '1 / 1',
]));
?>
<div data-slot="aspect-ratio" style="aspect-ratio: <?= e($ratio) ?>" <?= $attributes->twMerge('relative') ?>>
    <?= $slot ?>
</div>
