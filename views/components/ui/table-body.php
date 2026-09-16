<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<tbody data-slot="table-body" <?= $attributes->twMerge('[&_tr:last-child]:border-0') ?>>
    <?= $slot ?>
</tbody>
