<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => 0,
    'from' => 0,
    'duration' => 1500,
    'decimals' => 0,
    'prefix' => '',
    'suffix' => '',
    'separator' => ',',
]));

$decimals = max(0, (int) $decimals);
$finalFormatted = $prefix.number_format((float) $value, $decimals, '.', $separator).$suffix;
?>
<span
    data-slot="number-ticker"
    x-data="hotNumberTicker({
        value: <?= js((float) $value) ?>,
        from: <?= js((float) $from) ?>,
        duration: <?= js(max(0, (int) $duration)) ?>,
        decimals: <?= js($decimals) ?>,
        separator: <?= js((string) $separator) ?>,
    })"
    <?= $attributes->twMerge('inline-block tabular-nums text-foreground') ?>
>
    <span class="sr-only"><?= e($finalFormatted) ?></span>

    <span aria-hidden="true"><?php if ($prefix !== '') { echo e($prefix); } ?><span x-text="format(current)"></span><?php if ($suffix !== '') { echo e($suffix); } ?></span>
</span>
