<?php

declare(strict_types=1);

extract(props($__ctx, [
    'label' => null,
    'value' => null,
    'change' => null,
    'trend' => null,
    'icon' => null,
    'caption' => null,
    'series' => null,
]));

$t = $trend;
if ($t === null && is_string($change)) {
    $first = ltrim($change);
    if (str_starts_with($first, '+')) {
        $t = 'up';
    } elseif (str_starts_with($first, '-') || str_starts_with($first, "\u{2212}")) {
        $t = 'down';
    }
}
$t = in_array($t, ['up', 'down', 'neutral'], true) ? $t : 'neutral';

$trendColor = [
    'up' => 'text-emerald-700 dark:text-emerald-400',
    'down' => 'text-destructive',
    'neutral' => 'text-muted-foreground',
][$t];

$trendWord = ['up' => 'Increase', 'down' => 'Decrease', 'neutral' => 'No change'][$t];

$hasSeries = is_array($series) && count($series) > 0;
?>
<div
    data-slot="stat"
    <?= $attributes->twMerge('bg-card text-card-foreground flex flex-col gap-3 rounded-xl border p-6 shadow-sm') ?>
>
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <?php if ($icon || isset($leading)): ?>
                <span class="bg-muted text-muted-foreground flex size-9 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">
                    <?php if (isset($leading)): ?>
                        <?= $leading ?>
                    <?php else: ?>
                        <i data-lucide="<?= e((string) $icon) ?>" class="size-4"></i>
                    <?php endif; ?>
                </span>
            <?php endif; ?>

            <?php if ($label !== null): ?>
                <span class="text-muted-foreground text-sm font-medium">
                    <?= e((string) $label) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($hasSeries): ?>
            <?= $this->uiSparkline([
                'data' => $series,
                'width' => 80,
                'height' => 28,
                'class' => $trendColor.' mt-0.5 shrink-0',
                'ariaLabel' => ($label ? $label.' ' : '').'trend',
            ]) ?>
        <?php elseif (isset($trailing)): ?>
            <div class="mt-0.5 shrink-0"><?= $trailing ?></div>
        <?php endif; ?>
    </div>

    <div class="text-2xl font-semibold tabular-nums sm:text-3xl"><?php if ($value !== null): ?><?= e((string) $value) ?><?php else: ?><?= $slot ?><?php endif; ?></div>

    <?php if ($change !== null || $caption !== null): ?>
        <div class="flex items-center gap-1.5 text-sm">
            <?php if ($change !== null): ?>
                <span class="<?= classes(['inline-flex items-center gap-1 font-medium', $trendColor]) ?>">
                    <?php if ($t === 'up'): ?>
                        <i data-lucide="trending-up" class="size-4" aria-hidden="true"></i>
                    <?php elseif ($t === 'down'): ?>
                        <i data-lucide="trending-down" class="size-4" aria-hidden="true"></i>
                    <?php endif; ?>
                    <span class="sr-only"><?= e($trendWord) ?>:</span>
                    <span class="tabular-nums"><?= e((string) $change) ?></span>
                </span>
            <?php endif; ?>

            <?php if ($caption !== null): ?>
                <span class="text-muted-foreground"><?= e((string) $caption) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
