<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'default',
]));

$container = $variant === 'card'
    ? 'relative w-full overflow-x-auto rounded-lg border bg-card shadow-xs'
    : 'relative w-full overflow-x-auto';
?>
<div data-slot="table-container" data-variant="<?= e($variant) ?>" class="<?= e($container) ?>">
    <table data-slot="table" <?= $attributes->twMerge('w-full caption-bottom text-sm') ?>>
        <?= $slot ?>
    </table>
</div>
