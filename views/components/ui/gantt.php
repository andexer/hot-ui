<?php

declare(strict_types=1);

extract(props($__ctx, [
    'tasks' => [],
    'start' => null,
    'end' => null,
    'unit' => 'day',
    'today' => null,
]));

$parse = static fn (mixed $v): \DateTimeImmutable => (new \DateTimeImmutable((string) $v))->setTime(0, 0);
$monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthsLong = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$mmm = static fn (\DateTimeImmutable $d): string => $monthsShort[(int) $d->format('n') - 1];
$mmmD = static fn (\DateTimeImmutable $d): string => $monthsShort[(int) $d->format('n') - 1].' '.$d->format('j');
$longDate = static fn (\DateTimeImmutable $d): string => $monthsLong[(int) $d->format('n') - 1].' '.$d->format('j').', '.$d->format('Y');
$daysBetween = static fn (\DateTimeImmutable $a, \DateTimeImmutable $b): int => (int) $a->diff($b)->format('%r%a');
$sameDay = static fn (\DateTimeImmutable $a, \DateTimeImmutable $b): bool => $a->format('Y-m-d') === $b->format('Y-m-d');

$rows = [];
foreach ($tasks as $t) {
    if (empty($t['start']) || empty($t['end'])) {
        continue;
    }
    $rows[] = [
        'name'     => $t['name'] ?? '',
        'start'    => $parse($t['start']),
        'end'      => $parse($t['end']),
        'progress' => isset($t['progress']) ? max(0, min(100, (int) $t['progress'])) : null,
        'color'    => $t['color'] ?? 'bg-primary',
    ];
}

$rangeStart = $start ? $parse($start) : null;
$rangeEnd = $end ? $parse($end) : null;
foreach ($rows as $r) {
    if ($rangeStart === null || $r['start'] < $rangeStart) {
        $rangeStart = $r['start'];
    }
    if ($rangeEnd === null || $r['end'] > $rangeEnd) {
        $rangeEnd = $r['end'];
    }
}

$rangeStart ??= $parse('today');
$rangeEnd ??= $rangeStart->modify('+7 days');

$totalDays = max(1, $daysBetween($rangeStart, $rangeEnd) + 1);

$dayPct = 100 / $totalDays;

$buckets = [];
$cursor = $rangeStart;
if ($unit === 'month') {
    while ($cursor <= $rangeEnd) {
        $bucketEnd = $cursor->modify('last day of this month')->setTime(0, 0);
        if ($bucketEnd > $rangeEnd) {
            $bucketEnd = $rangeEnd;
        }
        $buckets[] = ['label' => $mmm($cursor).' '.$cursor->format('Y'), 'days' => $daysBetween($cursor, $bucketEnd) + 1];
        $cursor = $bucketEnd->modify('+1 day');
    }
} elseif ($unit === 'week') {
    while ($cursor <= $rangeEnd) {
        $bucketEnd = $cursor->modify('+6 days');
        if ($bucketEnd > $rangeEnd) {
            $bucketEnd = $rangeEnd;
        }
        $buckets[] = ['label' => $mmm($cursor).' '.$cursor->format('j'), 'days' => $daysBetween($cursor, $bucketEnd) + 1];
        $cursor = $bucketEnd->modify('+1 day');
    }
} else {
    while ($cursor <= $rangeEnd) {
        $buckets[] = ['label' => $cursor->format('j'), 'sub' => $mmm($cursor), 'days' => 1];
        $cursor = $cursor->modify('+1 day');
    }
}

$minDayPx = $unit === 'day' ? 40 : 16;
$timelineMinPx = (int) ($totalDays * $minDayPx);

$todayDate = $today ? $parse($today) : $parse('today');
$showToday = $todayDate >= $rangeStart && $todayDate <= $rangeEnd;

$todayPct = $showToday ? ($daysBetween($rangeStart, $todayDate) + 0.5) * $dayPct : 0;

$labelWidth = '11rem';

$taskNoun = count($rows) === 1 ? 'task' : 'tasks';
?>
<div
    data-slot="gantt"
    <?= $attributes->twMerge('bg-card text-card-foreground w-full overflow-hidden rounded-xl border text-sm') ?>
