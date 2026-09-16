<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<tfoot data-slot="table-footer" <?= $attributes->twMerge('bg-muted/50 border-t font-medium [&>tr]:last:border-b-0') ?>>
    <?= $slot ?>
</tfoot>
