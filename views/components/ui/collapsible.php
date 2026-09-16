<?php

declare(strict_types=1);

extract(props($__ctx, ['open' => false]));
?>
<div data-slot="collapsible" x-data="hotCollapsible({ open: <?= js((bool) $open) ?> })" x-id="['hot-collapsible']" <?= $attributes ?>>
    <?= $slot ?>
</div>