>
    <div
        data-slot="gantt-scroll"
        tabindex="0" role="region" aria-label="Project timeline (scrollable)"
        class="focus-visible:ring-ring/50 w-full overflow-x-auto rounded-[inherit] outline-none focus-visible:ring-[3px]"
    >
        <table
            data-slot="gantt-table"
            class="w-full border-collapse"
            style="min-width: calc(<?= e($labelWidth) ?> + <?= $timelineMinPx ?>px)"
        >
            <caption class="sr-only">
                Project timeline from <?= e($longDate($rangeStart)) ?> to <?= e($longDate($rangeEnd)) ?>,
                <?= count($rows) ?> <?= e($taskNoun) ?>.
            </caption>

            <thead>
                <tr class="bg-muted/40 border-b">
                    <th
                        scope="col"
                        class="bg-muted/40 text-muted-foreground sticky start-0 z-10 px-4 py-2 text-start align-bottom text-xs font-medium"
                        style="width: <?= e($labelWidth) ?>; min-width: <?= e($labelWidth) ?>"
                    >Task</th>
                    <?php foreach ($buckets as $b): ?>
                        <th
                            scope="col"
                            class="<?= classes([
                                'text-muted-foreground border-s px-1 py-2 text-center align-bottom font-normal',
                                'text-xs' => $unit !== 'day',
                                'text-[11px] leading-tight' => $unit === 'day',
                            ]) ?>"
                            style="width: <?= $b['days'] * $dayPct ?>%"
                        >
                            <?php if (isset($b['sub'])): ?>
                                <span class="text-muted-foreground/60 block text-[9px] tracking-wide uppercase"><?= e($b['sub']) ?></span>
                            <?php endif; ?>
                            <span class="tabular-nums"><?= e($b['label']) ?></span>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows === []): ?>
                    <tr>
                        <td class="text-muted-foreground px-4 py-8 text-center" colspan="2">No tasks to display.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <?php

                        $barOffset = $daysBetween($rangeStart, $r['start']) * $dayPct;
                        $barDays = $daysBetween($r['start'], $r['end']) + 1;
                        $barWidth = $barDays * $dayPct;
                        $isMilestone = $barDays <= 1;
                        $dateLabel = $mmmD($r['start']).(! $sameDay($r['start'], $r['end']) ? ' – '.$mmmD($r['end']) : '');
                        ?>
                        <tr data-slot="gantt-row" class="hover:bg-muted/30 group border-b transition-colors last:border-0">
                            <th
                                scope="row"
                                class="bg-card group-hover:bg-muted/30 sticky start-0 z-10 truncate px-4 py-2 text-start font-medium transition-colors"
                                style="width: <?= e($labelWidth) ?>; min-width: <?= e($labelWidth) ?>"
                                title="<?= e($r['name']) ?>"
                            ><?= e($r['name']) ?></th>
                            <td class="border-s px-0 py-2" colspan="<?= count($buckets) ?>">
                                <div data-slot="gantt-track" class="relative h-7" style="min-width: <?= $timelineMinPx ?>px">
                                    <div class="bg-border/60 absolute inset-x-0 top-1/2 h-px -translate-y-1/2" aria-hidden="true"></div>

                                    <?php if ($showToday): ?>
                                        <div
                                            data-slot="gantt-today"
                                            class="bg-destructive/70 absolute inset-y-0 w-px"
                                            style="inset-inline-start: <?= $todayPct ?>%"
                                            aria-hidden="true"
                                        ></div>
                                    <?php endif; ?>

                                    <?php if ($isMilestone): ?>
                                        <div
                                            data-slot="gantt-milestone"
                                            class="ring-background absolute top-1/2 size-3 -translate-y-1/2 -translate-x-1/2 rotate-45 rounded-[2px] shadow-sm ring-2 <?= e($r['color']) ?>"
                                            style="inset-inline-start: calc(<?= $barOffset + $dayPct / 2 ?>% )"
                                            aria-hidden="true"
                                        ></div>
                                        <span class="absolute top-1/2 -translate-y-1/2 ps-2 text-xs whitespace-nowrap"
                                            style="inset-inline-start: calc(<?= $barOffset + $dayPct / 2 ?>% )">
                                            <span class="bg-foreground/5 text-foreground/80 rounded px-1 py-0.5">
                                                <?= e($r['name']) ?><span class="sr-only">, milestone on <?= e($dateLabel) ?></span>
                                            </span>
                                        </span>
                                    <?php else: ?>
                                        <div
                                            data-slot="gantt-bar"
                                            class="ring-border absolute top-1/2 flex h-5 -translate-y-1/2 items-center justify-end overflow-hidden rounded-md ring-1 ring-inset <?= e($r['color']) ?>"
                                            style="inset-inline-start: <?= $barOffset ?>%; width: <?= $barWidth ?>%; min-width: 0.5rem"
                                        >
                                            <span class="sr-only"><?= e($r['name']) ?>, <?= e($dateLabel) ?><?php if ($r['progress'] !== null): ?>, <?= e((string) $r['progress']) ?>% complete <?php endif; ?></span>
                                            <?php if ($r['progress'] !== null): ?>
                                                <div
                                                    data-slot="gantt-progress-remainder"
                                                    class="bg-card/55 absolute inset-y-0 end-0"
                                                    style="width: <?= 100 - $r['progress'] ?>%"
                                                    aria-hidden="true"
                                                ></div>
                                                <span class="text-foreground bg-card/80 relative me-1 rounded px-1 text-[10px] font-semibold tabular-nums" aria-hidden="true"><?= e((string) $r['progress']) ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($showToday && $rows !== []): ?>
        <div data-slot="gantt-legend" class="text-muted-foreground flex items-center gap-1.5 border-t px-4 py-1.5 text-xs">
            <span class="bg-destructive/70 inline-block h-3 w-px" aria-hidden="true"></span>
            Today · <?= e($mmm($todayDate).' '.$todayDate->format('j').', '.$todayDate->format('Y')) ?>
        </div>
    <?php endif; ?>
</div>
