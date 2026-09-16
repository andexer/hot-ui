<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => null,
]));

$valueJs = js($value);
?>
<div
    data-slot="tabs-content"
    role="tabpanel"
    tabindex="0"
    :id="$id('hot-tabpanel', <?= $valueJs ?>)"
    :aria-labelledby="$id('hot-tab', <?= $valueJs ?>)"
    x-show="tab === <?= $valueJs ?>"
    x-cloak
    <?= $attributes->twMerge('flex-1 outline-none') ?>
>
    <?= $slot ?>
</div>
