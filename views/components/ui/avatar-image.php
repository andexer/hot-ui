<?php

declare(strict_types=1);

extract(props($__ctx, [
    'src' => null,
    'alt' => '',
]));

$srcUrl = safe_url(is_string($src) ? $src : null);
?>
<img
    data-slot="avatar-image"
<?php if ($srcUrl !== null): ?>
    src="<?= e($srcUrl) ?>"
<?php endif; ?>
    alt="<?= e($alt) ?>"
    x-show="!error"
    x-on:load="loaded = true"
    x-on:error="error = true"
    <?= $attributes->twMerge('aspect-square size-full') ?>
/>
