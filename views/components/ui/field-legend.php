<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'legend',
]));
?>
<legend
    data-slot="field-legend"
    data-variant="<?= e($variant) ?>"
    <?= $attributes->twMerge('mb-3 font-medium data-[variant=legend]:text-base data-[variant=label]:text-sm') ?>
><?= $slot ?></legend>
