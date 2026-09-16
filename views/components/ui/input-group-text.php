<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<span data-slot="input-group-text" <?= $attributes->twMerge("text-muted-foreground flex items-center gap-2 text-sm [&_svg:not([class*='size-'])]:size-4") ?>>
    <?= $slot ?>
</span>
