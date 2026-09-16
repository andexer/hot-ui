<?php

declare(strict_types=1);

extract(props($__ctx, [
    'heading' => null,
]));

$headingId = $heading ? 'cmd-grp-'.bin2hex(random_bytes(4)) : null;
?>
<div
    data-slot="command-group"
    role="group"
    <?php if ($heading): ?>aria-labelledby="<?= e($headingId) ?>"
    <?php endif; ?>
    <?= $attributes->twMerge('text-foreground overflow-hidden p-1') ?>
>
    <?php if ($heading): ?>
        <div data-slot="command-group-heading" id="<?= e($headingId) ?>" aria-hidden="true" class="text-muted-foreground px-2 py-1.5 text-xs font-medium"><?= e($heading) ?></div>
    <?php endif; ?>
    <?= $slot ?>
</div>
