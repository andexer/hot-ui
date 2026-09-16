<?php

declare(strict_types=1);

extract(props($__ctx, [
    'step' => 1,
]));
?>
<div
    data-slot="stepper-content"
    x-show="step === <?= js((int) $step) ?>"
    x-cloak
    role="tabpanel"
    <?= $attributes->twMerge('text-sm') ?>
>
    <?= $slot ?>
</div>
