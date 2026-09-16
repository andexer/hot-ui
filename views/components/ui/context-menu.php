<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div
    data-slot="context-menu"
    x-data="hotMenu()"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
