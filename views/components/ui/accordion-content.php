<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="accordion-content"
    role="region"
    :id="$id('hot-accordion-panel', _v)"
    :aria-labelledby="$id('hot-accordion-trigger', _v)"
    x-show="isOpen(_v)"
    x-hot-collapse="isOpen(_v)"
    x-cloak
    :data-state="isOpen(_v) ? 'open' : 'closed'"
    class="overflow-hidden text-sm"
>
    <div <?= $attributes->twMerge('pt-0 pb-4') ?>>
        <?= $slot ?>
    </div>
</div>
