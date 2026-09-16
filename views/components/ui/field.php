<?php

declare(strict_types=1);

extract(props($__ctx, [
    'orientation' => 'vertical',
]));

$orientations = [
    'vertical' => 'flex-col [&>*]:w-full [&>.sr-only]:w-auto',
    'horizontal' => 'flex-row items-center [&>[data-slot=field-label]]:flex-auto has-[>[data-slot=field-content]]:items-start has-[>[data-slot=field-content]]:[&>[role=checkbox],[role=switch]]:mt-px',
    'responsive' => 'flex-col [&>*]:w-full @md/field-group:flex-row @md/field-group:items-center @md/field-group:[&>*]:w-auto',
];
?>
<div
    role="group"
    data-slot="field"
    data-orientation="<?= e($orientation) ?>"
    x-data="{}"
    x-hot-field
    <?= $attributes->twMerge('group/field flex w-full gap-2 data-[invalid=true]:text-destructive '.($orientations[$orientation] ?? $orientations['vertical'])) ?>
>
    <?= $slot ?>
</div>
