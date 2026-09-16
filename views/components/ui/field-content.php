<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="field-content" <?= $attributes->twMerge('group/field-content flex flex-1 flex-col gap-1.5 leading-snug') ?>>
    <?= $slot ?>
</div>
