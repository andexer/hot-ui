<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="collapsible-content"
    :id="$id('hot-collapsible')"
    x-show="open"
    x-hot-collapse="open"
    x-cloak
    :data-state="open ? 'open' : 'closed'"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
