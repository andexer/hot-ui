<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<button
    type="button"
    data-slot="collapsible-trigger"
    @click="open = !open"
    :aria-controls="$id('hot-collapsible')"
    :data-state="open ? 'open' : 'closed'"
    :aria-expanded="open"
    <?= $attributes ?>
>
    <?= $slot ?>
</button>
