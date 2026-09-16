<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="field-group" <?= $attributes->twMerge('group/field-group @container/field-group flex w-full flex-col gap-7 data-[slot=checkbox-group]:gap-3') ?>>
    <?= $slot ?>
</div>
