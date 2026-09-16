<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<td data-slot="table-cell" <?= $attributes->twMerge('p-2 align-middle whitespace-nowrap [&:has([role=checkbox])]:pe-0 [&>[role=checkbox]]:translate-y-[2px]') ?>>
    <?= $slot ?>
</td>
