<?php

declare(strict_types=1);

extract(props($__ctx, [
    'step' => 1,
    'disabled' => false,
]));
?>
<li
    data-slot="stepper-item"
    x-data="{ itemStep: <?= js((int) $step) ?>, disabled: <?= js((bool) $disabled) ?> }"
    :data-state="step > itemStep ? 'completed' : (step === itemStep ? 'active' : 'inactive')"
    :data-disabled="disabled ? '' : null"
    :class="orientation === 'vertical' ? 'flex-col' : 'items-center not-last:flex-1'"
    <?= $attributes->twMerge('group/step relative flex') ?>
>
    <?= $slot ?>
</li>
