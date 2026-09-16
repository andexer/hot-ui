<?php

declare(strict_types=1);

extract(props($__ctx, [
    'data' => [],
    'levels' => null,
]));

$cells = [];
$raw = (array) $data;

if ($raw === []) {

    $days = 17 * 7;
    for ($i = 0; $i < $days; $i++) {
        $weekday = $i % 7;
        $wave = (int) round(2.5 + 2.5 * sin($i / 9));
        $weekend = ($weekday === 0 || $weekday === 6) ? -2 : 0;
        $count = max(0, $wave + ($i % 3) + $weekend);
        $cells[] = ['date' => null, 'count' => (int) $count];
    }
} else {
    $isAssoc = array_keys($raw) !== range(0, count($raw) - 1);
    if ($isAssoc) {
        ksort($raw);
        foreach ($raw as $date => $count) {
            $cells[] = ['date' => (string) $date, 'count' => (int) $count];
        }
    } else {
        foreach (array_values($raw) as $count) {
            $cells[] = ['date' => null, 'count' => (int) $count];
        }
    }
}

$counts = array_map(static fn ($c) => $c['count'], $cells);
$maxCount = $counts !== [] ? max($counts) : 0;

if (is_array($levels) && count($levels) >= 1) {
    $thresholds = array_values(array_map('intval', $levels));
    sort($thresholds);
    $thresholds = array_slice($thresholds, 0, 4);
} else {

    $step = max(1, (int) ceil($maxCount / 4));
    $thresholds = [$step, $step * 2, $step * 3, $step * 4];
}

$levelOf = static function (int $count) use ($thresholds): int {
    if ($count <= 0) {
        return 0;
    }
    $lvl = 1;
    foreach ($thresholds as $t) {
        if ($count >= $t) {
            $lvl = min(4, (int) array_search($t, $thresholds, true) + 1);
        }
    }
    return $lvl;
};

$cellClasses = [
    0 => 'bg-muted',
    1 => 'bg-emerald-200 dark:bg-emerald-900',
    2 => 'bg-emerald-400 dark:bg-emerald-700',
    3 => 'bg-emerald-600 dark:bg-emerald-500',
    4 => 'bg-emerald-800 dark:bg-emerald-300',
];

$weekCount = (int) ceil(count($cells) / 7);
$columns = array_fill(0, $weekCount, array_fill(0, 7, null));
foreach ($cells as $i => $cell) {
    $col = intdiv($i, 7);
    $row = $i % 7;
    $columns[$col][$row] = $cell;
}

$weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$shownLabels = [1 => 'Mon', 3 => 'Wed', 5 => 'Fri'];

$total = array_sum($counts);
$summary = count($cells).' days, '.$total.' total, peak '.$maxCount;
?>
<div
    data-slot="heatmap"
    role="group"
    aria-label="Activity heatmap: <?= e($summary) ?>"
    <?= $attributes->twMerge('text-muted-foreground inline-flex flex-col gap-2 text-xs') ?>
>
    <div class="flex gap-2">
        <div class="grid grid-rows-7 gap-1" aria-hidden="true">
            <?php for ($r = 0; $r < 7; $r++): ?>
                <div class="flex h-3 items-center leading-none"><?= e($shownLabels[$r] ?? '') ?></div>
            <?php endfor; ?>
        </div>

        <div class="flex gap-1">
            <?php foreach ($columns as $week): ?>
                <div class="grid grid-rows-7 gap-1">
                    <?php foreach ($week as $r => $cell): ?>
                        <?php
                        $count = $cell['count'] ?? null;
                        $lvl = $count === null ? 0 : $levelOf((int) $count);
                        $when = ($cell['date'] ?? null) ?: $weekdayLabels[$r];
                        $label = $count === null ? '' : $count.' on '.$when;
                        ?>
                        <?php if ($cell === null): ?>
                            <div class="size-3 rounded-sm" aria-hidden="true"></div>
                        <?php else: ?>
                            <div
                                class="<?= classes(['size-3 rounded-sm', $cellClasses[$lvl]]) ?>"
                                title="<?= e($label) ?>"
                                aria-hidden="true"
                            ></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex items-center gap-1 self-end">
        <span>Less</span>
        <?php for ($l = 0; $l <= 4; $l++): ?>
            <span class="<?= classes(['size-3 rounded-sm', $cellClasses[$l]]) ?>" aria-hidden="true"></span>
        <?php endfor; ?>
        <span>More</span>
    </div>
</div>
