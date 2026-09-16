<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => '#',
]));

$linkTarget = safe_url(is_string($href) ? $href : null);
?>
<a href="<?= e($linkTarget ?? '#') ?>" data-slot="breadcrumb-link" <?= $attributes->twMerge('hover:text-foreground transition-colors') ?>>
    <?= $slot ?>
</a>
