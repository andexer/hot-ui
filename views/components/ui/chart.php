<?php

declare(strict_types=1);

extract(props($__ctx, [
    'type' => 'line',
    'series' => [],
    'options' => [],
    'colors' => [],
    'config' => [],
    'labels' => [],
    'height' => 250,
    'label' => 'Chart',
]));

$resolvedColors = $colors;
if (empty($resolvedColors) && ! empty($config)) {
    $resolvedColors = array_values(array_filter(array_map(fn ($c) => $c['color'] ?? null, $config)));
}

$styleVars = '';
foreach ($config as $key => $value) {
    if (is_array($value) && isset($value['color'])) {
        $styleVars .= "--color-{$key}: {$value['color']}; ";
    }
}
$styleVars = trim($styleVars);

$payload = [
    'type' => $type,
    'series' => $series,
    'options' => $options,
    'colors' => $resolvedColors,
    'height' => (int) $height,
];
if (! empty($labels)) {
    $payload['labels'] = $labels;
}
?>
<div
    data-slot="chart"
    role="img"
    aria-label="<?= e($label) ?>"
    style="<?= e($styleVars) ?>"
    x-data="shadcnChart(<?= js($payload) ?>)"
    <?= $attributes->twMerge('flex aspect-video justify-center text-xs w-full [&_.apexcharts-tooltip]:!rounded-lg [&_.apexcharts-tooltip]:!border [&_.apexcharts-tooltip]:!border-border [&_.apexcharts-tooltip]:!bg-popover [&_.apexcharts-tooltip]:!text-popover-foreground [&_.apexcharts-tooltip]:!shadow-xl') ?>
>
    <div x-ref="canvas" class="w-full" style="min-height: <?= (int) $height ?>px"></div>
</div>
