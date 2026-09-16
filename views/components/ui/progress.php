<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => 0,
    'indeterminate' => false,
    'ariaLabel' => 'Progress',
    'circular' => false,
    'size' => 64,
    'thickness' => 6,
    'showValue' => false,
]));

$pct = max(0.0, min(100.0, (float) $value));
?>
<?php if ($circular): ?>
<?php
    $r = ((float) $size - (float) $thickness) / 2;
    $circ = 2 * M_PI * $r;
    $offset = $indeterminate ? $circ * 0.65 : $circ * (1 - $pct / 100);
    $center = (float) $size / 2;
?>
    <div
        data-slot="progress"
        role="progressbar"
        aria-label="<?= e($ariaLabel) ?>"
        aria-valuemin="0"
        aria-valuemax="100"
<?php if (! $indeterminate): ?>
        aria-valuenow="<?= (int) round($pct) ?>"
        aria-valuetext="<?= (int) round($pct) ?>%"
<?php endif; ?>
        style="width: <?= e($size) ?>px; height: <?= e($size) ?>px;"
        <?= $attributes->twMerge('text-primary relative inline-grid shrink-0 place-items-center') ?>
    >
        <svg
            width="<?= e($size) ?>" height="<?= e($size) ?>" viewBox="0 0 <?= e($size) ?> <?= e($size) ?>" fill="none" aria-hidden="true"
            class="<?= $indeterminate ? 'animate-spin' : '-rotate-90' ?>"
        >
            <circle cx="<?= e($center) ?>" cy="<?= e($center) ?>" r="<?= e($r) ?>" stroke="currentColor" stroke-opacity="0.2" stroke-width="<?= e($thickness) ?>" />
            <circle
                cx="<?= e($center) ?>" cy="<?= e($center) ?>" r="<?= e($r) ?>"
                stroke="currentColor" stroke-width="<?= e($thickness) ?>" stroke-linecap="round"
                stroke-dasharray="<?= e($circ) ?>" stroke-dashoffset="<?= e($offset) ?>"
                class="transition-[stroke-dashoffset] duration-500 ease-out"
            />
        </svg>
        <?php if ($showValue && ! $indeterminate): ?>
            <span data-slot="progress-value" class="absolute text-sm font-semibold tabular-nums"><?= (int) round($pct) ?>%</span>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div
        data-slot="progress"
        role="progressbar"
        aria-label="<?= e($ariaLabel) ?>"
        aria-valuemin="0"
        aria-valuemax="100"
<?php if (! $indeterminate): ?>
        aria-valuenow="<?= (int) round($pct) ?>"
        aria-valuetext="<?= (int) round($pct) ?>%"
<?php endif; ?>
        <?= $attributes->twMerge('bg-primary/20 relative h-2 w-full overflow-hidden rounded-full') ?>
    >
        <?php if ($indeterminate): ?>
            <div
                data-slot="progress-indicator"
                class="bg-primary animate-progress-indeterminate absolute inset-y-0 w-2/5 rounded-full"
            ></div>
        <?php else: ?>
            <div
                data-slot="progress-indicator"
                class="bg-primary h-full w-full flex-1 transition-all"
                style="transform: translateX(-<?= e(100 - $pct) ?>%)"
            ></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
