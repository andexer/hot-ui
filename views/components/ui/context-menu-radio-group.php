<?php

declare(strict_types=1);

extract(props($__ctx, ['value' => '']));
?>
<div
    data-slot="context-menu-radio-group"
    role="group"
    x-data="{ radioValue: <?= js((string) $value) ?> }"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
