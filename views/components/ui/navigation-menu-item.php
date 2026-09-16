<?php

declare(strict_types=1);

extract(props($__ctx));

$navId = 'nav-'.bin2hex(random_bytes(4));
?>
<li data-slot="navigation-menu-item" x-data="{ id: <?= js($navId) ?> }" <?= $attributes->twMerge('relative') ?>>
    <?= $slot ?>
</li>
