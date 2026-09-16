<?php

declare(strict_types=1);

extract(props($__ctx, [
    'layout' => 'horizontal',
    'bordered' => false,
]));

$classes = $bordered
    ? 'bg-card text-card-foreground divide-border rounded-xl border divide-y'
    : 'flex flex-col gap-4';
?>
<dl data-slot="description-list" <?= $attributes->twMerge($classes) ?>>
    <?= $slot ?>
</dl>
