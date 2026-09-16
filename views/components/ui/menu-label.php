<?php

declare(strict_types=1);

extract(props($__ctx, [
    'dataSlot' => 'menu-label',

    'classes' => 'px-2 py-1.5 text-sm font-medium data-[inset]:ps-8',
    'inset' => false,
]));
?>
<div
    data-slot="<?= e($dataSlot) ?>"
    role="presentation"
<?php if ($inset): ?>
    data-inset
<?php endif; ?>
    <?= $attributes->twMerge($classes) ?>
><?= $slot ?></div>
