<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<nav
    role="navigation"
    aria-label="pagination"
    data-slot="pagination"
    <?= $attributes->twMerge('mx-auto flex w-full justify-center') ?>
>
    <?= $slot ?>
</nav>
