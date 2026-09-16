<?php

declare(strict_types=1);

extract(props($__ctx, [
    'old' => '',
    'new' => '',
    'mode' => 'inline',
    'filename' => null,
]));

$mode = in_array($mode, ['inline', 'split'], true) ? $mode : 'inline';

$a = $old === '' ? [] : explode("\n", str_replace("\r\n", "\n", (string) $old));
$b = $new === '' ? [] : explode("\n", str_replace("\r\n", "\n", (string) $new));

$n = count($a);
$m = count($b);

$cap = 600;
if ($n <= $cap && $m <= $cap) {

    $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
    for ($i = 1; $i <= $n; $i++) {
        for ($j = 1; $j <= $m; $j++) {
            if ($a[$i - 1] === $b[$j - 1]) {
                $lcs[$i][$j] = $lcs[$i - 1][$j - 1] + 1;
            } else {
                $lcs[$i][$j] = max($lcs[$i - 1][$j], $lcs[$i][$j - 1]);
            }
        }
    }

    $ops = [];
    $i = $n;
    $j = $m;
    while ($i > 0 || $j > 0) {
        if ($i > 0 && $j > 0 && $a[$i - 1] === $b[$j - 1]) {
            $ops[] = ['type' => 'eq', 'text' => $a[$i - 1]];
            $i--;
            $j--;
        } elseif ($j > 0 && ($i === 0 || $lcs[$i][$j - 1] >= $lcs[$i - 1][$j])) {
            $ops[] = ['type' => 'add', 'text' => $b[$j - 1]];
            $j--;
        } else {
            $ops[] = ['type' => 'del', 'text' => $a[$i - 1]];
            $i--;
        }
    }
    $ops = array_reverse($ops);
} else {
    $ops = [];
    foreach ($a as $line) {
        $ops[] = ['type' => 'del', 'text' => $line];
    }
    foreach ($b as $line) {
        $ops[] = ['type' => 'add', 'text' => $line];
    }
}

$rows = [];
$oldNo = 0;
$newNo = 0;
foreach ($ops as $op) {
    if ($op['type'] === 'eq') {
        $oldNo++;
        $newNo++;
        $rows[] = ['type' => 'eq', 'text' => $op['text'], 'oldNo' => $oldNo, 'newNo' => $newNo];
    } elseif ($op['type'] === 'del') {
        $oldNo++;
        $rows[] = ['type' => 'del', 'text' => $op['text'], 'oldNo' => $oldNo, 'newNo' => null];
    } else {
        $newNo++;
        $rows[] = ['type' => 'add', 'text' => $op['text'], 'oldNo' => null, 'newNo' => $newNo];
    }
}

$pairs = [];
if ($mode === 'split') {
    $i = 0;
    $count = count($rows);
    while ($i < $count) {
        $row = $rows[$i];
        if ($row['type'] === 'eq') {
            $pairs[] = ['left' => $row, 'right' => $row];
            $i++;
            continue;
        }

        $dels = [];
        $adds = [];
        while ($i < $count && $rows[$i]['type'] === 'del') {
            $dels[] = $rows[$i];
            $i++;
        }
        while ($i < $count && $rows[$i]['type'] === 'add') {
            $adds[] = $rows[$i];
            $i++;
        }
        $rowsInRun = max(count($dels), count($adds));
        for ($k = 0; $k < $rowsInRun; $k++) {
            $pairs[] = [
                'left' => $dels[$k] ?? null,
                'right' => $adds[$k] ?? null,
            ];
        }
    }
}

$gutter = ['eq' => ' ', 'del' => '−', 'add' => '+'];
$srPrefix = ['del' => 'Removed: ', 'add' => 'Added: '];

$lineBg = [
    'eq' => '',
    'del' => 'bg-destructive/10 text-foreground',
    'add' => 'bg-emerald-500/10 text-foreground',
];
$gutterColor = [
    'eq' => 'text-muted-foreground/40',
    'del' => 'text-destructive',
    'add' => 'text-emerald-700 dark:text-emerald-400',
];
?>
<div
    data-slot="diff-viewer"
    <?= $attributes->twMerge('bg-card text-card-foreground overflow-hidden rounded-lg border font-mono text-[13px]') ?>
