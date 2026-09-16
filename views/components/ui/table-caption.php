<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<caption data-slot="table-caption" <?= $attributes->twMerge('text-muted-foreground mt-4 text-sm') ?>>
    <?= $slot ?>
</caption>
