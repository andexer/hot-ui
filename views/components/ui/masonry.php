<?php

declare(strict_types=1);

extract(props($__ctx, [
    'columns' => 3,
    'gap' => '4',
]));

$columnClasses = [
    1 => 'columns-1',
    2 => 'columns-1 sm:columns-2',
    3 => 'columns-1 sm:columns-2 lg:columns-3',
    4 => 'columns-1 sm:columns-2 lg:columns-3 xl:columns-4',
    5 => 'columns-1 sm:columns-2 lg:columns-3 xl:columns-5',
    6 => 'columns-1 sm:columns-2 lg:columns-3 xl:columns-4 2xl:columns-6',
];
$cols = $columnClasses[(int) $columns] ?? $columnClasses[3];

$gapClasses = [
    '0' => 'gap-0 [&>*]:mb-0',
    '1' => 'gap-1 [&>*]:mb-1',
    '2' => 'gap-2 [&>*]:mb-2',
    '3' => 'gap-3 [&>*]:mb-3',
    '4' => 'gap-4 [&>*]:mb-4',
    '6' => 'gap-6 [&>*]:mb-6',
    '8' => 'gap-8 [&>*]:mb-8',
];
$gapUtil = $gapClasses[(string) $gap] ?? $gapClasses['4'];

$classes = trim($cols.' '.$gapUtil.' [&>*]:break-inside-avoid');
?>
<div data-slot="masonry" <?= $attributes->twMerge($classes) ?>>
    <?= $slot ?>
</div>