>
    <?php if ($filename): ?>
        <div data-slot="diff-viewer-header" class="bg-muted/40 text-muted-foreground flex items-center gap-2 border-b px-4 py-2 text-xs">
            <i data-lucide="file-diff" class="size-3.5" aria-hidden="true"></i>
            <span><?= e($filename) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($mode === 'split'): ?>
        <div data-slot="diff-viewer-scroll" tabindex="0" class="overflow-x-auto outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:ring-inset">
            <table dir="ltr" class="w-full border-collapse text-start tabular-nums">
                <caption class="sr-only">Side-by-side diff<?= $filename ? e(' of '.$filename) : '' ?>: original on the left, changed on the right.</caption>
                <thead class="sr-only">
                    <tr>
                        <th scope="col">Original line number</th>
                        <th scope="col">Original line</th>
                        <th scope="col">Changed line number</th>
                        <th scope="col">Changed line</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pairs as $pair): ?>
                    <?php $left = $pair['left']; ?>
                    <?php $right = $pair['right']; ?>
                        <tr>
                            <td class="text-muted-foreground/50 border-r select-none px-2 py-0.5 text-end align-top"><?= e($left['oldNo'] ?? '') ?></td>
                            <td class="<?= classes([
                                'border-r py-0.5 pr-3 align-top whitespace-pre-wrap',
                                $left !== null ? $lineBg[$left['type']] : '',
                            ]) ?>"><span class="<?= classes(['inline-block w-4 shrink-0 select-none text-center', $gutterColor[$left['type'] ?? 'eq']]) ?>" aria-hidden="true"><?= e($left ? $gutter[$left['type']] : '') ?></span><?php if ($left && $left['type'] === 'del'): ?><span class="sr-only"><?= e($srPrefix['del']) ?></span><?php endif; ?><span><?= e($left['text'] ?? '') ?></span></td>
                            <td class="text-muted-foreground/50 border-r select-none px-2 py-0.5 text-end align-top"><?= e($right['newNo'] ?? '') ?></td>
                            <td class="<?= classes([
                                'py-0.5 pr-3 align-top whitespace-pre-wrap',
                                $right !== null ? $lineBg[$right['type']] : '',
                            ]) ?>"><span class="<?= classes(['inline-block w-4 shrink-0 select-none text-center', $gutterColor[$right['type'] ?? 'eq']]) ?>" aria-hidden="true"><?= e($right ? $gutter[$right['type']] : '') ?></span><?php if ($right && $right['type'] === 'add'): ?><span class="sr-only"><?= e($srPrefix['add']) ?></span><?php endif; ?><span><?= e($right['text'] ?? '') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div data-slot="diff-viewer-scroll" tabindex="0" class="overflow-x-auto outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:ring-inset">
            <table dir="ltr" class="w-full border-collapse text-start tabular-nums">
                <caption class="sr-only">Inline diff<?= $filename ? e(' of '.$filename) : '' ?>: removed lines are marked with a minus, added lines with a plus.</caption>
                <thead class="sr-only">
                    <tr>
                        <th scope="col">Original line number</th>
                        <th scope="col">Changed line number</th>
                        <th scope="col">Line</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr class="<?= classes([$lineBg[$row['type']]]) ?>">
                            <td class="text-muted-foreground/50 select-none px-2 py-0.5 text-end align-top"><?= e($row['oldNo'] ?? '') ?></td>
                            <td class="text-muted-foreground/50 border-r select-none px-2 py-0.5 text-end align-top"><?= e($row['newNo'] ?? '') ?></td>
                            <td class="py-0.5 pr-3 align-top whitespace-pre-wrap"><span class="<?= classes(['inline-block w-4 shrink-0 select-none text-center', $gutterColor[$row['type']]]) ?>" aria-hidden="true"><?= e($gutter[$row['type']]) ?></span><?php if ($row['type'] !== 'eq'): ?><span class="sr-only"><?= e($srPrefix[$row['type']]) ?></span><?php endif; ?><span><?= e($row['text']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
