<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => 0,
    'min' => 0,
    'max' => 100,
    'label' => null,
    'tone' => null,
    'low' => null,
    'high' => null,
    'optimum' => null,
    'showValue' => true,
    'unit' => '%',
]));

$min = (float) $min;
$max = (float) $max;

if ($max <= $min) {
    $max = $min + 1;
}

$value = (float) $value;
$clamped = max($min, min($max, $value));
$pct = ($clamped - $min) / ($max - $min) * 100;

$resolved = $tone;
if ($resolved === null && ($low !== null || $high !== null || $optimum !== null)) {
    $low = $low === null ? $min : (float) $low;
    $high = $high === null ? $max : (float) $high;

    $low = max($min, min($low, $max));
    $high = max($low, min((float) $high, $max));

    if ($clamped < $low || $clamped > $high) {
        $band = 'out';
    } else {
        $band = 'in';
    }

    if ($optimum === null) {
        $resolved = $band === 'in' ? 'good' : 'warning';
    } else {
        $optimum = max($min, min((float) $optimum, $max));

        if ($optimum < $low) {

            $resolved = $clamped <= $low ? 'good' : ($clamped <= $high ? 'warning' : 'danger');
        } elseif ($optimum > $high) {

            $resolved = $clamped >= $high ? 'good' : ($clamped >= $low ? 'warning' : 'danger');
        } else {

            $resolved = $band === 'in' ? 'good' : 'warning';
        }
    }
}

$tones = [
    'good' => 'bg-emerald-600',
    'warning' => 'bg-amber-500',
    'danger' => 'bg-destructive',
    'default' => 'bg-primary',
];
$fill = $tones[$resolved] ?? $tones['default'];

$accessibleName = $label ?? $attributes->get('aria-label') ?? 'Meter';

$valueText = rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.').($unit ?? '');
?>
<div
    data-slot="meter"
    <?= $attributes->except('aria-label')->twMerge('grid w-full gap-1.5') ?>
>
<?php if ($label !== null || $showValue): ?>
    <div data-slot="meter-header" class="flex items-center justify-between gap-2 text-sm">
        <?php if ($label !== null): ?>
            <span data-slot="meter-label" class="text-foreground font-medium"><?= e($label) ?></span>
        <?php else: ?>
            <span aria-hidden="true"></span>
        <?php endif; ?>

        <?php if ($showValue): ?>
            <span data-slot="meter-value" class="text-muted-foreground tabular-nums"><?= e($valueText) ?></span>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <div
        data-slot="meter-track"
        role="meter"
        aria-label="<?= e($accessibleName) ?>"
        aria-valuenow="<?= e(rtrim(rtrim(number_format($clamped, 2, '.', ''), '0'), '.')) ?>"
        aria-valuemin="<?= e(rtrim(rtrim(number_format($min, 2, '.', ''), '0'), '.')) ?>"
        aria-valuemax="<?= e(rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.')) ?>"
        aria-valuetext="<?= e($valueText) ?>"
        class="bg-muted relative h-2 w-full overflow-hidden rounded-full"
    >
        <div
            data-slot="meter-fill"
            class="<?= e($fill) ?> absolute inset-y-0 start-0 rounded-full transition-[width] duration-500 ease-out"
            style="width: <?= e(round($pct, 2)) ?>%"
        ></div>
    </div>
</div>
