<?php

declare(strict_types=1);

extract(props($__ctx, [
    'columns' => 3,
]));

$colClasses = [
    2 => 'sm:grid-cols-2',
    3 => 'sm:grid-cols-2 lg:grid-cols-3',
    4 => 'sm:grid-cols-2 lg:grid-cols-4',
];

$cols = $colClasses[(int) $columns] ?? $colClasses[3];
?>
<div
    data-slot="bento-grid"
    <?= $attributes->twMerge('grid grid-cols-1 gap-4 auto-rows-[minmax(10rem,auto)] '.$cols) ?>
>
    <?= $slot ?>
</div>
