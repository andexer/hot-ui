<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span
    data-slot="stepper-separator"
    :data-state="step > itemStep ? 'completed' : 'inactive'"
    :class="orientation === 'vertical' ? 'my-1.5 w-0.5 grow' : 'mx-2 h-0.5 flex-1'"
    <?= $attributes->twMerge('bg-border data-[state=completed]:bg-primary rounded-full transition-colors') ?>
></span>
