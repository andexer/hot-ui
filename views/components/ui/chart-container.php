<?php

declare(strict_types=1);

extract(props($__ctx, [
    'config' => [],
    'id' => null,
]));

$chartId = $id ?? 'chart-'.bin2hex(random_bytes(4));

$styleVars = '';
foreach ($config as $key => $value) {
    if (is_array($value) && isset($value['color'])) {
        $styleVars .= "--color-{$key}: {$value['color']}; ";
    }
}
$styleVars = trim($styleVars);
?>
<div
    data-slot="chart"
    data-chart="<?= e($chartId) ?>"
    style="<?= e($styleVars) ?>"
    <?= $attributes->twMerge("[&_.recharts-cartesian-axis-tick_text]:fill-muted-foreground [&_.recharts-cartesian-grid_line[stroke='#ccc']]:stroke-border/50 [&_.recharts-curve.recharts-tooltip-cursor]:stroke-border [&_.recharts-polar-grid_[stroke='#ccc']]:stroke-border [&_.recharts-radial-bar-background-sector]:fill-muted [&_.recharts-rectangle.recharts-tooltip-cursor]:fill-muted [&_.recharts-reference-line_[stroke='#ccc']]:stroke-border flex aspect-video justify-center text-xs [&_.recharts-dot[stroke='#fff']]:stroke-transparent [&_.recharts-layer]:outline-none [&_.recharts-sector]:outline-none [&_.recharts-sector[stroke='#fff']]:stroke-transparent [&_.recharts-surface]:outline-none") ?>
>
    <?= $slot ?>
</div>
