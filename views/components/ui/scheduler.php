<?php

declare(strict_types=1);

extract(props($__ctx, [
    'events' => [],
    'days' => null,
    'startHour' => 8,
    'endHour' => 18,
    'view' => 'week',
    'label' => 'Schedule',
]));

$startHour = max(0, min(24, (int) $startHour));
$endHour   = max($startHour + 1, min(24, (int) $endHour));
$hours     = range($startHour, $endHour);
$span      = $endHour - $startHour;
$rowRem    = 3.5;
$bodyRem   = $span * $rowRem;

if (is_array($days) && count($days)) {
    $columns = array_values($days);
} elseif ($view === 'day') {
    $columns = ['Day'];
} else {
    $columns = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
}
$colCount = count($columns);

$toHours = function ($t): ?float {
    if (! is_string($t) || ! preg_match('/^(\d{1,2}):(\d{2})$/', trim($t), $m)) {
        return null;
    }

    return (int) $m[1] + ((int) $m[2]) / 60;
};

$fmt = function ($h): string {
    $hh = (int) floor($h);
    $mm = (int) round(($h - $hh) * 60);
    if ($mm === 60) { $hh++; $mm = 0; }
    $period = $hh >= 12 ? 'PM' : 'AM';
    $h12 = $hh % 12 === 0 ? 12 : $hh % 12;

    return $mm === 0 ? "{$h12} {$period}" : sprintf('%d:%02d %s', $h12, $mm, $period);
};

$colOf = function ($day) use ($colCount, $view): int {
    if (is_int($day) || (is_string($day) && ctype_digit($day))) {
        return ((int) $day) % max(1, $colCount);
    }
    if (is_string($day) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
        if ($view === 'day' || $colCount === 1) {
            return 0;
        }

        return ((int) date('N', strtotime($day)) - 1) % $colCount;
    }

    return 0;
};

$tones = [
    'primary' => 'bg-primary/10 border-primary text-foreground',
    'sky'     => 'bg-sky-500/10 border-sky-500 text-foreground',
    'emerald' => 'bg-emerald-500/10 border-emerald-500 text-foreground',
    'amber'   => 'bg-amber-500/10 border-amber-500 text-foreground',
    'violet'  => 'bg-violet-500/10 border-violet-500 text-foreground',
    'rose'    => 'bg-rose-500/10 border-rose-500 text-foreground',
];

$placed = [];
foreach ($events as $ev) {
    $s = $toHours($ev['start'] ?? null);
    $e = $toHours($ev['end'] ?? null);
    if ($s === null || $e === null || $e <= $s) {
        continue;
    }
    $cs = max($startHour, $s);
    $ce = min($endHour, $e);
    if ($ce <= $cs) {
        continue;
    }
    $placed[] = [
        'col'     => $colOf($ev['day'] ?? 0),
        'top'     => ($cs - $startHour) * $rowRem,
        'height'  => ($ce - $cs) * $rowRem,
        'title'   => (string) ($ev['title'] ?? 'Event'),
        'range'   => $fmt($s).' – '.$fmt($e),
        'tone'    => isset($ev['color'], $tones[$ev['color']]) ? $tones[$ev['color']] : null,
    ];
}

$byCol = array_fill(0, $colCount, []);
foreach ($placed as $p) {
    $byCol[$p['col']][] = $p;
}

$toneKeys = array_keys($tones);
$hourLines = array_slice($hours, 0, -1);
$columnData = [];
foreach ($columns as $ci => $colLabel) {
    $colEvents = $byCol[$ci] ?? [];

    $laneEnds = [];
    foreach ($colEvents as $k => $ce2) {
        $start = $ce2['top'];
        $lane = null;
        foreach ($laneEnds as $li => $end) {
            if ($start >= $end - 0.001) { $lane = $li; break; }
        }
        if ($lane === null) { $lane = count($laneEnds); $laneEnds[$lane] = 0; }
        $colEvents[$k]['lane'] = $lane;
        $colEvents[$k]['tone'] = $ce2['tone'] ?? $tones[$toneKeys[$ci % count($toneKeys)]];
        $laneEnds[$lane] = $ce2['top'] + $ce2['height'];
    }
    $laneCount = max(1, count($laneEnds));
    foreach ($colEvents as $k => $ce2) {
        $colEvents[$k]['widthPct'] = 100 / $laneCount;
        $colEvents[$k]['leftPct'] = $ce2['lane'] * (100 / $laneCount);
        $colEvents[$k]['boxHeight'] = max(1.25, $ce2['height']);
    }
    $columnData[] = [
        'label' => $colLabel,
        'odd'   => $ci % 2 === 1,
        'events'=> $colEvents,
    ];
}
?>
<div
    data-slot="scheduler"
    <?= $attributes->twMerge('bg-card text-card-foreground w-full overflow-hidden rounded-xl border shadow-sm') ?>
>
    <div role="group" aria-label="<?= e($label) ?>" class="flex flex-col">
        <div class="bg-muted/40 flex border-b">
            <div class="text-muted-foreground w-14 shrink-0 border-e px-2 py-2 text-end text-[11px] font-medium">
                <span class="sr-only">Time</span>
            </div>
            <?php foreach ($columns as $col): ?>
                <div class="text-foreground flex-1 px-2 py-2 text-center text-sm font-semibold">
                    <?= e(is_scalar($col) ? (string) $col : '') ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div
            tabindex="0"
            class="focus-visible:ring-ring/50 max-h-[28rem] overflow-y-auto outline-none focus-visible:ring-2 focus-visible:ring-inset"
            aria-label="<?= e($label) ?> grid, scrollable"
        >
            <div class="flex" style="height: <?= e((string) $bodyRem) ?>rem;">
                <div class="text-muted-foreground relative w-14 shrink-0 border-e text-[11px]" aria-hidden="true">
                    <?php foreach ($hourLines as $i => $h): ?>
                        <div class="flex items-start justify-end px-2 pt-0.5" style="height: <?= e((string) $rowRem) ?>rem;">
                            <?= e($fmt($h)) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($columnData as $colIndex => $column): ?>
                    <div class="<?= classes([
                        'relative flex-1 border-e last:border-e-0',
                        'bg-muted/10' => $column['odd'],
                    ]) ?>">
                        <?php foreach ($hourLines as $i => $h): ?>
                            <div class="bg-muted/40 absolute inset-x-0 h-px" style="top: <?= e((string) ($i * $rowRem)) ?>rem;" aria-hidden="true"></div>
                        <?php endforeach; ?>

                        <?php foreach ($column['events'] as $ev): ?>
                            <div
                                role="note"
                                aria-label="<?= e($ev['title']) ?>, <?= e($ev['range']) ?>"
                                title="<?= e($ev['title']) ?> · <?= e($ev['range']) ?>"
                                class="<?= classes(['absolute overflow-hidden rounded-md border-s-2 px-2 py-1 text-start', $ev['tone']]) ?>"
                                style="top: <?= e((string) $ev['top']) ?>rem; height: <?= e((string) $ev['boxHeight']) ?>rem; inset-inline-start: calc(<?= e((string) $ev['leftPct']) ?>% + 0.125rem); width: calc(<?= e((string) $ev['widthPct']) ?>% - 0.25rem);"
                            >
                                <p class="truncate text-xs font-semibold leading-tight"><?= e($ev['title']) ?></p>
                                <p class="truncate text-[11px] leading-tight opacity-80"><?= e($ev['range']) ?></p>
                                <span class="sr-only">from <?= e($ev['range']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
