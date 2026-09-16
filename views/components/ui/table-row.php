<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<tr data-slot="table-row" <?= $attributes->twMerge('hover:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors') ?>>
    <?= $slot ?>
</tr>
