<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<th data-slot="table-head" <?php if (! $attributes->has('scope')): ?>scope="col"<?php endif; ?> <?= $attributes->twMerge('text-foreground h-10 px-2 text-start align-middle font-medium whitespace-nowrap [&:has([role=checkbox])]:pe-0 [&>[role=checkbox]]:translate-y-[2px]') ?>>
    <?= $slot ?>
</th>
