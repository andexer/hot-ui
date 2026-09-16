<?php

declare(strict_types=1);

extract(props($__ctx, [
    'variant' => 'default',
]));

$variants = [

    'default' => 'bg-card text-card-foreground rounded-xl border p-6 shadow-sm',

    'sectioned' => 'bg-card text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm',
];
?>
<div data-slot="card" <?= $attributes->twMerge($variants[$variant] ?? $variants['default']) ?>><?= $slot ?></div>
