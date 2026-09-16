<?php

declare(strict_types=1);

extract(props($__ctx, [
    'primary' => false,
]));
?>
<?php if ($primary): ?>
    <div data-slot="resizable-panel" :style="`flex-basis: ${size}%`" <?= $attributes->twMerge('grow-0 shrink-0 overflow-hidden') ?>>
        <?= $slot ?>
    </div>
<?php else: ?>
    <div data-slot="resizable-panel" <?= $attributes->twMerge('flex-1 overflow-hidden') ?>>
        <?= $slot ?>
    </div>
<?php endif; ?>
