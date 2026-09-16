<?php

declare(strict_types=1);

extract(props($__ctx, [
    'dataSlot' => 'menu-group',

    'labelSlot' => 'menu-label',

    'compact' => false,
]));
?>
<?php if ($compact): ?>
<div data-slot="<?= e($dataSlot) ?>" role="group" x-hot-labelledby="{ label: ':scope > [data-slot=<?= e($labelSlot) ?>]' }" <?= $attributes ?>><?= $slot ?></div>
<?php else: ?>
<div role="group" data-slot="<?= e($dataSlot) ?>" x-hot-labelledby="{ label: ':scope > [data-slot=<?= e($labelSlot) ?>]' }" <?= $attributes ?>>
    <?= $slot ?>
</div>
<?php endif; ?>
