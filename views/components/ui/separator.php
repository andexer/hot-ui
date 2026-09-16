<?php

declare(strict_types=1);

extract(props($__ctx, [
    'orientation' => 'horizontal',
    'decorative' => true,
]));
?>
<div
    data-slot="separator"
    role="<?= e($decorative ? 'none' : 'separator') ?>"
<?php if (! $decorative): ?>
    aria-orientation="<?= e($orientation) ?>"
<?php endif; ?>
    data-orientation="<?= e($orientation) ?>"
    <?= $attributes->twMerge('bg-border shrink-0 data-[orientation=horizontal]:h-px data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-px') ?>
></div>
