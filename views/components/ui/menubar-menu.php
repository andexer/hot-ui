<?php

declare(strict_types=1);

extract(props($__ctx));

$menuId = 'menubar-'.bin2hex(random_bytes(4));
?>
<div data-slot="menubar-menu" x-data="{ id: <?= js($menuId) ?> }" <?= $attributes->twMerge('relative') ?>>
    <?= $slot ?>
</div>
